<?php

$sections = [
    [
        'NAME' => 'Товары для кошек',
        'URL' => '/catalog/koshki/'
    ],
    [
        'NAME' => 'Товары для собак',
        'URL' => '/catalog/sobaki/'
    ],
    [
        'NAME' => 'Защита от блох и клещей',
        'URL' => '/catalog/veterinarnaya-apteka/zashchita-ot-blokh-i-kleshchey/'
    ],
    [
        'NAME' => 'Товары для аквариумистики',
        'URL' => '/catalog/ryby/'
    ],
    [
        'NAME' => 'Товары для грызунов и хорьков',
        'URL' => '/catalog/gryzuny-i-khorki/'
    ],
    [
        'NAME' => 'Товары для черепах и рептилий',
        'URL' => '/catalog/reptilii/'
    ],
    [
        'NAME' => 'Товары для птиц',
        'URL' => '/catalog/ptitsy/'
    ],
    [
        'NAME' => 'Ветаптека',
        'URL' => '/catalog/veterinarnaya-apteka/'
    ]
];

foreach ($sections as $section) {
    $aMenuLinks[] = [
        $section['NAME'],
        $section['URL'],
        [],
        [],
        '',
    ];
}
