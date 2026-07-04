<?php

/**
 * Trait Shortcodes - Motor de Renderização Dinâmica (Lunar Engine)
 *
 * Transforma tags no formato [tag] em componentes Blade ou lógica Eloquent.
 *
 * ==========================================================================
 * COMO ADICIONAR UM NOVO SHORTCODE:
 * ==========================================================================
 *
 * CAMINHO 1: APENAS VISUAL (Sem lógica de Banco de Dados)
 * --------------------------------------------------------------------------
 * Basta criar um arquivo Blade em: resources/views/components/shortcodes/{tag}.blade.php
 * O ContentHelper encontrará o arquivo automaticamente. Use a variável $attr para
 * os atributos e $content para o conteúdo entre as tags.
 *
 * CAMINHO 2: COM LÓGICA (Busca em Banco de Dados / Processamento PHP)
 * --------------------------------------------------------------------------
 * 1. Crie um método privado nesta Trait seguindo o padrão: render{NomeEmStudlyCase}
 * 2. O método deve aceitar ($attributes, $content = null).
 *
 * Exemplo para [box title="Aviso"] Conteúdo [/box]:
 *
 *    private static function renderBox($attributes, $content = null) {
 *        // 1. Processa dados
 *        $title = $attributes['title'] ?? 'Informação';
 *
 *        // 2. Despacha para uma View Blade (Recomendado para manter HTML Vanilla)
 *        return view('components.shortcodes.box', [
 *            'title'   => $title,
 *            'content' => $content
 *        ])->render();
 *    }
 *
 * ==========================================================================
 * REGRAS DE OURO E ATRIBUTOS ESPECIAIS:
 * ==========================================================================
 *
 * 1. ID DE ATIVO (Anti-Duplicação):
 *    Se o componente carrega CSS ou JS, use a diretiva @onceAsset(asset('...'))
 *    ou @onceAsset('id-unico') no arquivo Blade para evitar scripts duplicados.
 *
 * 2. FORMATOS SUPORTADOS:
 *    Self-closing: [link href="..."]
 *    Com conteúdo: [style] .classe { ... } [/style]
 *
 * 3. ATRIBUTOS:
 *    Sempre chegam como um array associativo (ex: ['id' => '123']).
 *    No Blade, acesse via: {{ $attr['id'] ?? 'default' }}
 * ==========================================================================
 */

namespace App\Traits;

use App\Models\Form;

trait Shortcodes {
    /**
     * Renderiza: [form slug="contato"]
     */
    // private static function renderForm($attributes, $content = null)
    // {
    //     $slug = $attributes['slug'] ?? null;
    //     if (!$slug) return '';

    //     $form = Form::active()->where('slug', $slug)->first();
    //     if (!$form) return '';

    //     return view('components.shortcodes.form', ['form' => $form])->render();
    // }

    /**
     * Renderiza: [script src="..." id="..." ...][/script]
     */
    private static function renderScript($attributes, $content = null)
    {
        return view('components.shortcodes.script', [
            'attr' => $attributes,
            'content' => $content
        ])->render();
    }

    /**
     * Renderiza: [style id="..."] .classe { ... } [/style]
     */
    private static function renderStyle($attributes, $content = null)
    {
        return view('components.shortcodes.style', [
            'attr' => $attributes,
            'content' => $content
        ])->render();
    }

    /**
     * Renderiza: [link rel="..." href="..." id="..."]
     */
    private static function renderLink($attributes, $content = null)
    {
        return view('components.shortcodes.link', [
            'attr' => $attributes
        ])->render();
    }
}
