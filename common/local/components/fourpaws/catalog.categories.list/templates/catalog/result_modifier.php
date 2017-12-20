<?php

use FourPaws\Catalog\Model\Category;

/**
 * @var array    $arParams
 * @var array    $arResult
 * @var Category $category
 */

/**
 * Раскрываем нужные данные
 */

$category = $arResult['CATEGORY'];
foreach ($category->getChild() as $category) {
    $category->getChild();
}
