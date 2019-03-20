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
        if (!empty($catalogRequest->getCategory()->getUfLandingBanner())) {
            if ($i === 3 || ($i === $countItems && $i < 3)) { ?>
                <div class="b-fleas-protection-banner b-tablet">
                    <?= htmlspecialcharsback($category->getUfLandingBanner()) ?>
                </div>
            <?php }

            if ($i === 4 || ($i === $countItems && $i < 4)) { ?>
                <div class="b-fleas-protection-banner">
                    <?= htmlspecialcharsback($category->getUfLandingBanner()) ?>
                </div>
            <?php }
        }

        if (!empty($catalogRequest->getCategory()->getUfLandingBanner2())) {
            if ($i === 9 || ($i === $countItems && $i < 9)) { ?>
                <div class="b-fleas-protection-banner b-tablet">
                    <?= htmlspecialcharsback($category->getUfLandingBanner2()) ?>
                </div>
            <?php }
            if ($i === 12 || ($i === $countItems && $i < 12)) { ?>
                <div class="b-fleas-protection-banner">
                    <?= htmlspecialcharsback($category->getUfLandingBanner2()) ?>
                </div>
            <?php }
        }
    }

}
