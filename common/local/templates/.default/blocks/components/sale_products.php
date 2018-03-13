<?php

use Bitrix\Main\Application;
use Bitrix\Main\Data\Cache;
use FourPaws\Catalog\Collection\ProductCollection;
use FourPaws\Catalog\Query\ProductQuery;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $APPLICATION, $params, $elId;

/**
 * Блок "Распродажа", выводимый на детальных страницах публикаций
 */

$cache = Cache::createInstance();
$products = [];
$countOnPage = 20;
$page = (int)Application::getInstance()->getContext()->getRequest()->get('page');
if ($page === 0) {
    $page = 1;
}
if ($cache->initCache(
    $params['CACHE_TIME'],
    serialize(
        [
            'ITEM_ID'   => $elId,
            'IBLOCK_ID' => $params['IBLOCK_ID'],
            'TYPE'      => 'DETAIL_SALE_PRODUCTS',
            'page'      => $page,
            'count'     => $countOnPage,
        ]
    )
)) {
    $vars = $cache->getVars();
    $res = $vars['res'];
} else {
    $res = \CIBlockElement::GetProperty($params['IBLOCK_ID'], $elId, '', '', ['CODE' => 'PRODUCTS']);
    $products = [];
    while ($item = $res->Fetch()) {
        if (!empty($item['VALUE']) && !\in_array($item['VALUE'], $products, true)) {
            $products[] = $item['VALUE'];
        }
    }
    if (!empty($products)) {
        $query = new ProductQuery();
        $query->withNav(['nPageSize' => $countOnPage, 'iNumPage' => $page]);
        $res = $query->withFilter(['=XML_ID' => $products])->exec();
        $cache->endDataCache(['res' => $res]); // записываем в кеш
    }
}
if ($res instanceof ProductCollection && !$res->isEmpty()) { ?>
    <div class="b-container">
        <section class="b-common-section">
            <div class="b-common-section__title-box b-common-section__title-box--sale">
                <h2 class="b-title b-title--sale">Распродажа</h2>
            </div>
            <div class="b-common-section__content b-common-section__content--sale js-popular-product">
                <?php foreach ($res as $product) {
                    $APPLICATION->IncludeComponent(
                        'fourpaws:catalog.element.snippet',
                        '',
                        ['PRODUCT' => $product]
                    );
                } ?>
            </div>
        </section>
    </div>
    <?php $APPLICATION->IncludeComponent(
        'bitrix:system.pagenavigation',
        'pagination',
        [
            'NAV_TITLE'      => '',
            'NAV_RESULT'     => $res->getCdbResult(),
            'SHOW_ALWAYS'    => false,
            'PAGE_PARAMETER' => 'page',
            'AJAX_MODE'      => 'N',
        ],
        null,
        [
            'HIDE_ICONS' => 'Y',
        ]
    ); ?>
    <?php
} ?>
<?php
