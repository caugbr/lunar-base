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
        $query = Media::with('mediaable');

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

        $media = $query->latest()->paginate(24);

        return view('admin.media.index', compact('media'));
    }

    /**
     * Upload de nova mídia (Form ou AJAX)
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx|max:10240', // 10MB
            'folder' => 'nullable|string|max:50',
            'alt' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:500',
        ]);

        $file = $request->file('file');
        $folder = $request->input('folder', 'uploads');

        // Usa seu helper já adaptado
        $result = uploadImage($file, $folder, [
            'save_original' => true,
            'create_media_record' => true,
            'thumb' => [300, 300, true],      // Thumbnail padrão
            'resize' => 800,                  // Versão média para web
            'quality' => 85,
            'alt' => $request->input('alt'),
            'caption' => $request->input('caption'),
        ]);

        // Resposta JSON para Alpine/AJAX
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $result['media'] ?? null,
                'url' => $result['url'] ?? null,
                'thumbnail_url' => $result['thumbnail_url'] ?? null,
            ]);
        }

        return redirect()->route('admin.media.index')
            ->with('success', 'Mídia enviada com sucesso.');
    }

    /**
     * Atualiza metadados (alt, caption, nome)
     */
    // public function update(Request $request, Media $media)
    // {
    //     $validated = $request->validate([
    //         'name' => 'nullable|string|max:255',
    //         'alt' => 'nullable|string|max:255',
    //         'caption' => 'nullable|string|max:500',
    //     ]);

    //     $media->update($validated);

    //     return $request->wantsJson()
    //         ? response()->json(['success' => true, 'data' => $media])
    //         : redirect()->back()->with('success', 'Metadados atualizados.');
    // }
    public function update(Request $request, Media $media) // ← Lembre: parâmetro é $medium, não $media
    {
        // \Log::info('Update recebido', [
        //     'id' => $medium->id,
        //     'input' => $request->only(['name', 'alt', 'caption']),
        // ]);

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'alt' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:500',
        ]);

        $media->update($validated);

        return $request->wantsJson()
            ? response()->json(['success' => true, 'data' => $media]) // ← fresh() garante dados atualizados
            : redirect()->back()->with('success', 'Metadados atualizados.');
    }

    /**
     * Remove mídia (Soft Delete + limpeza física via model boot)
     */
    public function destroy(Media $media)
    {
        // 1. Extrai a pasta do path (ex: "media/settings/original" → "settings")
        $pathParts = explode('/', $media->path);
        $folder = $pathParts[1] ?? 'uploads'; // Padrão: 'uploads'

        // 2. Chama o helper para deletar arquivos físicos (original + variações)
        deleteImage($media->path, $folder, 'public');

        // 3. Deleta o registro do banco
        $media->delete(); // ou forceDelete() se quiser exclusão permanente

        return request()->wantsJson()
            ? response()->json([
                'success' => true,
                'message' => 'Mídia removida com sucesso.'
            ])
            : redirect()->back()->with('success', 'Mídia removida com sucesso.');
    }

    /**
     * Endpoint AJAX para Alpine/Modal
     * Retorna JSON paginado com suporte a busca, filtro e URLs formatadas
     */
    public function data(Request $request)
    {
        $query = Media::query();

        // Filtro de vínculo
        if ($request->filled('linked')) {
            if ($request->linked === 'orphan') {
                // Só o que NÃO tem vínculo
                $query->whereNull('mediaable_id');

            } elseif ($request->linked === 'linked') {
                if ($request->filled('mediaable_id')) {
                    // Contexto específico (ex: edição de página)
                    $query->where('mediaable_id', $request->mediaable_id)
                        ->where('mediaable_type', $request->mediaable_type);
                } else {
                    // 👈 Contexto geral (admin/media): só o que JÁ tem vínculo
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
        $perPage = $request->integer('per_page', 20);
        $media = $query->latest()->paginate($perPage);

        // Transforma a coleção adicionando dados úteis para o frontend
        $media->getCollection()->transform(function ($item) {
            // Gera URL da thumbnail de forma segura para qualquer extensão
            $pathInfo = pathinfo($item->path);
            $thumbName = $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
            $thumbPath = rtrim($pathInfo['dirname'], '/') . '/' . $thumbName;

            $thumbnailUrl = Storage::disk('public')->exists($thumbPath)
                ? Storage::disk('public')->url($thumbPath)
                : $item->url; // Fallback para a original se não houver thumb

            return [
                'id' => $item->id,
                'name' => $item->name,
                'url' => $item->url,
                'thumbnail_url' => $thumbnailUrl,
                'alt' => $item->alt,
                'caption' => $item->caption,
                'size_formatted' => $item->size_formatted,
                'is_image' => $item->is_image,
                'mime_type' => $item->mime_type,
                'created_at' => $item->created_at->format('d/m/Y H:i'),
            ];
        });

        return response()->json($media);
    }
}
