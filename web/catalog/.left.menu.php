<?php

use FourPaws\Catalog\Query\CategoryQuery;
use FourPaws\Catalog\Model\Category;

$aMenuLinks = [];
$sections = (new CategoryQuery())->withFilterParameter('DEPTH_LEVEL', 1)->exec();
/** @var Category $section */
foreach ($sections as $section) {
    $aMenuLinks[] = [
        $section->getName(),
        $section->getSectionPageUrl(),
        [],
        [],
        ''
    ];
}
?>
