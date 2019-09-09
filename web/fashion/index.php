<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->SetPageProperty('title', 'Новая коллекция одежды для собак');
$APPLICATION->SetPageProperty('description', '');
$APPLICATION->SetTitle("Новая коллекция одежды для собак");

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\WordHelper;
use FourPaws\LocationBundle\LocationService;
use Symfony\Component\Templating\TemplateNameParser;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\Loader\FilesystemLoader;

?>
<div class="fashion-page">
    <section class="fashion-main-banner">
        <? $sectionFashion = CIBlockSection::GetList([], ['CODE' => 'fashion'], false, ['ID', 'NAME'])->Fetch();
        if($sectionFashion){
            $filterName = 'catalogSliderFilter';
            global ${$filterName};
            ${$filterName} = ['SECTION_CODE' => 'fashion'];
            $APPLICATION->IncludeComponent('bitrix:news.list',
                'index.slider',
                [
                    'COMPONENT_TEMPLATE' => 'fashion.slider',
                    'IBLOCK_TYPE' => IblockType::PUBLICATION,
                    'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::BANNERS),
                    'NEWS_COUNT' => '20',
                    'SORT_BY1' => 'SORT',
                    'SORT_ORDER1' => 'ASC',
                    'SORT_BY2' => 'ACTIVE_FROM',
                    'SORT_ORDER2' => 'DESC',
                    'FILTER_NAME' => $filterName,
                    'FIELD_CODE' => [
                        0 => 'NAME',
                        1 => 'PREVIEW_PICTURE',
                        2 => 'DETAIL_PICTURE',
                        3 => '',
                    ],
                    'PROPERTY_CODE' => [
                        0 => 'LINK',
                        1 => 'IMG_TABLET',
                        2 => 'BACKGROUND',
                    ],
                    'CHECK_DATES' => 'Y',
                    'DETAIL_URL' => '',
                    'AJAX_MODE' => 'N',
                    'AJAX_OPTION_JUMP' => 'N',
                    'AJAX_OPTION_STYLE' => 'N',
                    'AJAX_OPTION_HISTORY' => 'N',
                    'AJAX_OPTION_ADDITIONAL' => '',
                    'CACHE_TYPE' => 'A',
                    'CACHE_TIME' => '36000000',
                    'CACHE_FILTER' => 'Y',
                    'CACHE_GROUPS' => 'N',
                    'PREVIEW_TRUNCATE_LEN' => '',
                    'ACTIVE_DATE_FORMAT' => '',
                    'SET_TITLE' => 'N',
                    'SET_BROWSER_TITLE' => 'N',
                    'SET_META_KEYWORDS' => 'N',
                    'SET_META_DESCRIPTION' => 'N',
                    'SET_LAST_MODIFIED' => 'N',
                    'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
                    'ADD_SECTIONS_CHAIN' => 'N',
                    'HIDE_LINK_WHEN_NO_DETAIL' => 'N',
                    'PARENT_SECTION' => '',
                    'PARENT_SECTION_CODE' => '',
                    'INCLUDE_SUBSECTIONS' => 'N',
                    'STRICT_SECTION_CHECK' => 'N',
                    'DISPLAY_DATE' => 'N',
                    'DISPLAY_NAME' => 'N',
                    'DISPLAY_PICTURE' => 'N',
                    'DISPLAY_PREVIEW_TEXT' => 'N',
                    'PAGER_TEMPLATE' => '',
                    'DISPLAY_TOP_PAGER' => 'N',
                    'DISPLAY_BOTTOM_PAGER' => 'N',
                    'PAGER_TITLE' => '',
                    'PAGER_SHOW_ALWAYS' => 'N',
                    'PAGER_DESC_NUMBERING' => 'N',
                    'PAGER_DESC_NUMBERING_CACHE_TIME' => '',
                    'PAGER_SHOW_ALL' => 'N',
                    'PAGER_BASE_LINK_ENABLE' => 'N',
                    'SET_STATUS_404' => 'N',
                    'SHOW_404' => 'N',
                    'MESSAGE_404' => '',
                ],
                false,
                ['HIDE_ICONS' => 'Y']
            );
        }
        ?>
    </section>

    <section class="fashion-info">
        <div class="b-container">
            <div class="fashion-info__list">
                <div class="fashion-item-info fashion-item-info_small">
                    <div class="fashion-item-info__title">Новая коллекция</div>
                    <div class="fashion-item-info__descr">2000&nbsp;товаров одежды <br /> и&nbsp;обуви</div>
                </div>
                <div class="fashion-item-info">
                    <div class="fashion-item-info__title">скидки до&nbsp;15%</div>
                    <div class="fashion-item-info__descr">при покупке <nobr>2-х</nobr> вещей&nbsp;&mdash; 7%,<br/> <nobr>3-х</nobr> вещей&nbsp;&mdash; 10%, <nobr>4-х</nobr>&nbsp;&mdash; 15%</div>
                </div>
                <div class="fashion-item-info fashion-item-info_full hide-xs">
                    <div class="fashion-item-info__title">бесплатная доставка и&nbsp;примерка</div>
                    <div class="fashion-item-info__descr">
                        <span>закажите несколько размеров<br/> домой или в&nbsp;магазин</span>
                        <span class="fashion-item-info__arr"></span>
                        <span>примерьте и&nbsp;купите<br/> то, что подошло</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <? $APPLICATION->IncludeComponent('articul:fashion.product.slider', '') ?>

    <section class="fashion-info hide show-xs">
        <div class="b-container">
            <div class="fashion-info__list">
                <div class="fashion-item-info fashion-item-info_full">
                    <div class="fashion-item-info__title">бесплатная доставка и&nbsp;примерка</div>
                    <div class="fashion-item-info__descr">
                        <span>закажи несколько размеров<br/> домой или в&nbsp;магазин</span>
                        <span class="fashion-item-info__arr"></span>
                        <span>примерь и&nbsp;купи<br/> то, что подошло</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <? $APPLICATION->IncludeComponent('articul:fashion.product.footer', '') ?>

    <section class="fashion-info-banner">
        <div class="fashion-info-banner__title">
            <div class="b-container">
                Профессиональная защита от влаги, ветра и холода
            </div>
        </div>

        <div class="fashion-info-banner__img">
            <picture>
                <source media="(max-width: 767px)" srcset="/fashion/img/fashion-info-banner_mobile.jpg">
                <img src="/fashion/img/fashion-info-banner.jpg" alt="Новая коллекция одежды для собак" />
            </picture>
        </div>
    </section>

    <? $APPLICATION->IncludeComponent('articul:fashion.product.footer', 'rungo', ['SECTION_CODE' => 'rungo', 'TYPE' => 'rungo']) ?>

    <section class="fashion-measure-dog" data-measure-dog-fashion="true">
        <?
        $filesystemLoader = new FilesystemLoader($_SERVER['DOCUMENT_ROOT'].'/../src/CatalogBundle/Resources/views/Catalog/%name%');
        $templating = new PhpEngine(new TemplateNameParser(), $filesystemLoader);
        echo $templating->render('landing.fitting.html.php', ['hide_info' => true]); ?>
    </section>

    <?
    // Стоимость доставки
    /** @var DeliveryService $deliveryService */
    $deliveryService = Application::getInstance()->getContainer()->get('delivery.service');

    /** @var CalculationResultInterface[] $deliveries */
    $deliveries = $deliveryService->getByLocation();
    $filtered = array_filter(
        $deliveries,
        function (CalculationResultInterface $delivery) use ($deliveryService) {
            return $deliveryService->isDelivery($delivery);
        }
    );
    $deliveryResult = reset($filtered);
    ?>

    <section class="fashion-free-shipping">
        <div class="b-container">
            <div class="fashion-free-shipping__content">
                <div class="fashion-free-shipping__img"></div>
                <div class="fashion-free-shipping__info">
                    <div class="fashion-free-shipping__title">Бесплатная примерка</div>
                    <ul class="fashion-free-shipping__steps">
                        <li>примерьте несколько размеров</li>
                        <li>купите только то, что подошло</li>
                    </ul>
                    <div class="item-free-shipping">
                        <div class="item-free-shipping__title">домой</div>
                        <div class="item-free-shipping__descr item-free-shipping__descr_time"><b>время примерки</b> 15&nbsp;минут</div>
                        <div class="item-free-shipping__descr item-free-shipping__descr_delivery">
                            <? if($deliveryResult) { ?>
                                <p><b>доставка</b> от&nbsp;<?= WordHelper::numberFormat($deliveryResult->getPrice(), 0) ?> Р
                                    <?php if (!empty($deliveryResult->getFreeFrom())) { ?>
                                    <br/> бесплатно&nbsp;- при заказе от&nbsp;<?= WordHelper::numberFormat($deliveryResult->getFreeFrom(), 0) ?>р</p>
                                    <?php } ?>
                                <p>курьер привезёт ваш заказ в&nbsp;удобное место и&nbsp;время</p>
                            <? } ?>
                        </div>
                    </div>
                    <div class="item-free-shipping">
                        <div class="item-free-shipping__title">в магазин</div>
                        <div class="item-free-shipping__descr item-free-shipping__descr_time"><b>время примерки</b> не ограничено</div>
                        <div class="item-free-shipping__descr item-free-shipping__descr_delivery">
                            <p><b>доставка</b> бесплатно</p>
                            <p>продавец поможет одеть питомца, а&nbsp;в&nbsp;случае необходимости подобрать альтернативу</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?
    $section = CIBlockSection::GetList([], ['CODE' => 'fashion-dogs'])->Fetch();
    if($section){
        global $arNewsFilter;
        $arNewsFilter = ['SECTION_ID' => [$section['ID']]];
        ?>
        <section class="fashion-interesting-clothes">
            <div class="b-container">
                <h2 class="fashion-title txt-center">Интересное про одежду</h2>
                <?
                $APPLICATION->IncludeComponent('fourpaws:items.list',
                    'fashion',
                    [
                        'ACTIVE_DATE_FORMAT'     => 'j F Y',
                        'AJAX_MODE'              => 'N',
                        'AJAX_OPTION_ADDITIONAL' => '',
                        'AJAX_OPTION_HISTORY'    => 'N',
                        'AJAX_OPTION_JUMP'       => 'N',
                        'AJAX_OPTION_STYLE'      => 'Y',
                        'CACHE_FILTER'           => 'Y',
                        'CACHE_GROUPS'           => 'N',
                        'CACHE_TIME'             => '36000000',
                        'CACHE_TYPE'             => 'A',
                        'CHECK_DATES'            => 'Y',
                        'FIELD_CODE'             => [
                            '',
                        ],
                        'FILTER_NAME'            => 'arNewsFilter',
                        'IBLOCK_ID'              => [
                            IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::NEWS),
                            IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::ARTICLES),
                        ],
                        'IBLOCK_TYPE'            => IblockType::PUBLICATION,
                        'NEWS_COUNT'             => '7',
                        'PREVIEW_TRUNCATE_LEN'   => '',
                        'PROPERTY_CODE'          => [
                            'PUBLICATION_TYPE',
                            'VIDEO',
                        ],
                        'SET_LAST_MODIFIED'      => 'N',
                        'SORT_BY1'               => 'ACTIVE_FROM',
                        'SORT_BY2'               => 'SORT',
                        'SORT_ORDER1'            => 'DESC',
                        'SORT_ORDER2'            => 'ASC',
                    ],
                    false,
                    ['HIDE_ICONS' => 'Y']
                );
                ?>
            </div>
        </section>
    <? } ?>
</div>

<script>
    window.addEventListener('load', function() {
        var items = document.querySelectorAll('.fashion-page .measure_dog__button.js-scroll-to-catalog, .fashion-page .b-news-item__link, .fashion-page .measure_dog__steps a, .b-main-slider a.b-main-item__link-main');

        for (var i = 0; i < items.length; i++) {
            items[i].setAttribute('target', '_blank');
            items[i].target = '_blank';
        }
    });
</script>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>
