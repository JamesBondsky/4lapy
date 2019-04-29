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
