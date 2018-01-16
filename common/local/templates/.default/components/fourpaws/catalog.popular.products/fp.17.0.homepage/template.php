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
// Если выбрана отложенная загрузка результата, отправляем ajax-запрос
//
if ($arResult['RESULT_TYPE'] === 'INITIAL' && !empty($arResult['BIG_DATA_SETTINGS'])) {
    $signer = new \Bitrix\Main\Security\Sign\Signer();
    $signedTemplate = $signer->sign($templateName, 'catalog.popular.products');
    $signedParams = $signer->sign(base64_encode(serialize($arResult['ORIGINAL_PARAMETERS'])), 'catalog.popular.products');

    ?><script type="text/javascript">
        (function() {
            window.FourPawsCatalogPopularProductsComponent = function(params) {
                this.siteId = params.siteId || '';
                this.ajaxId = params.ajaxId || '';
                this.template = params.template || '';
                this.componentPath = params.componentPath || '';
                this.parameters = params.parameters || '';
                this.containerSelector = params.containerSelector || '';

                this.bigData = params.bigData || {enabled: false};

                if (this.bigData.enabled) {
                    BX.cookie_prefix = this.bigData.js.cookiePrefix || '';
                    BX.cookie_domain = this.bigData.js.cookieDomain || '';
                    BX.current_server_time = this.bigData.js.serverTime;

                    BX.ready(BX.delegate(this.bigDataLoad, this));
                }
            };
            window.FourPawsCatalogPopularProductsComponent.prototype = {
                bigDataLoad: function() {
                    var url = this.bigData.requestBaseUrl + '?' + this.bigData.requestUrlParams;
                    var onReady = BX.delegate(
                        function (result) {
                            this.sendRequest({
                                action: 'deferredLoad',
                                bigData: 'Y',
                                items: result && result.items || [],
                                rid: result && result.id
                            });
                        },
                        this
                    );
                    BX.ajax({
                        method: 'GET',
                        dataType: 'json',
                        url: url,
                        timeout: 3,
                        onsuccess: onReady,
                        onfailure: onReady
                    });
                },
                sendRequest: function(data) {
                    var defaultData = {
                        siteId: this.siteId,
                        template: this.template,
                        parameters: this.parameters
                    };

                    if (this.ajaxId) {
                        defaultData.AJAX_ID = this.ajaxId;
                    }
                    var requestUrl = this.componentPath + '/ajax.php' + (document.location.href.indexOf('clear_cache=Y') !== -1 ? '?clear_cache=Y' : '');

                    BX.ajax({
                        url: requestUrl,
                        method: 'POST',
                        dataType: 'json',
                        timeout: 60,
                        data: BX.merge(defaultData, data),
                        onsuccess: BX.delegate(
                            function(result) {
                                if (!result) {
                                    return;
                                }
                                if (result.JS) {
                                    BX.ajax.processScripts(
                                        BX.processHTML(result.JS).SCRIPT,
                                        false,
                                        BX.delegate(
                                            function () {
                                                if (result.HTML) {
                                                    if (this.containerSelector) {
                                                        jQuery(this.containerSelector).replaceWith(result.HTML);
                                                    }
                                                }
                                            },
                                            this
                                        )
                                    );
                                } else if (result.HTML) {
                                    if (this.containerSelector) {
                                        jQuery(this.containerSelector).replaceWith(result.HTML);
                                    }
                                }
                            },
                            this
                        )
                    });
                }
            };
        })();

        var tmp = new FourPawsCatalogPopularProductsComponent({
            siteId: '<?=\CUtil::JSEscape(SITE_ID)?>',
            componentPath: '<?=\CUtil::JSEscape($componentPath)?>',
            bigData: <?=\CUtil::PhpToJSObject($arResult['BIG_DATA_SETTINGS'])?>,
            template: '<?=\CUtil::JSEscape($signedTemplate)?>',
            ajaxId: '<?=\CUtil::JSEscape($arParams['AJAX_ID'])?>',
            parameters: '<?=\CUtil::JSEscape($signedParams)?>',
            containerSelector: '#popular_products_cont'
        });
    </script><?php

    echo '<div id="popular_products_cont"></div>';

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
                <div class="b-common-section__title-box b-common-section__title-box--popular">
                    <h2 class="b-title b-title--popular"><?=Loc::getMessage('POPULAR_PRODUCTS_HOME.TITLE')?></h2>
                    <a class="b-link b-link--title b-link--title" href="javascript:void(0)" title="<?=Loc::getMessage('POPULAR_PRODUCTS_HOME.ALL_LINK_TITLE')?>">
                        <span class="b-link__text b-link__text--title"><?=Loc::getMessage('POPULAR_PRODUCTS_HOME.ALL_LINK')?></span>
                        <span class="b-link__mobile b-link__mobile--title"><?=Loc::getMessage('POPULAR_PRODUCTS_HOME.ALL')?></span><?php
                        echo new \FourPaws\Decorators\SvgDecorator('icon-arrow-right', 6, 10);
                    ?></a>
                </div>
                <div class="b-common-section__content b-common-section__content--popular js-popular-product"><?php
                    foreach ($arResult['PRODUCTS'] as $product) {
                        $productId = $product->getId();
                        $APPLICATION->IncludeComponent(
                            'fourpaws:catalog.element.snippet',
                            '',
                            [
                                'PRODUCT' => $product,
                                // При клике на ссылку по рекомендации нужно писать в куки 4LP_RCM_PRODUCT_LOG значение лога: {PRODUCT_ID}-{RCM_ID}-{current_server_time}
                                // записи для каждого элемента разделять точкой, пример: {PRODUCT_ID_1}-{RCM_ID_1}-{current_server_time_1}.{PRODUCT_ID_2}-{RCM_ID_2}-{current_server_time_2}
                                // При этом следует удалить из лога, хранимого в куке, все записи старше 30 дней, а саму куку следует хранить 10 лет
                                // (штатную реализация см. в js-функции rememberProductRecommendation шаблона bitrix:catalog.item)
                                'RCM_ID' => isset($arResult['recommendationIdToProduct'][$productId]) ? $arResult['recommendationIdToProduct'][$productId] : '',
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
        //$result['JS'] = \Bitrix\Main\Page\Asset::getInstance()->getJs();
        echo \Bitrix\Main\Web\Json::encode($result);
    }
}
