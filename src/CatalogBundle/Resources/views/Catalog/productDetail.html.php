<?php
/**
 * @var ProductDetailRequest $productDetailRequest
 * @var CatalogLandingService $landingService
 * @var Request $request
 * @var CMain $APPLICATION
 */

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\SystemException;
use FourPaws\App\MainTemplate;
use FourPaws\Catalog\Model\Category;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\App\Templates\ViewsEnum;
use FourPaws\BitrixOrm\Model\IblockElement;
use FourPaws\Catalog\Model\Product;
use FourPaws\CatalogBundle\Dto\ProductDetailRequest;
use FourPaws\CatalogBundle\Helper\MarkHelper;
use FourPaws\CatalogBundle\Service\CatalogLandingService;
use FourPaws\Components\CatalogElementDetailComponent;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Helpers\DateHelper;
use FourPaws\Helpers\HighloadHelper;
use FourPaws\KioskBundle\Service\KioskService;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use FourPaws\Helpers\WordHelper;
use FourPaws\Catalog\Model\Offer;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

global $APPLICATION;

$logger = LoggerFactory::create('productDetail');
$offerId = $productDetailRequest->getOfferId();
$isPopup = MainTemplate::getInstance()->isCatalogPopup();

/** @var Product $product */
$product = $APPLICATION->IncludeComponent(
    'fourpaws:catalog.element.detail',
    '',
    [
        'CODE' => $productDetailRequest->getProductSlug(),
        'OFFER_ID' => $offerId,
        'SET_TITLE' => 'Y',
        'SHOW_FAST_ORDER' => $productDetailRequest->getZone() !== DeliveryService::ZONE_4 && !KioskService::isKioskMode() && !$isPopup,
        'IS_POPUP' => $isPopup,
    ],
    false,
    ['HIDE_ICONS' => 'Y']
);

if (!($product instanceof Product)) {
    $logger->error('Нет итема');
    /** прерываем если вернулось непонятно что */
    return;
}

/** @var Category $rootCategory */
$rootCategory = $product->getFullPathCollection()->last();

$offer = null;
CBitrixComponent::includeComponentClass('fourpaws:personal.profile');
/** @noinspection ExceptionsAnnotatingAndHandlingInspection */
try {
    $catalogElementDetailClass = new CatalogElementDetailComponent();
    try {
        $offer = $catalogElementDetailClass->getCurrentOffer($product, $offerId);
    } catch (LoaderException | NotSupportedException | ObjectNotFoundException $e) {
        $logger->error('ошибка при получении оффера');
        /** ошибки быть не должно */
    }
} catch (SystemException | RuntimeException | ServiceNotFoundException $e) {
    $logger->error('ошибка при загрузке класса компонента');
    /** ошибки быть не должно, так как компонент отрабатывает выше */
    return;
}

if (null === $offer) {
    /** нет оффера что-то пошло не так */
    $logger->error('Нет оффера');
    return;
}
?>
    <div class="b-product-card"
         data-productid="<?= $product->getId() ?>"
         data-offerId="<?= $offer->getId() ?>"
         data-pagetype="catalogDetail"
         data-urlDelivery="/ajax/catalog/product-info/product/deliverySet/"
         itemprop="itemListElement" itemscope itemtype="http://schema.org/Product">
        <div class="b-container">
            <?php
            ob_start();
            $APPLICATION->IncludeComponent(
                'fourpaws:breadcrumbs',
                '',
                [
                    'IBLOCK_ELEMENT' => $product,
                    'SHOW_CURRENT' => true,
                ],
                null,
                ['HIDE_ICONS' => 'Y']
            );
            $breadcrumbs = ob_get_clean();
            echo $landingService->replaceLinksToLanding($breadcrumbs, $request);
            ?>
            <div class="b-product-card__top">
                <div class="b-product-card__title-product">
                    <?php $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_DETAIL_TITLE_VIEW); ?>
                    <div class="b-common-item b-common-item--card">
                        <div class="b-common-item__rank b-common-item__rank--card">
                            <?php $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_RATING_STARS_VIEW); ?>
                            <div class="b-common-item__rank-wrapper">
                                <?= MarkHelper::getDetailTopMarks($offer) ?>
                                <?php if ($offer->isShare()) {
                                    /** @var IblockElement $share */
                                    foreach ($offer->getShare() as $share) { ?>
                                        <span class="b-common-item__rank-text b-common-item__rank-text--red"><?= $share->getName() ?></span>
                                    <?php }
                                } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-product-card__product">
                    <div class="b-product-card__permutation-weight js-weight-tablet"></div>
                    <?php $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_DETAIL_SLIDER_VIEW); ?>

                    <div class="b-product-card__info-product js-weight-here">
                        <?php
                        $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_DETAIL_OFFERS_VIEW);
                        $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_DETAIL_CURRENT_OFFER_INFO);
                        ?>
                    </div>
                </div>
                <?php $hasSetFirst = $APPLICATION->IncludeComponent(
                    'fourpaws:catalog.groupset',
                    '',
                    [
                        'OFFER' => $offer,
                    ],
                    null,
                    ['HIDE_ICONS' => 'Y']
                );
                if (!$hasSetFirst) {
                    $APPLICATION->IncludeComponent(
                        'fourpaws:catalog.product.bundle',
                        '',
                        [
                            'OFFER' => $offer,
                        ],
                        null,
                        ['HIDE_ICONS' => 'Y']
                    );
                } ?>
            </div>

            <? if ($rootCategory && $rootCategory->isShowDelText()){ ?>
                <div class="b-information-message b-information-message--green b-information-message--product-detail"><?=Category::DEL_TEXT?></div>
            <? } ?>

            <?
            $APPLICATION->IncludeComponent(
                'articul:catalog.element.detail.kit',
                '.default',
                [
                    'CODE' => $productDetailRequest->getProductSlug(),
                    'OFFER_ID' => $offerId
                ],
                false,
                ['HIDE_ICONS' => 'Y']
            );
            ?>
            <div class="b-product-card__tab">
                <div class="b-tab">
                    <div class="b-tab-title">
                        <ul class="b-tab-title__list">
                            <?php
                            $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_DETAIL_DESCRIPTION_TAB_HEADER);

                            if ($product->getComposition()->getText() || $product->getLayoutComposition()->getText()) { ?>
                                <li class="b-tab-title__item js-tab-item">
                                    <a class="b-tab-title__link js-tab-link"
                                       href="javascript:void(0);" title="Состав"
                                       data-tab="composition"><h2
                                                class="b-tab-title__text">Состав</h2></a>
                                </li>
                            <?php }

                            if ($product->getNormsOfUse()->getText() || $product->getLayoutRecommendations()->getText()) { ?>
                                <li class="b-tab-title__item js-tab-item">
                                    <a class="b-tab-title__link js-tab-link"
                                       href="javascript:void(0);" title="Рекомендации по питанию"
                                       data-tab="recommendations"><h2
                                                class="b-tab-title__text">Рекомендации по питанию</h2></a>
                                </li>
                            <?php }

                            $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_RATING_TAB_HEADER_VIEW);
                            $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_DETAIL_DELIVERY_PAYMENT_TAB_HEADER);

                            /** наличие меняется аяксом */ ?>
                            <li class="b-tab-title__item js-tab-item shops-tab disable">
                                <a class="b-tab-title__link js-tab-link"
                                   href="javascript:void(0);" title="Наличие в магазинах"
                                   data-tab="availability">
                                    <h2 class="b-tab-title__text">Наличие в магазинах
                                        <span class="b-tab-title__number">(0)</span>
                                    </h2>
                                </a>
                            </li>
                            <?php if ($offer->isShare()) { ?>
                                <li class="b-tab-title__item js-tab-item">
                                    <a class="b-tab-title__link js-tab-link"
                                       href="javascript:void(0);" title="Акция"
                                       data-tab="shares">
                                        <h2 class="b-tab-title__text">Акция</h2>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                    <div class="b-tab-content">
                        <?php
                        $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_DETAIL_DESCRIPTION_TAB);

                        if ($product->getComposition()->getText() || $product->getLayoutComposition()->getText()) { ?>
                            <div class="b-tab-content__container js-tab-content" data-tab-content="composition">
                                <div class="b-description-tab b-description-tab--full">
                                    <div class="b-description-tab__column b-description-tab__column--full">
                                        <div class="rc-product-detail">
                                            <? if ($product->getLayoutComposition()->getText() != '' && $product->getLayoutComposition()->getText() != null) { ?>
                                                <?= $product->getLayoutComposition()->getText() ?>
                                            <? } else { ?>
                                                <p><?= $product->getComposition()->getText() ?></p>
                                            <? } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php }

                        if ($product->getNormsOfUse()->getText() || $product->getLayoutRecommendations()->getText()) { ?>
                            <div class="b-tab-content__container js-tab-content b-tab-content__container_recommendations" data-tab-content="recommendations">
                                <div class="b-description-tab b-description-tab--full">
                                    <div class="rc-product-detail">
                                        <div class="b-description-tab__column b-description-tab__column--full">
                                            <? if ($product->getLayoutRecommendations()->getText() != '' && $product->getLayoutRecommendations()->getText() != null) { ?>
                                                <?= $product->getLayoutRecommendations()->getText() ?>
                                            <? } else { ?>
                                                <p><?= $product->getNormsOfUse()->getText() ?></p>
                                            <? } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php }

                        /** @noinspection PhpUnhandledExceptionInspection */
                        $APPLICATION->IncludeComponent(
                            'fourpaws:comments',
                            'catalog',
                            [
                                'HL_ID' => HighloadHelper::getIdByName('Comments'),
                                'OBJECT_ID' => $product->getId(),
                                'SORT_DESC' => 'Y',
                                'ITEMS_COUNT' => 5,
                                'ACTIVE_DATE_FORMAT' => 'd j Y',
                                'TYPE' => 'catalog',
                            ],
                            false,
                            ['HIDE_ICONS' => 'Y']
                        ); ?>
                        <?php $APPLICATION->IncludeComponent(
                            'fourpaws:city.delivery.info',
                            'catalog.detail.tab',
                            [
                                'DELIVERY_CODES' => [DeliveryService::INNER_DELIVERY_CODE],
                                'CACHE_TIME' => 3600
                            ],
                            false,
                            ['HIDE_ICONS' => 'Y']
                        ); ?>
                        <?php $APPLICATION->IncludeComponent(
                            'fourpaws:catalog.shop.available',
                            'catalog.detail.tab',
                            [
                                'PRODUCT' => $product,
                                'OFFER' => $offer,
                            ],
                            false,
                            ['HIDE_ICONS' => 'Y']
                        ); ?>
                        <?php if ($offer->isShare()) { ?>
                            <div class="b-tab-content__container js-tab-content" data-tab-content="shares">
                                <?php /** @var IblockElement $share */
                                foreach ($offer->getShare() as $share) {
                                    ?>
                                    <div class="b-title b-title--advice b-title--stock">Акция</div>
                                    <div class="b-stock">
                                        <div class="b-characteristics-tab b-characteristics-tab--stock">
                                            <ul class="b-characteristics-tab__list">
                                                <li class="b-characteristics-tab__item b-characteristics-tab__item--stock">
                                                    <div class="b-characteristics-tab__characteristics-text b-characteristics-tab__characteristics-text--stock">
                                                        <span>Название</span>
                                                        <div class="b-characteristics-tab__dots"></div>
                                                    </div>
                                                    <div class="b-characteristics-tab__characteristics-value b-characteristics-tab__characteristics-value--stock">
                                                        <a href="<?= $share->getDetailPageUrl() ?>" title="<?= $share->getName() ?>"><?= $share->getName() ?></a>
                                                    </div>
                                                </li>
                                                <li class="b-characteristics-tab__item b-characteristics-tab__item--stock">
                                                    <div class="b-characteristics-tab__characteristics-text b-characteristics-tab__characteristics-text--stock">
                                                        <span>Срок проведения</span>
                                                        <div class="b-characteristics-tab__dots"></div>
                                                    </div>
                                                    <div class="b-characteristics-tab__characteristics-value b-characteristics-tab__characteristics-value--stock">
                                                        <?php
                                                        $activeFrom = $share->getDateActiveFrom();
                                                        $activeTo = $share->getDateActiveTo();

                                                        if ($activeFrom && $activeTo) {
                                                            ?>
                                                            <?= DateHelper::replaceRuMonth($activeFrom->format('d #n# Y'),
                                                                DateHelper::GENITIVE) ?>
                                                            —
                                                            <?= DateHelper::replaceRuMonth($activeTo->format('d #n# Y'),
                                                                DateHelper::GENITIVE) ?>
                                                            <?php
                                                        } elseif ($activeFrom) {
                                                            ?>
                                                            С <?= DateHelper::replaceRuMonth($activeFrom->format('d #n# Y'),
                                                                DateHelper::GENITIVE) ?>
                                                            <?php
                                                        } elseif ($activeTo) {
                                                            ?>
                                                            По <?= DateHelper::replaceRuMonth($activeTo->format('d #n# Y'),
                                                                DateHelper::GENITIVE) ?>
                                                            <?php
                                                        } ?>
                                                    </div>
                                                </li>
                                                <?php if (!empty($share->getPreviewText()->getText())) { ?>
                                                    <li class="b-characteristics-tab__item b-characteristics-tab__item--stock">
                                                        <div class="b-characteristics-tab__characteristics-text b-characteristics-tab__characteristics-text--stock">
                                                            <span>Описание</span>
                                                            <div class="b-characteristics-tab__dots"></div>
                                                        </div>
                                                        <div class="b-characteristics-tab__characteristics-value b-characteristics-tab__characteristics-value--stock">
                                                            <?= $share->getPreviewText()->getText() ?>
                                                        </div>
                                                    </li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                        <?php
                                        /**
                                         * @todo подарок по акции
                                         * <div class="b-stock__gift">
                                         * <div class="b-advice b-advice--stock">
                                         * <a class="b-advice__item b-advice__item--stock"
                                         * href="javascript:void(0)" title="">
                                         * <span class="b-advice__image-wrapper b-advice__image-wrapper--stock"><img
                                         * class="b-advice__image"
                                         * src="/static/build/images/content/fresh-step.png"
                                         * alt="" title="" role="presentation" /></span>
                                         * <span class="b-advice__block b-advice__block--stock">
                                         * <span class="b-advice__text b-advice__text--red">Подарок по акции</span>
                                         * <span class="b-clipped-text b-clipped-text--advice">
                                         * <span><strong>Китекат</strong> корм для кошек рыба в соусе</span>
                                         * </span>
                                         * <span class="b-advice__info b-advice__info--stock">
                                         * <span class="b-advice__weight">85 г</span>
                                         * <span class="b-advice__cost">
                                         * 13,40 <span class="b-ruble b-ruble--advice">₽</span>
                                         * </span>
                                         * </span>
                                         * </span>
                                         * </a>
                                         * </div>
                                         * <a class="b-button b-button--bordered-grey" href="javascript:void(0)" title="">
                                         * Выбрать подарок
                                         * </a>
                                         * </div>
                                         **/
                                        ?>
                                    </div>
                                    <?php $APPLICATION->IncludeComponent(
                                        'fourpaws:products.by.prop',
                                        'product.detail.stocks',
                                        [
                                            'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION,
                                                IblockCode::SHARES),
                                            'ITEM_ID' => $share->getId(),
                                            'TITLE' => 'Товары по акции',
                                            'COUNT_ON_PAGE' => 20,
                                            'PROPERTY_CODE' => 'PRODUCTS',
                                            'FILTER_FIELD' => 'XML_ID',
                                            'SHOW_PAGE_NAVIGATION' => false,
                                        ],
                                        null,
                                        [
                                            'HIDE_ICONS' => 'Y',
                                        ]
                                    ); ?>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php

/**
 * Преимущества
 */
$APPLICATION->IncludeComponent(
    'bitrix:main.include',
    '',
    [
        'AREA_FILE_SHOW' => 'file',
        'PATH' => '/local/include/blocks/advantages.php',
        'EDIT_TEMPLATE' => '',
    ],
    null,
    [
        'HIDE_ICONS' => 'N',
    ]
);

/**
 * Похожие товары
 */
$APPLICATION->IncludeFile(
    'blocks/components/similar_products.php',
    [
        'PRODUCT_ID' => $product->getId(),
    ],
    [
        'SHOW_BORDER' => false,
        'NAME' => 'Блок похожих товаров',
        'MODE' => 'php',
    ]
);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
die();
