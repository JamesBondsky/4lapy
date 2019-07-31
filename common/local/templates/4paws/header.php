<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var \CMain $APPLICATION
 */

use Bitrix\Main\Application;
use Bitrix\Main\Page\Asset;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application as PawsApplication;
use FourPaws\App\MainTemplate;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\KioskBundle\Service\KioskService;
use FourPaws\PersonalBundle\Service\PersonalOffersService;
use FourPaws\SaleBundle\Service\BasketViewService;
use FourPaws\UserBundle\Enum\UserLocationEnum;

/** @var MainTemplate $template */
$template = MainTemplate::getInstance(Application::getInstance()
    ->getContext());
$markup = PawsApplication::markup(); 

/**
 * @var $sViewportCookie - Значение куки отвечающе за переключение вьпорта с мобильного на десктоп.
 */
$sViewportCookie = $_COOKIE['viewport'] ?? null;

$bodyClass = '';
if(KioskService::isKioskMode()) { $bodyClass = 'body-kiosk js-body-kiosk'; }

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <? /** onesignal.com manifest.json, must appear before any other link <link rel="manifest" ...> */?>
    <? if ($USER->IsAdmin()) { /** [todo] remove after production tests */?>
        <? if (getenv('ONESIGNAL_API_KEY')) {?>
            <link rel="manifest" href="/manifest.json">
        <?}?>
    <?}?>

    <base href="<?= PawsApplication::getInstance()
        ->getSiteDomain() ?>">
    <?
        if ($sViewportCookie == 'desktop') {
            echo "<meta name=\"viewport\" content=\"width=1300px\">";
        } else {
            echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=1, minimal-ui, user-scalable=no\">";
        }
    ?>
    <meta name="skype_toolbar" content="skype_toolbar_parser_compatible">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="google" content="notranslate">
    <meta name="format-detection" content="telephone=no">
    <meta name="yandex-verification" content="6266e34669b85ed6">
    <?php /** @todo Mobe onto right place  */ ?>
    <script src="/static/build/js/jquery/jquery.min.js"></script>
    <script data-skip-moving="true">
        window.js_static = '/static/build/';
        window._global = {};
        window._global.locationCookieCode = '<?= UserLocationEnum::DEFAULT_LOCATION_COOKIE_CODE ?>';
        window.dataLayer = window.dataLayer || [];
    </script>
    <?php $APPLICATION->ShowHead(); ?>
    <title><?php $APPLICATION->ShowTitle() ?></title>
    <?php
    $asset = Asset::getInstance();
    $asset->addCss($markup->getCssFile());
    $asset->addJs('//api-maps.yandex.ru/2.1/?apikey=ad666cd3-80be-4111-af2d-209dddf2c55e&lang=ru_RU&load=package.full');
    //$asset->addJs('/api-maps.yandex.ru.js');
    $asset->addJs('https://www.google.com/recaptcha/api.js?hl=ru');

    /** onesignal.com */
    if (getenv('ONESIGNAL_API_KEY')) {
        $asset->addString('<script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async=""></script>');
        $asset->addString('
            <script>
              var OneSignal = window.OneSignal || [];
              OneSignal.push(function() {
                OneSignal.init({
                  appId: \''.getenv('ONESIGNAL_API_KEY').'\',
                  autoRegister: true,
                  welcomeNotification: {
                    "title": "Зоомагазин \"Четыре лапы\"",
                    "message": "Спасибо за подписку!"
                  }
                });
              });
            </script>
        ');
    }

    ?>

    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/local/include/blocks/counters_header.php'; ?>
</head>
<body <? if($bodyClass != ''){ ?>class="<?= $bodyClass ?>"<? } ?>>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/local/include/blocks/pixel_vk.php'; ?>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/local/include/blocks/counters_body.php'; ?>
<?php if (!KioskService::isKioskMode()) {
    $APPLICATION->ShowPanel();
} ?>

<header class="b-header <?= $template->getHeaderClass() ?> js-header">
    <?php
        if(!KioskService::isKioskMode()
            && !$template->isBasket()
            && !$template->isOrderPage()) {
            require_once __DIR__ . '/blocks/header/promo_top_dobrolap.php';
        }
    ?>
    <?php
    $APPLICATION->IncludeComponent('articul:header.mobile.bunner',
        '',
        [],
        false,
        []
    );
    ?>
    <div class="b-container">
        <?php if ($template->hasShortHeaderFooter()) { ?>
            <div class="b-header__info b-header__info--short-header">
                <a class="b-logo"
                   href="/"
                   title="">
                    <img src="/static/build/images/inhtml/logo.svg"
                         alt="Четыре лапы"
                         title="Четыре лапы"/>
                </a>
                <span class="b-header__phone-short-header">
                        <?php $APPLICATION->IncludeComponent('fourpaws:city.phone',
                            'template.header.short',
                            [],
                            false,
                            ['HIDE_ICONS' => 'Y']) ?>
                    </span>
                <? if (!KioskService::isKioskMode()) { ?>
                    <div class="b-header-info b-header-info--short-header js-hide-open-menu">
                        <?php require_once __DIR__ . '/blocks/header/phone_block.php' ?>
                    </div>
                <? } ?>
            </div>
        <?php } else { ?>
	        <?
            if(!$template->isPersonalOffers() && $USER->IsAuthorized()) {
                $modal_counts_txt = CUser::GetByID( $USER->GetID() )->Fetch()['UF_MODALS_CNTS'];
                $modal_counts = explode(' ', $modal_counts_txt);

                /** @var PersonalOffersService $personalOffersService */
                $personalOffersService = PawsApplication::getInstance()->getContainer()->get('personal_offers.service');
                $userId = $USER->GetID();
                try {
                    $userPersonalOffers = $personalOffersService->getActiveUserCoupons($userId, true);
                } catch (\Exception $e) {
                    $userPersonalOffers = [];
                }

                if ($userPersonalOffers) {
	                /** @var ArrayCollection $coupons */
	                $coupons = $userPersonalOffers['coupons'];

	                if (!$coupons->isEmpty() && $modal_counts[3] <= 2
		                && $USER->GetParam('data_collect') !== 'Y' // модалку в сессии еще не показали
	                ) {
	                    $modal_number = 4;
                        $lastCouponOffer = $userPersonalOffers['offers']->get($coupons->get(0)['UF_OFFER']);
	                }
                }
            }
	        ?>
            <div class="b-header__info">
                <a class="b-hamburger b-hamburger--mobile-menu js-hamburger-menu-mobile"
                   href="javascript:void(0);"
                   title="">
                    <span class="b-hamburger__hamburger-icon"></span>
                </a>
                <a class="b-hamburger js-hamburger-menu-main" href="javascript:void(0);" title="">
                    <span class="b-icon b-icon--hamburger">
                        <?= new SvgDecorator('icon-hamburger', 24, 18) ?>
                    </span>
                </a>
                <a class="b-logo" href="<?= PawsApplication::getInstance()->getSiteCurrentDomain() ?>" title="">
                    <img src="/static/build/images/inhtml/logo.svg" alt="Четыре лапы" title="Четыре лапы"/>
                </a>
                <?php
                $APPLICATION->IncludeComponent('fourpaws:catalog.search.form',
                    '',
                    [],
                    false,
                    ['HIDE_ICONS' => 'Y']);
                ?>
                <div class="b-header-info">
                    <? if (!KioskService::isKioskMode()) {
                      require_once __DIR__ . '/blocks/header/phone_block.php';
                    } ?>
                    <?php $APPLICATION->IncludeComponent('fourpaws:auth.form',
                        '',
                        [
                            'NOT_SEEN_COUPONS' => isset($coupons) ? $coupons->count() : '',
                        ],
                        false,
                        ['HIDE_ICONS' => 'Y']);

                    echo PawsApplication::getInstance()
                        ->getContainer()
                        ->get(BasketViewService::class)
                        ->getMiniBasketHtml(); ?>

	                <?
				    if($USER->GetParam('data_collect') !== 'Y') // модалку в сессии еще не показали
				    {
		                if ($modal_number === 4) { ?>
			                <?
			                if ($lastCouponOffer) {
				                $offerDiscountText = ($lastCouponOffer['PROPERTY_DISCOUNT_VALUE'] ? $lastCouponOffer['PROPERTY_DISCOUNT_VALUE'] . '%' :
					                ($lastCouponOffer['PROPERTY_DISCOUNT_CURRENCY_VALUE'] ? $lastCouponOffer['PROPERTY_DISCOUNT_CURRENCY_VALUE'] . ' ₽' : '')
				                );
				                ?>
			                    <div class="b-person-coupon js-coupon-person-popup" data-id="<?= $coupons->get(0)['PERSONAL_COUPON_USER_COUPONS_ID'] ?>">
			                        <div class="b-person-coupon__inner">
			                            <div class="b-person-coupon__close js-close-person-coupon-popup"></div>
				                        <? if ($offerDiscountText) { ?>
			                                <div class="b-person-coupon__persent">-<?= $offerDiscountText ?></div>
										<? } ?>
			                            <div class="b-person-coupon__descr"><?= $lastCouponOffer['~PREVIEW_TEXT'] ?></div>
			                            <a href="/personal/personal-offers/" class="b-person-coupon__btn">Подробнее</a>
			                        </div>
			                    </div>
							<?
			                }
		                }
				    }
	                ?>
                </div>
            </div>
            <div class="b-header__menu js-minimal-menu js-nav-first-desktop">
                <?php
                /**
                 * Основное меню.
                 * dropdown передается через header_dropdown_menu
                 */
                $APPLICATION->IncludeComponent(
                    'fourpaws:iblock.main.menu',
                    'fp.17.0.top',
                    [
                        'MENU_IBLOCK_TYPE'          => IblockType::MENU,
                        'MENU_IBLOCK_CODE'          => IblockCode::MAIN_MENU,
                        'PRODUCTS_IBLOCK_TYPE'      => IblockType::CATALOG,
                        'PRODUCTS_IBLOCK_CODE'      => IblockCode::PRODUCTS,
                        'CACHE_TIME'                => 3600,
                        'CACHE_TYPE'                => 'A',
                        'MAX_DEPTH_LEVEL'           => '4',
                        // N - шаблон кэшируется
                        'CACHE_SELECTED_ITEMS'      => 'N',
                        'TEMPLATE_NO_CACHE'         => 'N',
                        // количество популярных брендов в пункте меню "Товары по питомцу"
                        'BRANDS_POPULAR_LIMIT'      => '6',
                        // количество популярных брендов в пункте меню "По бренду"
                        'BRANDS_MENU_POPULAR_LIMIT' => '8',
                        // режим киоска
                        'IS_KIOSK'                  => KioskService::isKioskMode(),
                    ],
                    null,
                    [
                        'HIDE_ICONS' => 'Y',
                    ]
                );
                ?>
                <?php
                if(!KioskService::isKioskMode()){
                    $APPLICATION->IncludeComponent('fourpaws:city.selector',
                        '',
                        ['GET_STORES' => false],
                        false,
                        ['HIDE_ICONS' => 'Y']);
                }
                ?>
                <?php $APPLICATION->IncludeComponent('fourpaws:city.delivery.info',
                    'template.header',
                    ['CACHE_TIME' => 3600 * 24],
                    false,
                    ['HIDE_ICONS' => 'Y']); ?>
            </div>
        <?php } ?>
    </div>
</header>
<?php
/**
 * Основное меню. dropdown
 */
$APPLICATION->ShowViewContent('header_dropdown_menu'); ?>
<div class="b-page-wrapper <?= $template->getWrapperClass() ?> js-this-scroll">
    <?php
    if (!KioskService::isKioskMode()) {
        require_once __DIR__ . '/blocks/header/social_bar.php';
    }
    ?>

    <?php if ($template->hasMainWrapper()) { ?>
    <main class="b-wrapper<?= $template->getIndexMainClass() ?>" role="main">
        <?php if ($template->hasHeaderPublicationListContainer()) { ?>
        <div class="<?php $APPLICATION->ShowProperty('PUBLICATION_LIST_CONTAINER_1',
            'b-container b-container--news') ?>">
            <div class="<?php $APPLICATION->ShowProperty('PUBLICATION_LIST_CONTAINER_2', 'b-news') ?>">
                <h1 class="b-title b-title--h1"><?php $APPLICATION->ShowTitle(false) ?></h1>
                <?php
                }

                if ($template->hasHeaderDetailPageContainer()) {
                    ?>
                    <div class="<?php $APPLICATION->ShowProperty('PUBLICATION_DETAIL_CONTAINER_1',
                        'b-container b-container--news-detail') ?>">
                        <div class="<?php $APPLICATION->ShowProperty('PUBLICATION_DETAIL_CONTAINER_2',
                            'b-detail-page') ?>">
                            <?php
                            $APPLICATION->IncludeComponent('bitrix:breadcrumb',
                                'breadcrumb',
                                [
                                    'PATH'       => '',
                                    'SITE_ID'    => SITE_ID,
                                    'START_FROM' => '0',
                                ]); ?>
                            <h1 class="b-title b-title--h1">
                                <?php $APPLICATION->ShowTitle(false) ?>
                            </h1>
                            <?php
                            $APPLICATION->ShowViewContent('header_news_display_date'); ?>
                        </div>
                    </div>
                <?php }

                if ($template->hasHeaderPersonalContainer()) { ?>
                <div class="b-account">
                    <div class="b-container b-container--account">
                        <div class="b-account__wrapper-title">
                            <h1 class="b-title b-title--h1"><?php $APPLICATION->ShowTitle(false) ?></h1>
                        </div>
                        <?php $APPLICATION->IncludeComponent('bitrix:menu',
                            'personal.menu',
                            [
                                'COMPONENT_TEMPLATE'    => 'personal.menu',
                                'ROOT_MENU_TYPE'        => 'personal_cab',
                                'MENU_CACHE_TYPE'       => 'A',
                                'MENU_CACHE_TIME'       => '360000',
                                'MENU_CACHE_USE_GROUPS' => 'Y',
                                'CACHE_SELECTED_ITEMS'  => 'N',
                                'TEMPLATE_NO_CACHE'     => 'N',
                                'MENU_CACHE_GET_VARS'   => [],
                                'MAX_LEVEL'             => '1',
                                'CHILD_MENU_TYPE'       => 'personal_cab',
                                'USE_EXT'               => 'N',
                                'DELAY'                 => 'N',
                                'ALLOW_MULTI_SELECT'    => 'N',
                            ],
                            false); ?>
                        <main class="b-account__content" role="main">
                            <?php }

                            if ($template->hasHeaderBlockShopList()) { ?>
                            <div class="b-stores">
                                <div class="b-container">
                                    <div class="b-stores__top">
                                        <h1 class="b-title b-title--h1 b-title--stores-header"><?php $APPLICATION->ShowTitle(false) ?></h1>
                                        <div class="b-stores__info">
                                            <p><?= tplvar('shops_subtitle', true) ?></p>
                                        </div>
                                    </div>
<?php }
}

if ($template->hasContent()) {
    $asset->addCss('/include/static/style.css');
    $asset->addJs('/include/static/scripts.js');

    $APPLICATION->IncludeComponent('bitrix:main.include',
        '',
        [
            'AREA_FILE_SHOW' => 'file',
            'PATH'           => sprintf('/include/%s.php', trim($template->getPath(), '/')),
        ],
        false);
}
