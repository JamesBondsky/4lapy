<?php

use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Query\CategoryQuery;

$aMenuLinks = [];
$sections   = (new CategoryQuery())->withFilterParameter('DEPTH_LEVEL', 1)->exec();
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

$aMenuLinks = array_merge($aMenuLinks, [
    [
        'Котята',
        '/catalog/koshki/zaveli-kotenka22/',
        [],
        [],
        '',
    ],
    [
        'Щенки',
        '/catalog/sobaki/zaveli-shchenka33/',
        [],
        [],
        '',
    ],
]);
