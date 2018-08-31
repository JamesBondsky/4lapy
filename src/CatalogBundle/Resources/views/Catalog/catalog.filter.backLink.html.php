<?php

/**
 * @var Request                               $request
 * @var CatalogCategorySearchRequestInterface $catalogRequest
 * @var ProductSearchResult                   $productSearchResult
 * @var ChildCategoryRequest                  $catalogRequest
 * @var PhpEngine                             $view
 * @var Category                              $category
 * @var CMain                                 $APPLICATION
 */

use FourPaws\Catalog\Model\Category;
use FourPaws\CatalogBundle\Dto\CatalogCategorySearchRequestInterface;
use FourPaws\CatalogBundle\Dto\ChildCategoryRequest;
use FourPaws\Search\Model\ProductSearchResult;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\PhpEngine;

$parents = [];
$tmpCategory = $category;

do {
    $parent = $tmpCategory->getParent();
    $tmpCategory = $parent;
    if ($parent->getId()) {
        $parents[] = $parent;
    }
} while ($parent->getId());

$parents = array_reverse($parents);

if (!empty($parents)) { ?>
    <div class="b-filter__block b-filter__block--back">
        <ul class="b-back">
            <?php /** @var Category $parent */ ?>
            <?php foreach ($parents as $parent) { ?>
                <li class="b-back__item">
                    <a class="b-link b-link--back" href="<?= $catalogRequest instanceof ChildCategoryRequest ? $catalogRequest->getCategoryPathByCategory($parent) : $parent->getSectionPageUrl() ?>"
                       title="<?= $parent->getCanonicalName() ?>">
                        <?= $parent->getCanonicalName() ?>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </div>
<?php } ?>
