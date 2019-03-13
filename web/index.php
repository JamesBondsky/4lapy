<?php

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->SetPageProperty('title', 'Интернет-зоомагазин Четыре Лапы – продажа и доставка зоотоваров по Москве, Московской области и всей России');
$APPLICATION->SetPageProperty('description', '');
$APPLICATION->SetPageProperty('keywords', '');
$APPLICATION->SetPageProperty('NOT_SHOW_NAV_CHAIN', 'Y');
$APPLICATION->SetTitle('Интернет-зоомагазин Четыре Лапы – продажа и доставка зоотоваров по Москве, Московской области и всей России');

$APPLICATION->IncludeComponent('bitrix:news.list',
    'index.slider',
    [
        'COMPONENT_TEMPLATE'              => 'index.slider',
        'IBLOCK_TYPE'                     => IblockType::PUBLICATION,
        'IBLOCK_ID'                       => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::BANNERS),
        'NEWS_COUNT'                      => '20',
        'SORT_BY1'                        => 'SORT',
        'SORT_ORDER1'                     => 'ASC',
        'SORT_BY2'                        => 'ACTIVE_FROM',
        'SORT_ORDER2'                     => 'DESC',
        'FILTER_NAME'                     => '',
        'FIELD_CODE'                      => [
            0 => 'NAME',
            1 => 'PREVIEW_PICTURE',
            2 => 'DETAIL_PICTURE',
            3 => '',
        ],
        'PROPERTY_CODE'                   => [
            0 => 'LINK',
            1 => 'IMG_TABLET',
            2 => 'BACKGROUND',
        ],
        'CHECK_DATES'                     => 'Y',
        'DETAIL_URL'                      => '',
        'AJAX_MODE'                       => 'N',
        'AJAX_OPTION_JUMP'                => 'N',
        'AJAX_OPTION_STYLE'               => 'N',
        'AJAX_OPTION_HISTORY'             => 'N',
        'AJAX_OPTION_ADDITIONAL'          => '',
        'CACHE_TYPE'                      => 'A',
        'CACHE_TIME'                      => '36000000',
        'CACHE_FILTER'                    => 'Y',
        'CACHE_GROUPS'                    => 'N',
        'PREVIEW_TRUNCATE_LEN'            => '',
        'ACTIVE_DATE_FORMAT'              => '',
        'SET_TITLE'                       => 'N',
        'SET_BROWSER_TITLE'               => 'N',
        'SET_META_KEYWORDS'               => 'N',
        'SET_META_DESCRIPTION'            => 'N',
        'SET_LAST_MODIFIED'               => 'N',
        'INCLUDE_IBLOCK_INTO_CHAIN'       => 'N',
        'ADD_SECTIONS_CHAIN'              => 'N',
        'HIDE_LINK_WHEN_NO_DETAIL'        => 'N',
        'PARENT_SECTION'                  => '',
        'PARENT_SECTION_CODE'             => 'slider_main',
        'INCLUDE_SUBSECTIONS'             => 'N',
        'STRICT_SECTION_CHECK'            => 'N',
        'DISPLAY_DATE'                    => 'N',
        'DISPLAY_NAME'                    => 'N',
        'DISPLAY_PICTURE'                 => 'N',
        'DISPLAY_PREVIEW_TEXT'            => 'N',
        'PAGER_TEMPLATE'                  => '',
        'DISPLAY_TOP_PAGER'               => 'N',
        'DISPLAY_BOTTOM_PAGER'            => 'N',
        'PAGER_TITLE'                     => '',
        'PAGER_SHOW_ALWAYS'               => 'N',
        'PAGER_DESC_NUMBERING'            => 'N',
        'PAGER_DESC_NUMBERING_CACHE_TIME' => '',
        'PAGER_SHOW_ALL'                  => 'N',
        'PAGER_BASE_LINK_ENABLE'          => 'N',
        'SET_STATUS_404'                  => 'N',
        'SHOW_404'                        => 'N',
        'MESSAGE_404'                     => '',
    ],
    false,
    ['HIDE_ICONS' => 'Y']);

/** main sections by mobile */
$APPLICATION->IncludeComponent('bitrix:menu',
    'mobile.root_section_menu',
    [
        'COMPONENT_TEMPLATE'    => 'mobile.root_section_menu',
        'ROOT_MENU_TYPE'        => 'mobile_root_section',
        'MENU_CACHE_TYPE'       => 'A',
        'MENU_CACHE_TIME'       => '360000',
        'MENU_CACHE_USE_GROUPS' => 'Y',
        'CACHE_SELECTED_ITEMS'  => 'N',
        'TEMPLATE_NO_CACHE'     => 'N',
        'MENU_CACHE_GET_VARS'   => [],
        'MAX_LEVEL'             => '1',
        'CHILD_MENU_TYPE'       => 'mobile_root_section',
        'USE_EXT'               => 'N',
        'DELAY'                 => 'N',
        'ALLOW_MULTI_SELECT'    => 'N',
    ],
    false);

/**
 * Популярные товары
 */
$APPLICATION->IncludeComponent(
    'bitrix:main.include',
    '',
    [
        'AREA_FILE_SHOW' => 'file',
        'PATH'           => '/local/include/blocks/index.popular_products.php',
        'EDIT_TEMPLATE'  => '',
    ],
    null,
    [
        'HIDE_ICONS' => 'Y',
    ]
);
?>

<section class="b-promo-banner">
    <div class="b-container">
        <div class="b-promo-banner__list js-promo-banner">
            <?/*div class="b-promo-banner-item">
                <div class="b-promo-banner-item__content">
                    <div class="b-promo-banner-item__left">
                        <div class="b-promo-banner-item__logo"></div>
                        <div class="b-promo-banner-item__img">
                            <img src="/static/build/images/inhtml/promo-banner_express.png" alt=""/>
                        </div>
                    </div>
                    <div class="b-promo-banner-item__descr">
                        Экспресс доставка по&nbsp;Москве! Привезем заказ через 3 часа!
                    </div>
                    <div class="b-promo-banner-item__link-wrap">
                        <a class="b-promo-banner-item__link" href="#">Подробнее</a>
                    </div>
                </div>
            </div*/?>
            <div class="b-promo-banner-item">
                <div class="b-promo-banner-item__content">
                    <div class="b-promo-banner-item__left">
                        <div class="b-promo-banner-item__logo"></div>
                        <div class="b-promo-banner-item__img">
                            <img src="/static/build/images/inhtml/promo-banner_application.png" alt=""/>
                        </div>
                    </div>
                    <div class="b-promo-banner-item__descr">
                        Удобные мобильные<br/> приложения для Android и&nbsp;IOS
                    </div>
                    <div class="b-promo-banner-item__link-wrap">
                        <a class="b-promo-banner-item__link" href="/shares/mobilnoe_prilozhenie_chetyre_lapy.html">Подробнее</a>
                    </div>
                </div>
            </div>
            <div class="b-promo-banner-item">
                <div class="b-promo-banner-item__content">
                    <div class="b-promo-banner-item__left">
                        <div class="b-promo-banner-item__logo"></div>
                        <div class="b-promo-banner-item__img">
                            <img src="/static/build/images/inhtml/promo-banner_pickup.png" alt=""/>
                        </div>
                    </div>
                    <div class="b-promo-banner-item__descr">
                        Бесплатный самовывоз заказа через час из&nbsp;230+ магазинов!
                    </div>
                    <div class="b-promo-banner-item__link-wrap">
                        <a class="b-promo-banner-item__link" href="/shops/">Подробнее</a>
                    </div>
                </div>
            </div>
            <div class="b-promo-banner-item b-promo-banner-item--big-text">
                <div class="b-promo-banner-item__content">
                    <div class="b-promo-banner-item__left">
                        <div class="b-promo-banner-item__logo"></div>
                        <div class="b-promo-banner-item__img">
                            <img src="/static/build/images/inhtml/promo-banner_region.png" alt=""/>
                        </div>
                    </div>
                    <div class="b-promo-banner-item__descr">
                        Бесплатная доставка по&nbsp;Москве в&nbsp;день заказа!<br/> Доставим в&nbsp;любой <nobr>4-х</nobr> часовой интервал
                    </div>
                    <div class="b-promo-banner-item__link-wrap">
                        <a class="b-promo-banner-item__link" href="/payment-and-delivery/">Подробнее</a>
                    </div>
                </div>
            </div>
            <div class="b-promo-banner-item b-promo-banner-item--dark">
                <div class="b-promo-banner-item__content">
                    <div class="b-promo-banner-item__left">
                        <div class="b-promo-banner-item__logo"></div>
                        <div class="b-promo-banner-item__img">
                            <img src="/static/build/images/inhtml/promo-banner_acarid.png" alt=""/>
                        </div>
                    </div>
                    <div class="b-promo-banner-item__descr">
                        Бесплатная обработка<br/> от&nbsp;клещей и&nbsp;блох в&nbsp;магазинах!
                    </div>
                    <div class="b-promo-banner-item__link-wrap">
                        <a class="b-promo-banner-item__link" href="/catalog/veterinarnaya-apteka/zashchita-ot-blokh-i-kleshchey/">Подробнее</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
/**
 * Распродажа
 */
$APPLICATION->IncludeComponent(
    'bitrix:main.include',
    '',
    [
        'AREA_FILE_SHOW' => 'file',
        'PATH'           => '/local/include/blocks/index.sale_products.php',
        'EDIT_TEMPLATE'  => '',
    ],
    null,
    [
        'HIDE_ICONS' => 'Y',
    ]
);

/**
 * Преимущества
 */
$APPLICATION->IncludeComponent(
    'bitrix:main.include',
    '',
    [
        'AREA_FILE_SHOW' => 'file',
        'PATH'           => '/local/include/blocks/advantages.php',
        'EDIT_TEMPLATE'  => '',
    ],
    null,
    [
        'HIDE_ICONS' => 'N',
    ]
);

/**
 * Контейнер страницы. Не должен редактироваться в визуальном редакторе. Закрывается перед подключением подвала.
 */
echo '<div class="b-container">';

/**
 * Популярные бренды
 */
$APPLICATION->IncludeComponent(
    'bitrix:main.include',
    '',
    [
        'AREA_FILE_SHOW' => 'file',
        'PATH'           => '/local/include/blocks/index.popular_brands.php',
        'EDIT_TEMPLATE'  => '',
    ],
    null,
    [
        'HIDE_ICONS' => 'Y',
    ]
);

$APPLICATION->IncludeComponent('bitrix:main.include',
    'index.pet_block',
    [
        'COMPONENT_TEMPLATE' => '.default',
        'AREA_FILE_SHOW'     => 'file',
        'PATH'               => '/local/include/blocks/index.pet_block.php',
        'EDIT_TEMPLATE'      => '',
    ],
    false);
/**
 * Новости и события.
 */
$APPLICATION->IncludeComponent('fourpaws:items.list',
    '',
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
        'FILTER_NAME'            => '',
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
    ['HIDE_ICONS' => 'Y']);

/**
 * Просмотренные товары
 */
$APPLICATION->IncludeComponent(
    'bitrix:main.include',
    '',
    [
        'AREA_FILE_SHOW' => 'file',
        'PATH'           => '/local/include/blocks/index.viewed_products.php',
        'EDIT_TEMPLATE'  => '',
    ],
    null,
    [
        'HIDE_ICONS' => 'Y',
    ]
);

/**
 * Контейнер текста на странице
 */
$APPLICATION->IncludeComponent(
    'bitrix:main.include',
    '',
    [
        'AREA_FILE_SHOW' => 'file',
        'PATH'           => '/local/include/blocks/index.seo_text.php',
        'EDIT_TEMPLATE'  => '',
    ],
    null,
    [
        'HIDE_ICONS' => 'N',
    ]
);

echo '</div>';

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
