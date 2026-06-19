<?php

/**
 * Trait Shortcodes
 *
 * Motor de renderização de shortcodes dinâmicos (estilo WordPress).
 *
 * =========================================================
 * COMO ADICIONAR UM NOVO SHORTCODE NO FUTURO:
 * =========================================================
 * Crie um método privado estático nesta trait seguindo o padrão:
 *    render{NomeEmStudlyCase}
 *
 *    Exemplo: Para criar o shortcode [gallery id="1" columns="3"],
 *    basta adicionar o método abaixo:
 *
 *    private static function renderGallery($attributes) {
 *        $id = $attributes['id'] ?? null;
 *        // ... sua lógica ...
 *        return view('...')->render();
 *    }
 *
 * O roteador dinâmico (renderShortcode) encontrará e executará
 *    o método automaticamente. Não é necessário registrar em nenhum outro lugar.
 *
 * Formato de tag aceito: [tag atributo="valor" outro_atributo='valor']
 * =========================================================
 */

namespace App\Traits;

use App\Models\Form;

trait Shortcodes {
    /**
     * Renderiza: [form slug="contato"]
     */
    private static function renderForm($attributes)
    {
        $slug = $attributes['slug'] ?? null;
        if (!$slug) return '';

        $form = Form::active()->where('slug', $slug)->first();
        if (!$form) return '<p style="color: #ef4444;">[Formulário "' . e($slug) . '" não encontrado]</p>';

        return view('forms.embed', ['form' => $form])->render();
    }
}
