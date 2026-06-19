<?php

return [
    /*
    O menu principal no header
    --------------------------
    Cada item pode ter:
    "label"     - o texto visível no link (obrigatório)
    e
    "route"     - o nome da rota
    ou
    "slug"      - slug de uma página dinâmica
    "namespace" - namespace de página (desambiguizar slugs iguais, opcional)
    ou
    "path"      - a URL será montada independente de rotas ou páginas: url(path)
    */
    "mainMenu" => [
        [
            "label" => "Início",
            "route" => 'home'
        ],
        [
            "label" => "Blog",
            "path" => "blog"
        ],
        [
            "label" => "Teste",
            "slug" => "test-page-2"
        ],
        [
            "label" => "Documentação",
            "route" => "docs"
        ],
    ]
];
