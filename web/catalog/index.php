<?php

use FourPaws\Catalog\Model\Category;
use FourPaws\CatalogBundle\Dto\RootCategoryRequest;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include.php';

/**
 * @todo
 *
 * Fucked hard fix
 */

$rootCategoryRequest = new RootCategoryRequest();
$rootCategoryRequest->setCategorySlug('/');
$rootCategoryRequest->setCategory(Category::createRoot());

include __DIR__ . '/../../src/CatalogBundle/Resources/views/Catalog/rootCategory.html.php';
