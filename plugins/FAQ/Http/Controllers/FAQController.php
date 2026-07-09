<?php

namespace Plugins\FAQ\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FAQController extends Controller
{
    /**
     * Lista todos os conjuntos de FAQ cadastrados nas Options
     */
    public function index()
    {
        // Puxa do helper todas as opções que começam com "faq_"
        $rawOptions = getPrefixedOptions('faq_') ?? [];
        $faqs = [];

        foreach ($rawOptions as $key => $value) {
            $decoded = is_string($value) ? json_decode($value, true) : $value;
            if (is_array($decoded)) {
                $faqs[] = $decoded;
            }
        }

        // Ordena a listagem pelo título do conjunto de FAQ de forma ascendente
        usort($faqs, fn($a, $b) => strcmp($a['title'] ?? '', $b['title'] ?? ''));

        return view('faq::admin.index', compact('faqs'));
    }

    /**
     * Tela de criação de nova FAQ
     */
    public function create()
    {
        return view('faq::admin.edit');
    }

    /**
     * Grava os dados serializando em JSON na chave faq_{slug}
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'      => 'required|string|max:255',
            'slug'       => 'required|string|max:255|alpha_dash',
            'items_json' => 'required|json'
        ]);

        $slug = $validated['slug'];

        // Checagem lógica de exclusividade da chave nas Options
        if (getOption("faq_{$slug}") !== null) {
            return back()->withErrors(['slug' => 'Este slug de FAQ já está em uso por outro conjunto.'])->withInput();
        }

        $data = [
            'title' => $validated['title'],
            'slug'  => $slug,
            'items' => json_decode($validated['items_json'], true) ?? []
        ];

        // Persiste de forma limpa como JSON na tabela de options
        setOption("faq_{$slug}", json_encode($data, JSON_UNESCAPED_UNICODE));

        return redirect()->route('admin.faq.index')
            ->with('success', "FAQ '{$validated['title']}' criada com sucesso! Use o shortcode [faq slug=\"{$slug}\"]");
    }

    /**
     * Carrega e decodifica a FAQ específica para edição no Alpine
     */
    public function edit(string $id)
    {
        $rawOption = getOption("faq_{$id}");
        if (!$rawOption) {
            abort(404, 'FAQ não encontrada.');
        }

        $faq = is_string($rawOption) ? json_decode($rawOption, true) : $rawOption;

        return view('faq::admin.edit', compact('faq'));
    }

    /**
     * Atualiza os dados, tratando renomeações de chaves para evitar lixo no banco
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'title'      => 'required|string|max:255',
            'slug'       => 'required|string|max:255|alpha_dash',
            'items_json' => 'required|json'
        ]);

        $newSlug = $validated['slug'];

        // Se alterou a slug (renomeação), limpa a chave antiga física em disco/banco
        if ($id !== $newSlug) {
            if (getOption("faq_{$newSlug}") !== null) {
                return back()->withErrors(['slug' => 'Este novo slug de FAQ já está em uso.'])->withInput();
            }
            // Deleta a opção antiga limpando o valor para null
            setOption("faq_{$id}", null);
        }

        $data = [
            'title' => $validated['title'],
            'slug'  => $newSlug,
            'items' => json_decode($validated['items_json'], true) ?? []
        ];

        // Grava na chave definitiva
        setOption("faq_{$newSlug}", json_encode($data, JSON_UNESCAPED_UNICODE));

        return redirect()->route('admin.faq.index')
            ->with('success', 'FAQ atualizada com sucesso!');
    }

    /**
     * Remove o conjunto de FAQ deletando a Option correspondente
     */
    public function destroy(string $id)
    {
        // No sistema de options, definir como null ou deletar a chave apaga o registro
        setOption("faq_{$id}", null);

        return redirect()->route('admin.faq.index')
            ->with('success', 'FAQ removida de forma definitiva.');
    }
}
