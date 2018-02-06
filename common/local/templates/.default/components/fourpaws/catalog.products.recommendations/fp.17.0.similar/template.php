<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true) {
    die();
}
/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CatalogSectionComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 */

//
// Если выбрана отложенная загрузка результата, то отправляем ajax-запрос
//
if ($arResult['RESULT_TYPE'] === 'INITIAL' && $arParams['DEFERRED_LOAD'] === 'Y') {
    $signer = new \Bitrix\Main\Security\Sign\Signer();
    $signedTemplate = $signer->sign($templateName, 'catalog.products.recommendations');
    $signedParams = $signer->sign(base64_encode(serialize($arResult['ORIGINAL_PARAMETERS'])), 'catalog.products.recommendations');

    ?><script type="text/javascript">
        new FourPawsCatalogProductsRecommendationsComponent({
            siteId:                                                                '<?=\CUtil::JSEscape(SITE_ID)?>',
            componentPath:                                                         '<?=\CUtil::JSEscape($componentPath)?>',
            bigData: <?=\CUtil::PhpToJSObject($arResult['BIG_DATA_SETTINGS'])?>,
            template:                                                              '<?=\CUtil::JSEscape($signedTemplate)?>',
            ajaxId:                                                                '<?=\CUtil::JSEscape($arParams['AJAX_ID'])?>',
            parameters:                                                            '<?=\CUtil::JSEscape($signedParams)?>',
                                                                containerSelector: '#similar_products_cont',
                                                                sliderSelector:    '.js-popular-product'
        });
    </script><?php

    echo '<div id="similar_products_cont"></div>';

    return;
}

//
// Вывод результата
//
if ($arResult['RESULT_TYPE'] === 'RESULT') {
    if ($arParams['DEFERRED_LOAD'] === 'Y') {
        ob_start();
    }

    if ($arResult['PRODUCTS']) {
        ?><div class="b-container">
            <section class="b-common-section">
                <div class="b-common-section__title-box b-common-section__title-box--popular b-common-section__title-box--product">
                    <h2 class="b-title b-title--popular b-title--product"><?=Loc::getMessage('SIMILAR_PRODUCTS.TITLE')?></h2><?php
                ?></div>
                <div class="b-common-section__content b-common-section__content--popular b-common-section__content--product js-popular-product"><?php
                    foreach ($arResult['PRODUCTS'] as $product) {
                        $productId = $product->getId();
                        $APPLICATION->IncludeComponent(
                            'fourpaws:catalog.element.snippet',
                            'vertical',
                            [
                                'PRODUCT' => $product,
                                'BIG_DATA' => [
                                    'RCM_ID' => isset($arResult['recommendationIdToProduct'][$productId]) ? $arResult['recommendationIdToProduct'][$productId] : '',
                                    'cookiePrefix' => isset($arResult['BIG_DATA_SETTINGS']['js']['cookiePrefix']) ? $arResult['BIG_DATA_SETTINGS']['js']['cookiePrefix'] : '',
                                    'cookieDomain' => isset($arResult['BIG_DATA_SETTINGS']['js']['cookieDomain']) ? $arResult['BIG_DATA_SETTINGS']['js']['cookieDomain'] : '',
                                    'serverTime' => isset($arResult['BIG_DATA_SETTINGS']['js']['serverTime']) ? $arResult['BIG_DATA_SETTINGS']['js']['serverTime'] : '',
                                ],
                            ],
                            $component,
                            [
                                'HIDE_ICONS' => 'Y'
                            ]
                        );
                    }
                ?></div>
            </section>
        </div><?php
    }

    if ($arParams['DEFERRED_LOAD'] === 'Y') {
        // для отложенной загрузки через ajax-запрос результат отдаем в виде json
        $result = [];
        $result['HTML'] = ob_get_clean();
        $result['JS'] = \Bitrix\Main\Page\Asset::getInstance()->getJs();
        echo \Bitrix\Main\Web\Json::encode($result);
    }
}
