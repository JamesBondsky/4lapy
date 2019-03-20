<?php

use FourPaws\Catalog\Collection\ProductCollection;
use FourPaws\Catalog\Model\Category;
use FourPaws\CatalogBundle\Dto\ChildCategoryRequest;
use Symfony\Component\Templating\PhpEngine;

/**
 * @var ProductCollection    $collection
 * @var ChildCategoryRequest $catalogRequest
 * @var PhpEngine            $view
 * @var CMain                $APPLICATION
 * @var Category             $category
 */

global $APPLICATION;

$category = $catalogRequest->getCategory();

if ($catalogRequest->getCategory()->isLanding()) {
    $getLastRowIndex = function ($rowCount) use ($collection) {
        $lastRowIndex = ($rowCount * (intval(count($collection) / $rowCount)));
        if (count($collection) % $rowCount == 0) {
            $lastRowIndex -= $rowCount;
        }
        return $lastRowIndex;
    };

    $lastRowIndex = $getLastRowIndex(4);
    $lastRowIndexTablet = $getLastRowIndex(3);

    ob_start();
    echo $view->render('FourPawsCatalogBundle:Catalog:old.landing.articles.php', \compact('category'));
    $articlesHtml = ob_get_contents();
    ob_end_clean();

    $pageNum = $collection->getCdbResult()->NavPageNomer;
    $isFiltered = $category->getFilters()->hasCheckedFilter();
}


foreach ($collection as $product) {
    $i++;

    $APPLICATION->IncludeComponent(
        'fourpaws:catalog.element.snippet',
        '',
        [
            'PRODUCT'               => $product,
            'CURRENT_CATEGORY'      => clone $category,
            'GOOGLE_ECOMMERCE_TYPE' => 'Каталог по питомцу',
        ],
        null,
        ['HIDE_ICONS' => 'Y']
    );

    if ($catalogRequest->getCategory()->isLanding()) {

        /**
         * Баннеры между рядами товаров
         */
        if(!$isFiltered){
            if (!empty($catalogRequest->getCategory()->getUfLandingBanner())) {
                if ($i === 3 || ($i === $countItems && $i < 3)) { ?>
                    <div class="b-fleas-protection-banner b-fleas-protection-banner--catalog b-tablet">
                        <?= htmlspecialcharsback($category->getUfLandingBanner()) ?>
                    </div>
                <? }

                if ($i === 4 || ($i === $countItems && $i < 4)) { ?>
                    <div class="b-fleas-protection-banner b-fleas-protection-banner--catalog">
                        <?= htmlspecialcharsback($category->getUfLandingBanner()) ?>
                    </div>
                <? }
            }

            if (!empty($catalogRequest->getCategory()->getUfLandingBanner2())) {
                if ($i === 9 || ($i === $countItems && $i < 9)) { ?>
                    <div class="b-fleas-protection-banner b-fleas-protection-banner--catalog b-tablet">
                        <?= htmlspecialcharsback($category->getUfLandingBanner2()) ?>
                    </div>
                <? }

                if ($i === 12 || ($i === $countItems && $i < 12)) { ?>
                    <div class="b-fleas-protection-banner b-fleas-protection-banner--catalog">
                        <?= htmlspecialcharsback($category->getUfLandingBanner2()) ?>
                    </div>
                <? }
            }
        }

        /**
         * Анонсы статей
         */
        if($pageNum < 2){
            // @todo: Здесь нужен класс для моб. разрешения
            /*if ($i === $lastRowIndexTablet || ($i === $countItems && $i < $lastRowIndexTablet)) {
                echo '<div class="b-tablet">',$articlesHtml,'</div>';
            }*/
            if ($i === $lastRowIndex || ($i === $countItems && $i < $lastRowIndex)) {
                echo $articlesHtml;
            }
        }

    }
}
