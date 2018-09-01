<?php

use FourPaws\Catalog\Collection\ProductCollection;
use FourPaws\CatalogBundle\Dto\ChildCategoryRequest;
use Symfony\Component\Templating\PhpEngine;

/**
 * @var ProductCollection    $collection
 * @var ChildCategoryRequest $catalogRequest
 * @var PhpEngine $view
 * @var CMain $APPLICATION
 */

global $APPLICATION;

foreach ($collection as $product) {
    $i++;

    $APPLICATION->IncludeComponent(
        'fourpaws:catalog.element.snippet',
        '',
        [
            'PRODUCT' => $product,
            'GOOGLE_ECOMMERCE_TYPE' => 'Каталог по питомцу'
        ],
        null,
        ['HIDE_ICONS' => 'Y']
    );

    if ($catalogRequest->getCategory()
            ->isLanding()
        && !empty($catalogRequest->getCategory()
            ->getUfLandingBanner())) {
        if ($i === 3 || ($i === $countItems && $i < 3)) { ?>
            <div class="b-fleas-protection-banner b-tablet">
                <?= htmlspecialcharsback($catalogRequest->getCategory()
                    ->getUfLandingBanner()) ?>
            </div>
        <?php }

        if ($i === 4 || ($i === $countItems && $i < 4)) { ?>
            <div class="b-fleas-protection-banner">
                <?= htmlspecialcharsback($catalogRequest->getCategory()
                    ->getUfLandingBanner()) ?>
            </div>
        <?php }
    }
}