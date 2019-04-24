<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Statistic Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used by the paginator library to build
    | the simple pagination links. You are free to change them to anything
    | you want to customize your views to better match your application.
    |
    */

    'header'  => 'Estatísticos',
    'subtext' => 'Clique em uma postagem abaixo para ver informações mais detalhadas.',
    'empty'   => 'Não há posts publicados para os quais você pode ver as estatísticas.',
    'views'   => 'Vista(s)',
    'cards'   => [
        'views'      => [
            'title' => 'Visualizações (30 dias)',
        ],
        'posts'      => [
            'title' => 'Total de postagens',
        ],
        'publishing' => [
            'title'   => 'Publicação',
            'details' => [
                'published' => 'Publicado Post(s)',
                'drafts'    => 'Rascunho(s)',
            ],
        ],
    ],
    'details' => [
        'created'   => 'Criado',
        'published' => 'publicado em',
        'views'     => 'Visualizações por fonte de tráfego',
        'reading'   => 'Tempos de leitura populares',
        'empty'     => 'Aguardando até que sua postagem tenha mais visualizações para mostrar essas informações.',
        'referer'   => [
            'other'   => 'De outros',
            'unknown' => 'As visualizações de postagem nessa categoria não podem determinar com segurança um referenciador. por exemplo. Modo incógnito',
        ],
    ],

];
