<?php

namespace Plugins\Menus\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Plugins\Menus\Models\Menu;
use Plugins\Menus\Models\MenuItem;
use App\Models\Page;
use App\Models\Post;
use App\Models\Term;

class MenuController extends Controller
{
    /**
     * Lista todos os menus criados
     */
    public function index()
    {
        $menus = Menu::orderBy('name')->get();
        return view('menus::admin.index', compact('menus')); // 💡 Corrigido: 'admin.forms.index'
    }

    /**
     * Tela de criação (usamos o formulário inline no index, mas mantemos o método mapeado)
     */
    public function create()
    {
        return view('menus::admin.create'); // 💡 Corrigido: 'admin.forms.create'
    }

    /**
     * Grava um novo menu e redireciona direto para a tela do construtor (Edit)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:menus,slug|alpha_dash',
            'hook' => 'nullable|string|max:255',
        ]);

        $menu = Menu::create($validated);

        return redirect()->route('admin.menus.edit', $menu->id)
            ->with('success', "Menu '{$menu->name}' criado! Agora adicione os links.");
    }

    /**
     * A Tela Central do Construtor de Menus
     */
    public function edit(Menu $menu)
    {
        // Carrega dados do sistema para as sanfonas de links
        $pages = Page::published()->orderBy('title')->get();
        $posts = Post::published()->orderBy('title')->get();
        $terms = Term::orderBy('name')->get();

        // Serializa a árvore recursiva do banco em JSON para o Alpine
        $itemsJson = json_encode($this->getSerializedTree($menu->rootItems));

        return view('menus::admin.edit', compact('menu', 'pages', 'posts', 'terms', 'itemsJson')); // 💡 Corrigido: 'admin.forms.edit'
    }

    /**
     * Auxiliar recursivo para formatar os itens do banco para o JSON do front-end
     */
    protected function getSerializedTree($items): array
    {
        return $items->map(function ($item) {
            return [
                'label'      => $item->label, // Chama o acessor inteligente de rótulo
                'type'       => $item->type,
                'url'        => $item->url,
                'model_type' => $item->model_type,
                'model_id'   => $item->model_id,
                'target'     => $item->target,
                'class'      => $item->class,
                'children'   => $this->getSerializedTree($item->children),
            ];
        })->toArray();
    }

    /**
     * Atualiza o nome, slug e hook do menu de forma convencional
     */
    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => "required|string|max:255|alpha_dash|unique:menus,slug,{$menu->id}",
            'hook' => "nullable|string|max:255", // Adicionada a validação do hook
        ]);

        $menu->update($validated);

        return redirect()->back() // Redireciona de volta para continuar organizando os links!
            ->with('success', 'Propriedades do menu atualizadas com sucesso.');
    }

    /**
     * Remove o menu e todos os seus itens associados (cascade)
     */
    public function destroy(Menu $menu)
    {
        $menu->delete();
        return redirect()->route('admin.menus.index')
            ->with('success', 'Menu removido com sucesso.');
    }

    /**
     * SALVAMENTO RECURSIVO EM ÁRVORE (JSON)
     */
    public function saveItems(Request $request, Menu $menu)
    {
        $request->validate([
            'items_json' => 'required|json'
        ]);

        $tree = json_decode($request->items_json, true) ?? [];

        try {
            DB::transaction(function () use ($menu, $tree) {
                // 1. Limpa todas as entradas de links anteriores deste menu de forma segura
                $menu->items()->delete();

                // 2. Dispara a inserção recursiva de ramos e folhas da árvore
                $this->saveMenuItemBranch($menu->id, $tree);
            });

            return response()->json([
                'success' => true,
                'message' => 'Estrutura e hierarquia do menu salvas com sucesso!'
            ]);

        } catch (\Exception $e) {
            \Log::error("Erro ao salvar estrutura de menu: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao salvar. Tente novamente.'
            ], 500);
        }
    }

    /**
     * Algoritmo auxiliar recursivo para inserção em cascata
     */
    protected function saveMenuItemBranch(int $menuId, array $branch, ?int $parentId = null): void
    {
        foreach ($branch as $index => $itemData) {

            // Grava o item atual vinculando seu respectivo parent_id (se houver)
            $item = MenuItem::create([
                'menu_id'    => $menuId,
                'parent_id'  => $parentId,
                'label'      => $itemData['label'] ?? null,
                'type'       => $itemData['type'] ?? 'custom',
                'url'        => $itemData['url'] ?? null,
                'model_type' => $itemData['model_type'] ?? null,
                'model_id'   => $itemData['model_id'] ?? null,
                'order'      => $index,
                'target'     => $itemData['target'] ?? '_self',
                'class'      => $itemData['class'] ?? null,
            ]);

            // Se esse item possuir filhos na árvore do JSON, chama a si mesmo recursivamente
            if (!empty($itemData['children']) && is_array($itemData['children'])) {
                $this->saveMenuItemBranch($menuId, $itemData['children'], $item->id);
            }
        }
    }
}
