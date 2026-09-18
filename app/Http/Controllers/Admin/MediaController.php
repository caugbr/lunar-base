<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    /**
     * Lista todas as mídias (View Standalone)
     */
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission(['manage-media', 'manage-own-media']), 403);

        $query = Media::forCurrentUser()->with('mediaable');

        // Filtros
        if ($request->filled('type')) {
            match ($request->type) {
                'image'    => $query->images(),
                'document' => $query->documents(),
                default    => null,
            };
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('alt', 'like', "%{$request->search}%");
            });
        }

        $media = $query->latest()->paginate(setting('reading.media_pagination_max_items'));

        return view('admin.media.index', compact('media'));
    }

    /**
     * Upload de nova mídia (Form ou AJAX)
     */
    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission(['manage-media', 'manage-own-media']), 403);

        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,webp,svg,pdf,doc,docx|max:10240', // 10MB
            'folder' => 'nullable|string|max:50',
            'alt' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:500',
        ]);

        $file = $request->file('file');
        $folder = $request->input('folder', 'uploads');

        // Upload da imagem
        $result = uploadImage($file, $folder, [
            'save_original' => true,
            'create_media_record' => true,
            'thumb' => [300, 300, true],      // Thumbnail padrão
            'resize' => 800,                  // Versão média para web
            'quality' => 85,
            'alt' => $request->input('alt'),
            'caption' => $request->input('caption'),
        ]);

        // Garante autor e metadados padrão para o registro criado
        if (isset($result['media']) && $result['media'] instanceof Media) {
            $mediaRecord = $result['media'];

            if (empty($mediaRecord->author_id)) {
                $mediaRecord->author_id = auth()->id();
            }

            // Define o ponto focal padrão (centro: 50% / 50%) se for imagem
            if ($mediaRecord->is_image && !$mediaRecord->hasMeta('focal_x')) {
                $mediaRecord->setMeta([
                    'focal_x' => 50,
                    'focal_y' => 50,
                ]);
            }

            $mediaRecord->save();
        }

        // Resposta JSON para Alpine/AJAX
        // if ($request->wantsJson()) {
        //     return response()->json([
        //         'success' => true,
        //         'data' => $result['media'] ?? null,
        //         'url' => $result['url'] ?? null,
        //         'thumbnail_url' => $result['thumbnail_url'] ?? null,
        //     ]);
        // }
        if ($request->wantsJson()) {
            $mediaRecord = $result['media'] ?? null;

            return response()->json([
                'success' => true,
                'data' => $mediaRecord ? array_merge($mediaRecord->toArray(), [
                    'is_image' => $mediaRecord->is_image,
                    'size_formatted' => $mediaRecord->size_formatted,
                    'url' => $result['url'] ?? $mediaRecord->url,
                ]) : null,
                'url' => $result['url'] ?? null,
                'thumbnail_url' => $result['thumbnail_url'] ?? null,
            ]);
        }

        return redirect()->route('admin.media.index')
            ->with('success', 'Mídia enviada com sucesso.');
    }

    /**
     * Atualiza metadados (alt, caption, nome, ponto focal e meta genérico)
     */
    public function update(Request $request, Media $media)
    {
        abort_unless($media->canBeManagedBy(), 403, 'Você não tem permissão para editar esta mídia.');

        $validated = $request->validate([
            'name'           => 'nullable|string|max:255',
            'alt'            => 'nullable|string|max:255',
            'caption'        => 'nullable|string|max:500',
            'focal_x'        => 'nullable|numeric|min:0|max:100',
            'focal_y'        => 'nullable|numeric|min:0|max:100',
            'meta'           => 'nullable|array',
            'meta.alignment' => 'nullable|string|in:left,center,right,float-left,float-right',
        ]);

        // Atualiza campos de texto direto
        $media->fill(collect($validated)->only(['name', 'alt', 'caption'])->toArray());

        // Se veio array 'meta' genérico, faz merge usando a trait HasMeta
        if (isset($validated['meta'])) {
            $media->setMeta($validated['meta']);
        }

        // Processa alteração do Ponto Focal
        $focalChanged = false;
        if ($request->has(['focal_x', 'focal_y'])) {
            $newFocalX = (int) round($request->input('focal_x'));
            $newFocalY = (int) round($request->input('focal_y'));

            $currentFocalX = (int) $media->getMeta('focal_x', 50);
            $currentFocalY = (int) $media->getMeta('focal_y', 50);

            // Verifica se as coordenadas realmente mudaram
            if ($newFocalX !== $currentFocalX || $newFocalY !== $currentFocalY) {
                $focalChanged = true;
                $media->setMeta([
                    'focal_x' => $newFocalX,
                    'focal_y' => $newFocalY,
                ]);
            }
        }

        $media->save();

        // Se for imagem e o ponto focal foi alterado, regenera todas as variações em cache
        if ($focalChanged && $media->is_image) {
            $pathParts = explode('/', $media->path);
            $folder = $pathParts[1] ?? 'uploads';

            if (function_exists('deleteMediaVariants') && function_exists('generateMediaVariants')) {
                deleteMediaVariants($media->path, $folder);
                generateMediaVariants($media->path, $folder, [
                    'focal_x' => $media->getMeta('focal_x', 50),
                    'focal_y' => $media->getMeta('focal_y', 50),
                ]);
            }
        }

        return $request->wantsJson()
            ? response()->json(['success' => true, 'data' => $media->fresh()])
            : redirect()->back()->with('success', 'Metadados atualizados com sucesso.');
    }

    /**
     * Remove mídia (Soft Delete + limpeza física via helpers)
     */
    public function destroy(Media $media)
    {
        abort_unless($media->canBeManagedBy(), 403, 'Você não tem permissão para excluir esta mídia.');

        $pathParts = explode('/', $media->path);
        $folder = $pathParts[1] ?? 'uploads';

        // Chama o helper para deletar arquivos físicos (original + variações)
        deleteImage($media->path, $folder, 'public');

        // Deleta o registro do banco
        $media->delete();

        return request()->wantsJson()
            ? response()->json([
                'success' => true,
                'message' => 'Mídia removida com sucesso.'
            ])
            : redirect()->back()->with('success', 'Mídia removida com sucesso.');
    }

    /**
     * Endpoint AJAX para Alpine/Modal
     */
    public function data(Request $request)
    {
        abort_unless(auth()->user()->hasPermission(['manage-media', 'manage-own-media']), 403);

        $query = Media::forCurrentUser()->with(['mediaable', 'postThumbnail', 'pageThumbnail']);

        // Filtro de vínculo
        if ($request->filled('linked')) {
            if ($request->linked === 'orphan') {
                $query->whereNull('mediaable_id');
            } elseif ($request->linked === 'linked') {
                if ($request->filled('mediaable_id')) {
                    $query->where('mediaable_id', $request->mediaable_id)
                        ->where('mediaable_type', $request->mediaable_type);
                } else {
                    $query->whereNotNull('mediaable_id');
                }
            }
        }

        // Filtro por tipo (imagem/documento)
        if ($request->filled('type')) {
            match ($request->type) {
                'image' => $query->images(),
                'document' => $query->documents(),
                default => null,
            };
        }

        // Busca por nome ou alt
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('alt', 'like', "%{$request->search}%");
            });
        }

        // Paginação
        $perPage = $request->integer('per_page', setting('reading.media_pagination_max_items'));
        $media = $query->latest()->paginate($perPage);

        // Transforma a coleção adicionando metadados úteis para o modal Alpine
        $media->getCollection()->transform(function ($item) {
            if (str_starts_with($item->mime_type, 'image/svg')) {
                $thumbnailUrl = $item->url;
            } else {
                $pathInfo = pathinfo($item->path);
                $thumbName = $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
                $thumbPath = rtrim($pathInfo['dirname'], '/') . '/' . $thumbName;

                $thumbnailUrl = Storage::disk('public')->exists($thumbPath)
                    ? Storage::disk('public')->url($thumbPath)
                    : $item->url;
            }

            $mediaableInfo = null;
            if ($item->mediaable) {
                $mediaableInfo = [
                    'type'  => class_basename($item->mediaable_type),
                    'title' => $item->mediaable->title
                            ?? $item->mediaable->name
                            ?? 'Sem título',
                    'url'   => method_exists($item->mediaable, 'adminEditUrl')
                        ? $item->mediaable->adminEditUrl()
                        : null,
                ];
            }

            $thumbnailInfo = $item->thumbnail_of;

            return [
                'id'             => $item->id,
                'name'           => $item->name,
                'url'            => $item->url,
                'thumbnail_url'  => $thumbnailUrl,
                'alt'            => $item->alt,
                'caption'        => $item->caption,
                'meta'           => $item->meta ?? [],
                'size_formatted' => $item->size_formatted,
                'is_image'       => $item->is_image,
                'mime_type'      => $item->mime_type,
                'created_at'     => $item->created_at->format('d/m/Y H:i'),
                'linked_to'      => $mediaableInfo,
                'thumbnail_of'   => $thumbnailInfo,
            ];
        });

        return response()->json($media);
    }
}
