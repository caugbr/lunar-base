<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Taxonomy;
use App\Models\Term;

class TaxonomySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Criar as taxonomias
        $taxonomies = [
            [
                'name' => 'Categorias',
                'slug' => 'categorias',
                'description' => 'Categorias para organizar o conteúdo por assunto principal',
                'hierarchical' => true,
            ],
            [
                'name' => 'Tags',
                'slug' => 'tags',
                'description' => 'Tags livres para conteúdo relacionado',
                'hierarchical' => false,
            ],
            [
                'name' => 'Seções',
                'slug' => 'secoes',
                'description' => 'Seções do site (Blog, Notícias, Artigos)',
                'hierarchical' => true,
            ],
        ];

        foreach ($taxonomies as $tax) {
            Taxonomy::create($tax);
        }

        // 2. Buscar as taxonomias criadas
        $categorias = Taxonomy::where('slug', 'categorias')->first();
        $tags = Taxonomy::where('slug', 'tags')->first();
        $secoes = Taxonomy::where('slug', 'secoes')->first();

        // 3. Criar termos para Categorias (hierárquico)
        if ($categorias) {
            $noticias = Term::create([
                'taxonomy_id' => $categorias->id,
                'name' => 'Notícias',
                'slug' => 'noticias',
                'description' => 'Conteúdo sobre novidades e atualizações',
                'parent_id' => null,
                'order' => 1,
            ]);

            $blog = Term::create([
                'taxonomy_id' => $categorias->id,
                'name' => 'Blog',
                'slug' => 'blog',
                'description' => 'Artigos e reflexões',
                'parent_id' => null,
                'order' => 2,
            ]);

            // Subcategoria de Notícias
            Term::create([
                'taxonomy_id' => $categorias->id,
                'name' => 'Tecnologia',
                'slug' => 'tecnologia',
                'description' => 'Notícias sobre tecnologia',
                'parent_id' => $noticias->id,
                'order' => 1,
            ]);

            Term::create([
                'taxonomy_id' => $categorias->id,
                'name' => 'Astrologia',
                'slug' => 'astrologia',
                'description' => 'Notícias sobre astrologia',
                'parent_id' => $noticias->id,
                'order' => 2,
            ]);

            // Subcategoria de Blog
            Term::create([
                'taxonomy_id' => $categorias->id,
                'name' => 'Desenvolvimento Pessoal',
                'slug' => 'desenvolvimento-pessoal',
                'description' => 'Artigos sobre autoconhecimento',
                'parent_id' => $blog->id,
                'order' => 1,
            ]);
        }

        // 4. Criar termos para Tags (não hierárquico)
        if ($tags) {
            $tagList = [
                ['name' => 'Laravel', 'slug' => 'laravel', 'description' => 'Conteúdo sobre Laravel'],
                ['name' => 'Vue.js', 'slug' => 'vue-js', 'description' => 'Conteúdo sobre Vue.js'],
                ['name' => 'Astrologia', 'slug' => 'astrologia', 'description' => 'Conteúdo sobre astrologia'],
                ['name' => 'Filosofia', 'slug' => 'filosofia', 'description' => 'Reflexões filosóficas'],
                ['name' => 'CSS', 'slug' => 'css', 'description' => 'Tutoriais e dicas de CSS'],
                ['name' => 'JavaScript', 'slug' => 'javascript', 'description' => 'Conteúdo sobre JavaScript'],
                ['name' => 'Lua', 'slug' => 'lua', 'description' => 'Conteúdo sobre astrologia lunar'],
                ['name' => 'Produtividade', 'slug' => 'produtividade', 'description' => 'Dicas de produtividade'],
            ];

            foreach ($tagList as $tag) {
                Term::create([
                    'taxonomy_id' => $tags->id,
                    'name' => $tag['name'],
                    'slug' => $tag['slug'],
                    'description' => $tag['description'],
                    'parent_id' => null,
                    'order' => 0,
                ]);
            }
        }

        // 5. Criar termos para Seções (hierárquico)
        if ($secoes) {
            Term::create([
                'taxonomy_id' => $secoes->id,
                'name' => 'Blog',
                'slug' => 'blog',
                'description' => 'Artigos e conteúdos do blog',
                'parent_id' => null,
                'order' => 1,
            ]);

            Term::create([
                'taxonomy_id' => $secoes->id,
                'name' => 'Notícias',
                'slug' => 'noticias',
                'description' => 'Notícias e atualizações',
                'parent_id' => null,
                'order' => 2,
            ]);

            Term::create([
                'taxonomy_id' => $secoes->id,
                'name' => 'Artigos',
                'slug' => 'artigos',
                'description' => 'Artigos mais longos e aprofundados',
                'parent_id' => null,
                'order' => 3,
            ]);
        }
    }
}
