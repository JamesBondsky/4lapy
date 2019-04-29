<?php

use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Query\CategoryQuery;

$aMenuLinks = [];
$sections = (new CategoryQuery())
    ->withFilter([
        'CODE' => [
            'koshki',
            'sobaki',
            'zashchita-ot-blokh-i-kleshchey',
            'ryby',
            'gryzuny-i-khorki',
            'reptilii',
            'ptitsy',
            'veterinarnaya-apteka',
        ]
    ])
    ->withOrder(['SORT' => 'ASC'])
    ->exec();

/** @var Category $section */
foreach ($sections as $section) {
    $aMenuLinks[] = [
        $section->getName(),
        $section->getSectionPageUrl(),
        [],
        [],
        '',
    ];
}
