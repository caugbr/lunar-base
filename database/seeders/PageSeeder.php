<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        // Páginas de exemplo
        $pages = [
            [
                'title' => 'Termos de Uso',
                'slug' => 'termos-de-uso',
                'content' => '<h1>Termos de Uso</h1>
<p>Bem-vindo ao nosso site. Ao utilizar nossos serviços, você concorda com os seguintes termos e condições:</p>
<h2>1. Uso do serviço</h2>
<p>Nosso serviço é fornecido para entretenimento e autoconhecimento. Não deve ser usado como diagnóstico médico ou psicológico.</p>
<h2>2. Responsabilidades</h2>
<p>O usuário é responsável pela veracidade dos dados fornecidos.</p>
<h2>3. Modificações</h2>
<p>Podemos alterar estes termos a qualquer momento.</p>',
                'excerpt' => 'Termos e condições de uso do serviço.',
                'author_id' => 1,
                'status' => 'published',
                'template' => 'page',
            ],
            [
                'title' => 'Política de Privacidade',
                'slug' => 'politica-de-privacidade',
                'content' => '<h1>Política de Privacidade</h1>
<p>Sua privacidade é importante para nós.</p>
<h2>Dados coletados</h2>
<p>Coletamos apenas os dados necessários para a prestação do serviço.</p>
<h2>Uso dos dados</h2>
<p>Seus dados não são compartilhados com terceiros sem seu consentimento.</p>',
                'excerpt' => 'Como coletamos e protegemos seus dados pessoais.',
                'author_id' => 1,
                'status' => 'published',
                'template' => 'page',
            ],
            [
                'title' => 'Sobre Nós',
                'slug' => 'sobre-nos',
                'content' => '<h1>Sobre Nós</h1>
<p>Somos uma plataforma dedicada a conectar pessoas através da sabedoria lunar.</p>
<p>Nosso time é formado por astrólogos, desenvolvedores e entusiastas da espiritualidade.</p>',
                'excerpt' => 'Conheça nossa história e equipe.',
                'author_id' => 1,
                'status' => 'published',
                'template' => 'fullwidth',
            ],
            [
                'title' => 'Página de Exemplo (Rascunho)',
                'slug' => 'exemplo-rascunho',
                'content' => '<h1>Página de Exemplo</h1>
<p>Esta página está em modo rascunho e não está visível ao público.</p>',
                'excerpt' => 'Esta página serve como exemplo de conteúdo não publicado.',
                'author_id' => 1,
                'status' => 'draft',
                'template' => 'page',
            ],
        ];

        foreach ($pages as $page) {
            Page::create($page);
        }
    }
}
