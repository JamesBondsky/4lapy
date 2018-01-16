<?php
/**
 * @var Request $request
 * @var CatalogCategorySearchRequestInterface $catalogRequest
 * @var ProductSearchResult $productSearchResult
 * @var PhpEngine $view
 * @var CategoryCollection $categories
 * @var Category $category
 * @var CMain $APPLICATION
 */

use FourPaws\CatalogBundle\Dto\CatalogCategorySearchRequestInterface;
use FourPaws\Catalog\Collection\CategoryCollection;
use FourPaws\Catalog\Model\Category;
use FourPaws\Search\Model\ProductSearchResult;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\PhpEngine;

global $APPLICATION;

?>
<div class="b-container">
    <div class="b-catalog__wrapper-title b-catalog__wrapper-title--filter">
        <h1 class="b-title b-title--h1 b-title--search"><? $APPLICATION->ShowTitle() ?>
        </h1>
        <p class="b-title b-title--result">К сожалению, по вашему запросу <?= $catalogRequest->getSearchString(
            ) ? '"<span>' . $catalogRequest->getSearchString() . '</span>"' : '' ?> ничего не найдено.
            Попробуйте найти подходящий товар в каталоге.
        </p>
    </div>
</div>
<div class="b-container">
    <div class="b-wrapper b-wrapper--negative b-wrapper--search-empty">
        <?php while ($category = $categories->next()) { ?>
            <a class="b-link b-link--filter b-link--filter-search"
               href="<?= $category->getSectionPageUrl() ?>"
               title="<?= $category->getDisplayName() ? $category->getDisplayName() : $category->getName() ?>">
                <?= $category->getDisplayName() ? $category->getDisplayName() : $category->getName() ?>
            </a>
        <?php } ?>
    </div>
</div>
