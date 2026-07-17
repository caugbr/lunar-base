<?php

namespace Plugins\Populator\Helpers;

class LoremIpsumHelper
{
    protected static array $frases = [
        'A tecnologia transforma a forma como vivemos e trabalhamos.',
        'Em um mundo cada vez mais conectado, a informação é poder.',
        'A criatividade é a inteligência se divertindo.',
        'Inovação não é sobre ideias, é sobre resolver problemas reais.',
        'O conhecimento é a única riqueza que ninguém pode tirar de você.',
        'Cada desafio é uma oportunidade disfarçada.',
        'A simplicidade é a sofisticação máxima.',
        'Persistência é a chave para qualquer conquista.',
        'O futuro pertence aqueles que acreditam na beleza dos seus sonhos.',
        'Aprender é a única coisa que a mente nunca se cansa.',
        'A curiosidade é o combustível da descoberta.',
        'Grandes realizações nascem de pequenos passos.',
        'A colaboração multiplica resultados exponencialmente.',
        'Erros são provas de que você está tentando.',
        'A empatia é a base de qualquer relação humana.',
        'Mudança começa com a decisão de tentar.',
        'O que não desafia, não transforma.',
        'Visão sem ação é apenas sonho.',
        'A disciplina é a ponte entre metas e realizações.',
        'Cada dia é uma nova chance de fazer diferente.',
        'A comunicação clara evita 90% dos problemas.',
        'Liderança é servir, não comandar.',
        'O feedback honesto é o maior presente que você pode receber.',
        'Adaptabilidade é a habilidade mais valiosa do século.',
        'A atenção plena é o antídoto para a ansiedade moderna.',
        'Pequenas melhorias diárias resultam em transformações extraordinárias.',
        'A integridade é fazer o certo mesmo quando ninguém está olhando.',
        'O tempo é o ativo mais escasso; use-o com intenção.',
        'Diversidade de pensamento enriquece qualquer solução.',
        'A humildade é o início da sabedoria.',
        'A educação é a arma mais poderosa que você pode usar para mudar o mundo.',
        'A verdadeira riqueza está nas experiências, não nos bens materiais.',
        'Criatividade é pensar em novas ideias; inovação é fazer essas ideias acontecerem.',
        'O sucesso é a soma de pequenos esforços repetidos dia após dia.',
        'A paciência é a virtude dos fortes, não dos fracos.',
        'Não espere por oportunidades; crie-as.',
        'A resiliência é a capacidade de se reconstruir depois de cada queda.',
        'O silêncio é às vezes a resposta mais eloquente.',
        'A ética é o alicerce de qualquer sociedade justa.',
        'A arte de viver bem é também a arte de morrer bem.',
        'Não há atalhos para nenhum lugar que valha a pena ir.',
        'A confiança é construída em gotas e perdida em baldes.',
        'O otimismo é a fé em ação.',
        'A sabedoria começa na reflexão.',
        'A justiça é a medida mais pura da civilização.',
        'A liberdade é o direito de dizer às pessoas o que elas não querem ouvir.',
        'A cultura é a soma de todas as formas de arte, de amor e de pensamento.',
        'A natureza não se apressa, mas tudo é realizado.',
        'A esperança é o sonho do homem acordado.',
        'A amizade é um só corpo habitado por duas almas.',
        'A coragem é a resistência ao medo, não a ausência dele.',
        'A beleza está nos olhos de quem vê.',
        'A felicidade não é algo pronto; vem de suas próprias ações.',
        'A solidão é o preço que se paga pela independência.',
        'A memória é o diário que carregamos conosco.',
        'A imaginação é mais importante que o conhecimento.',
        'A paixão é o fogo que nos impulsiona a agir.',
        'A gratidão transforma o que temos em suficiente.',
        'A gentileza é a linguagem que o surdo pode ouvir e o cego pode ver.',
        'A perseverança é o trabalho árduo que você faz depois de estar cansado de fazer o trabalho árduo.',
    ];

    protected static array $titulos = [
        'Introdução',
        'Desenvolvimento',
        'Conclusão',
        'Reflexões',
        'Análise',
        'Perspectivas',
        'Considerações Finais',
        'Contexto',
        'Fundamentos',
        'Aplicações Práticas',
        'Desafios',
        'Oportunidades',
        'Metodologia',
        'Resultados',
        'Discussão',
        'Implicações',
        'Revisão',
        'Síntese',
        'Proposta',
        'Estratégia',
        'Execução',
        'Avaliação',
        'Recomendações',
        'Próximos Passos',
    ];

    public static function paragrafo(int $minFrases = 3, int $maxFrases = 6): string
    {
        $qtd = rand($minFrases, $maxFrases);
        $frases = array_rand_values(self::$frases, $qtd);
        return implode(' ', $frases);
    }

    public static function paragrafos(int $qtd = 5): string
    {
        $html = '';
        for ($i = 0; $i < $qtd; $i++) {
            if ($i > 0 && rand(1, 4) === 1) {
                $html .= '<h2>' . self::$titulos[array_rand(self::$titulos)] . '</h2>' . "\n";
            }
            if (rand(1, 5) === 1) {
                $itens = array_rand_values(self::$frases, rand(3, 5));
                $html .= '<ul>' . "\n";
                foreach ($itens as $item) {
                    $html .= '<li>' . $item . '</li>' . "\n";
                }
                $html .= '</ul>' . "\n";
            } else {
                $html .= '<p>' . self::paragrafo() . '</p>' . "\n";
            }
        }
        return $html;
    }

    public static function excerpt(int $frases = 2): string
    {
        return implode(' ', array_rand_values(self::$frases, $frases));
    }

    public static function titulo(): string
    {
        $prefixos = ['Como', 'Por que', 'O futuro de', 'Entendendo', 'Guia completo de', 'A arte de'];
        $temas = ['tecnologia', 'inovação', 'criatividade', 'liderança', 'comunicação', 'aprendizado', 'transformação'];
        $sufixos = ['no século XXI', 'de forma eficiente', 'para iniciantes', 'na prática', 'sem complicações'];

        return $prefixos[array_rand($prefixos)] . ' ' . $temas[array_rand($temas)] . ' ' . $sufixos[array_rand($sufixos)];
    }
}

if (!function_exists('array_rand_values')) {
    function array_rand_values(array $array, int $num = 1): array
    {
        $keys = array_rand($array, min($num, count($array)));
        if (!is_array($keys)) {
            $keys = [$keys];
        }
        return array_map(fn($k) => $array[$k], $keys);
    }
}
