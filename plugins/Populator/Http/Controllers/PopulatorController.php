<?php

namespace Plugins\Populator\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Post;
use App\Models\Page;
use App\Models\Media;
use App\Models\Term;
use App\Helpers\ContentHelper;
use Plugins\Populator\Helpers\LoremIpsumHelper;

class PopulatorController extends Controller
{
    protected static array $nomes = [
        'Ana Silva', 'Carlos Mendes', 'Mariana Costa', 'Joao Pedro', 'Fernanda Lima',
        'Ricardo Souza', 'Juliana Almeida', 'Bruno Oliveira', 'Patricia Santos', 'Felipe Rocha',
        'Camila Ferreira', 'Andre Martins', 'Luciana Barbosa', 'Thiago Moreira', 'Vanessa Pinto',
        'Gabriel Dias', 'Renata Nunes', 'Leonardo Araujo', 'Bianca Cavalcanti', 'Matheus Teixeira',
        'Isabela Correia', 'Rodrigo Moura', 'Carolina Vasconcelos', 'Eduardo Freitas', 'Amanda Braga',
        'Lucas Figueiredo', 'Natalia Cunha', 'Pedro Henrique', 'Leticia Andrade', 'Daniel Cardoso',
        'Sofia Rezende', 'Henrique Campos', 'Larissa Miranda', 'Vitor Hugo', 'Clara Duarte',
        'Rafael Guimaraes', 'Beatriz Machado', 'Gustavo Lopes', 'Yasmin Pires', 'Enzo Ribeiro',
        'Livia Monteiro', 'Arthur Fonseca', 'Manuela Sales', 'Davi Peixoto', 'Helena Moraes',
        'Samuel Tavares', 'Valentina Silveira', 'Miguel Antunes', 'Eloa Borges', 'Theo Guedes',
    ];

    public function index()
    {
        $userCount = User::count();
        $postCount = Post::count();
        $pageCount = Page::count();
        $termCount = Term::count();

        return view('populator::admin.index', compact('userCount', 'postCount', 'pageCount', 'termCount'));
    }

    /**
     * Gera usuarios ficticios
     */
    public function generateUsers(Request $request)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:' . count(self::$nomes),
        ]);

        $quantity = $request->integer('quantity');
        $roles = array_keys(config('rolesPermissions.roles', []));
        $password = Str::random(12);
        $created = 0;

        $nomesDisponiveis = self::$nomes;
        shuffle($nomesDisponiveis);

        foreach (array_slice($nomesDisponiveis, 0, $quantity) as $nomeCompleto) {
            $slug = Str::slug($nomeCompleto);
            $email = $slug . '@server.com';

            if (User::where('email', $email)->exists()) {
                continue;
            }

            User::create([
                'name' => $nomeCompleto,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => $roles[array_rand($roles)],
            ]);
            $created++;
        }

        if ($created === 0) {
            return redirect()->route('admin.populator.index')
                ->with('warning', 'Nenhum usuario criado. Todos os nomes disponiveis ja existem no sistema.');
        }

        $msg = "{$created} usuarios criados";
        if ($created < $quantity) {
            $skipped = $quantity - $created;
            $msg .= " ({$skipped} pulados por ja existirem)";
        }
        $msg .= ". Senha padrao: {$password}";

        return redirect()->route('admin.populator.index')
            ->with('success', $msg);
    }

    /**
     * Gera posts ficticios
     */
    public function generatePosts(Request $request)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:100',
            'with_thumbnail' => 'boolean',
            'content_images' => 'nullable|integer|min:0|max:10',
            'gallery_images' => 'nullable|integer|min:0|max:20',
            'status_mode' => 'required|in:published_only,draft_only,mixed',
        ]);

        $quantity = $validated['quantity'];
        $withThumbnail = $request->boolean('with_thumbnail');
        $contentImages = $request->integer('content_images', 0);
        $galleryImages = $request->integer('gallery_images', 0);
        $statusMode = $validated['status_mode'];

        $userIds = User::pluck('id')->toArray();
        $termIds = Term::pluck('id')->toArray();
        $templates = array_keys(config('postTemplates.templates', []));
        $defaultTemplate = $templates[0] ?? 'default';

        if (empty($userIds)) {
            return redirect()->back()->with('error', 'Crie usuarios antes de gerar posts.');
        }

        $created = 0;

        for ($i = 0; $i < $quantity; $i++) {
            $titulo = LoremIpsumHelper::titulo();
            $slug = Str::slug($titulo) . '-' . Str::random(4);

            $content = LoremIpsumHelper::paragrafos(rand(4, 8));

            if ($contentImages > 0) {
                $content = $this->injectContentImages($content, rand(0, $contentImages));
            }

            $status = match ($statusMode) {
                'published_only' => 'published',
                'draft_only' => 'draft',
                default => rand(1, 10) <= 7 ? 'published' : 'draft',
            };

            $postData = [
                'title' => $titulo,
                'slug' => $slug,
                'content' => ContentHelper::sanitizeForStorage($content),
                'excerpt' => LoremIpsumHelper::excerpt(),
                'author_id' => $userIds[array_rand($userIds)],
                'status' => $status,
                'template' => $defaultTemplate,
                'published_at' => $status === 'published' ? now()->subDays(rand(0, 365)) : null,
                'featured' => rand(1, 10) === 1,
                'sticky' => rand(1, 20) === 1,
            ];

            $post = Post::create($postData);

            if ($withThumbnail) {
                $media = $this->downloadPlaceholderImage('posts');
                if ($media) {
                    $post->update(['thumbnail_id' => $media->id]);
                }
            }

            if ($galleryImages > 0) {
                $qtd = rand(1, $galleryImages);
                $galleryIds = [];
                for ($j = 0; $j < $qtd; $j++) {
                    $media = $this->downloadPlaceholderImage('posts');
                    if ($media) {
                        $galleryIds[] = $media->id;
                    }
                }
                if (!empty($galleryIds)) {
                    Media::whereIn('id', $galleryIds)->update([
                        'mediaable_id' => $post->id,
                        'mediaable_type' => Post::class,
                    ]);
                }
            }

            if (!empty($termIds) && rand(1, 3) === 1) {
                $qtdTerms = min(rand(1, 3), count($termIds));
                $selectedTerms = array_rand_values($termIds, $qtdTerms);
                $post->terms()->sync($selectedTerms);
            }

            $created++;
        }

        return redirect()->route('admin.populator.index')
            ->with('success', "{$created} posts criados com sucesso.");
    }

    /**
     * Gera paginas ficticias
     */
    public function generatePages(Request $request)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:100',
            'with_thumbnail' => 'boolean',
            'content_images' => 'nullable|integer|min:0|max:10',
            'gallery_images' => 'nullable|integer|min:0|max:20',
            'status_mode' => 'required|in:published_only,draft_only,mixed',
        ]);

        $quantity = $validated['quantity'];
        $withThumbnail = $request->boolean('with_thumbnail');
        $contentImages = $request->integer('content_images', 0);
        $galleryImages = $request->integer('gallery_images', 0);
        $statusMode = $validated['status_mode'];

        $userIds = User::pluck('id')->toArray();
        $templates = array_keys(config('pageTemplates.templates', []));
        $defaultTemplate = $templates[0] ?? 'default';
        $namespaces = Page::select('namespace')->distinct()->whereNotNull('namespace')->pluck('namespace')->toArray();

        if (empty($userIds)) {
            return redirect()->back()->with('error', 'Crie usuarios antes de gerar paginas.');
        }

        $created = 0;

        for ($i = 0; $i < $quantity; $i++) {
            $titulo = LoremIpsumHelper::titulo();
            $slug = Str::slug($titulo) . '-' . Str::random(4);
            $namespace = !empty($namespaces) && rand(1, 3) === 1 ? $namespaces[array_rand($namespaces)] : null;

            $content = LoremIpsumHelper::paragrafos(rand(3, 6));

            if ($contentImages > 0) {
                $content = $this->injectContentImages($content, rand(0, $contentImages));
            }

            $status = match ($statusMode) {
                'published_only' => 'published',
                'draft_only' => 'draft',
                default => rand(1, 10) <= 8 ? 'published' : 'draft',
            };

            $pageData = [
                'title' => $titulo,
                'slug' => $slug,
                'content' => ContentHelper::sanitizeForStorage($content),
                'excerpt' => LoremIpsumHelper::excerpt(),
                'namespace' => $namespace,
                'author_id' => $userIds[array_rand($userIds)],
                'status' => $status,
                'template' => $defaultTemplate,
                'is_main' => false,
            ];

            $page = Page::create($pageData);

            if ($withThumbnail) {
                $media = $this->downloadPlaceholderImage('pages');
                if ($media) {
                    $page->update(['thumbnail_id' => $media->id]);
                }
            }

            if ($galleryImages > 0) {
                $qtd = rand(1, $galleryImages);
                $galleryIds = [];
                for ($j = 0; $j < $qtd; $j++) {
                    $media = $this->downloadPlaceholderImage('pages');
                    if ($media) {
                        $galleryIds[] = $media->id;
                    }
                }
                if (!empty($galleryIds)) {
                    Media::whereIn('id', $galleryIds)->update([
                        'mediaable_id' => $page->id,
                        'mediaable_type' => Page::class,
                    ]);
                }
            }

            $created++;
        }

        return redirect()->route('admin.populator.index')
            ->with('success', "{$created} paginas criadas com sucesso.");
    }

    /**
     * Injeta imagens placeholder no HTML do conteudo
     */
    protected function injectContentImages(string $content, int $count): string
    {
        if ($count <= 0) return $content;

        $paragraphs = explode('</p>', $content);
        $total = count($paragraphs);

        for ($i = 0; $i < $count; $i++) {
            $pos = rand(1, max(1, $total - 2));
            $width = [600, 800, 1200][array_rand([600, 800, 1200])];
            $height = [300, 400, 600][array_rand([300, 400, 600])];
            $seed = rand(1, 1000);

            $img = '<figure><img src="https://picsum.photos/seed/' . $seed . '/' . $width . '/' . $height . '" alt="Imagem ilustrativa" loading="lazy"><figcaption>Imagem ilustrativa</figcaption></figure>' . "\n";

            array_splice($paragraphs, $pos, 0, [$img]);
            $total++;
        }

        return implode('', $paragraphs);
    }

    /**
     * Baixa imagem placeholder e cria registro na media
     */
    protected function downloadPlaceholderImage(string $folder): ?Media
    {
        try {
            $seed = rand(1, 1000);
            $width = [300, 600, 800][array_rand([300, 600, 800])];
            $height = [200, 400, 600][array_rand([200, 400, 600])];
            $url = "https://picsum.photos/seed/{$seed}/{$width}/{$height}";

            $response = Http::timeout(10)->get($url);

            if (!$response->successful()) {
                return null;
            }

            $tempFile = tempnam(sys_get_temp_dir(), 'populator_');
            file_put_contents($tempFile, $response->body());

            $uploadedFile = new \Illuminate\Http\UploadedFile(
                $tempFile,
                "placeholder_{$seed}.jpg",
                'image/jpeg',
                null,
                true
            );

            $result = uploadImage($uploadedFile, $folder, [
                'save_original' => true,
                'create_media_record' => true,
                'thumb' => [300, 300, true],
                'resize' => 800,
                'quality' => 85,
                'alt' => 'Imagem de teste',
            ]);

            @unlink($tempFile);

            return $result['media'] ?? null;

        } catch (\Exception $e) {
            \Log::error('Populator: erro ao baixar imagem placeholder', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
