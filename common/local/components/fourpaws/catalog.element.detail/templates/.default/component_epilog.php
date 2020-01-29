<?php

use Bitrix\Sale\BasketItem;
use FourPaws\BitrixOrm\Model\HlbReferenceItem;
use FourPaws\BitrixOrm\Model\IblockElement;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Components\CatalogElementDetailComponent;
use FourPaws\Decorators\FullHrefDecorator;
use FourPaws\Helpers\DateHelper;
use FourPaws\Helpers\WordHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array $arResult
 * @var Offer $currentOffer
 * @var Product $product
 * @var \FourPaws\Catalog\Model\Brand $brand
 * @var CatalogElementDetailComponent $this
 */
$currentOffer = $arResult['CURRENT_OFFER'];
$product = $arResult['PRODUCT'];
$brand = $arResult['BRAND'];

$userService = $this->getCurrentUserService();
$basketService = $this->getBasketService();

/**
 * TODO 1 запрос к user_table. Нужно бы убрать.
 */
$bonus = $currentOffer->getBonusFormattedText($userService->getDiscount());
$bonusSubscribe = $currentOffer->getBonusFormattedText($userService->getDiscount(), 1, true);

$shareContent = null;
if ($currentOffer->isShare()) {
    /** @var IblockElement $share */
    foreach ($currentOffer->getShare() as $share) {
        $activeFrom = $share->getDateActiveFrom();
        $activeTo = $share->getDateActiveTo();
        ob_start()?>
        <a href="<?= $share->getDetailPageUrl() ?>" title="<?= $share->getName() ?>" <?= $arParams['IS_POPUP'] ? 'target="_blank"' : ''?>>
            <p class="b-counter-basket__text b-counter-basket__text--red">
                <?= $share->getName() ?>
            </p>
        </a>
        <?php if (!empty($share->getPreviewText()->getText())) { ?>
            <p class="b-counter-basket__text"><?= $share->getPreviewText()->getText() ?></p>
        <?php } ?>
        <p class="b-counter-basket__text">
            <?php if ($activeFrom && $activeTo) { ?>
                <?= DateHelper::replaceRuMonth($activeFrom->format('d #n#')) ?>
                —
                <?= DateHelper::replaceRuMonth($activeTo->format('d #n# Y')) ?>
            <?php } elseif ($activeFrom) { ?>
                С <?= DateHelper::replaceRuMonth($activeFrom->format('d #n#')) ?>
            <?php } elseif ($activeTo) { ?>
                По <?= DateHelper::replaceRuMonth($activeTo->format('d #n# Y')) ?>
            <?php } ?>
        </p>
        <?php
        $shareContent = ob_get_contents();
        ob_end_clean();
    }
}

?>
    <script<?= ($arParams['IS_POPUP']) ? ' data-epilog-handlers="true"' : '' ?>>

        if(epilogHandlers === undefined){
            // класс для комплексного выполнения всех обработчиков
            var epilogHandlers = {
                handlers: [],
                add: function (handler) {
                    this.getInstance().handlers[this.handlers.length] = handler;
                },
                execute: function () {
                    this.getInstance().handlers.forEach(function (handler) {
                        if (typeof handler === 'function') {
                            handler();
                        }
                    });
                    this.getInstance().handlers = [];
                },
                getInstance: function(){ return this }
            };
        }

        epilogHandlers.add(function () {
            var $jsBonus = $('.js-bonus-<?=$currentOffer->getId()?>');
            var $jsBonusSubscribe = $('.js-bonus-subscribe-<?=$currentOffer->getId()?>');

            if ($jsBonus.length > 0) {
                <? if (!empty($bonus)) { ?>
                    $jsBonus.html('<?=$bonus?>');
                <?php }else{ ?>
                    $jsBonus.hide();
                <? } ?>
            }

            <? if (!empty($bonus)) { ?>
                if ($jsBonusSubscribe.length > 0) {
                    $jsBonusSubscribe.html('<?=$bonusSubscribe?>');
                }
            <? } ?>
        });

        epilogHandlers.add(function () {
            $('.js-current-offer-price-old').html('<?= $currentOffer->getCatalogOldPrice() ?>');
            $('.js-current-offer-price').html('<?= $currentOffer->getCatalogPrice() ?>');
            $('.js-plus-minus-count')
                .data('cont-max', '<?=$currentOffer->getQuantity()?>')
                .data('one-price', '<?=$currentOffer->getPrice()?>');
            <? if($currentOffer->getSubscribePrice() < $currentOffer->getPrice()){ ?>
                $('.js-subscribe-price').html('<?= $currentOffer->getSubscribePrice() ?>');
                $('.js-subscribe-price-block').show();
            <? } ?>
        });

        <?php
        /** установка количества товаров в корзине для офферов */
        $basket = $basketService->getBasket();

        /** @var BasketItem $basketItem */
        foreach ($basket->getBasketItems() as $basketItem) { ?>
            epilogHandlers.add(function () {
                var $offerInCart = $('.js-offer-in-cart-<?=$basketItem->getProductId()?>');

                if ($offerInCart.length > 0) {
                    $offerInCart.find('.b-weight-container__number').html('<?=$basketItem->getQuantity()?>');
                    $offerInCart.css('display', 'inline-block');
                }
            });
        <?php }

        foreach ($product->getOffers() as $offer) {
        /** установка цен, скидочных цен, акции, нет в наличии */ ?>
            epilogHandlers.add(function () {
                var $offerLink = $('.js-offer-link-<?=$offer->getId()?>');
                if ($offerLink.length > 0) {
                    $offerLink.find('.b-weight-container__price').html('<?= WordHelper::numberFormat($offer->getCatalogPrice(),
                        0) ?> <span class="b-ruble b-ruble--weight">₽</span>');
                    $offerLink.data('price', '<?= WordHelper::numberFormat($offer->getCatalogPrice(), 0) ?>');
                    <?php if(!$offer->isAvailable()) { ?>
                    $offerLink.addClass('unavailable-link');
                    $offerLink.find('.b-weight-container__not').html('Нет в наличии').css('display', 'inline-block');
                    <?php } elseif($offer->isShare()) { ?>
                    $offerLink.find('.js-offer-action').html('Акция').css('display', 'inline-block');
                    <?php }?>
                }
            });
        <?php }

        if ($currentOffer->isAvailable()) { ?>
            epilogHandlers.add(function () {
                $('.js-product-controls').addClass('active')
            });
        <?php } ?>

        <? if ($shareContent) { ?>
            epilogHandlers.add(function () {
                $('.js-dynaminc-content[data-id="shares"]').html(`<?=$shareContent?>`);
            });
        <?php } ?>

    </script>

<?php
/**
 * Offer microdata
 *
 * (распологается здесь, т.к. карточка кешируется,
 * поисковик не выполняет JavaScript,
 * а в карточке значения заполняются через JS,
 * а также для каждого региона возможно своё значение)
 */
foreach ($product->getOffers() as $offer) {

    $availabilityValue = 'OutOfStock';
    /** @noinspection PhpUnhandledExceptionInspection */
    if ($offer->isAvailable()) {
        $availabilityValue = 'InStock';
    }
    /** @noinspection PhpUnhandledExceptionInspection */
    $packageLabel = $offer->getPackageLabel(false, 0);
    ?>
    <span itemscope itemtype="http://schema.org/Offer" style="display: none;">
        <meta itemprop="itemOffered" content="<?= $packageLabel ?>">
        <meta itemprop="price" content="<?= $offer->getCatalogPrice() ?>">
        <meta itemprop="priceCurrency" content="<?= $offer->getCurrency() ?>">
        <meta itemprop="availability" content="http://schema.org/<?= $availabilityValue ?>">
    </span>
    <?php
}

try {
    $category3 = $product->getSection();
    $category2 = $category3->getParent();
    $category1 = $category2->getParent();

    $forWhoString = array_reduce($product->getForWho()->toArray(), static function($carry, HlbReferenceItem $item) {
    	return $carry ? implode(', ', [$carry, $item->getName()]) : $item->getName();
    });
    $petAgeString = array_reduce($product->getPetAge()->toArray(), static function($carry, HlbReferenceItem $item) {
        return $carry ? implode(', ', [$carry, $item->getName()]) : $item->getName();
    });
    $petSizeString = array_reduce($product->getPetSize()->toArray(), static function($carry, HlbReferenceItem $item) {
        return $carry ? implode(', ', [$carry, $item->getName()]) : $item->getName();
    });
    $productFormsString = array_reduce($product->getProductForms()->toArray(), static function($carry, HlbReferenceItem $item) {
        return $carry ? implode(', ', [$carry, $item->getName()]) : $item->getName();
    });
    $feedSpecification = $product->getFeedSpecification();
    $feedSpecificationString = '';
    if ($feedSpecification) {
        $feedSpecificationString = $feedSpecification->getName();
    }

	//TODO exponea dto?
    $exponeaData = [
        'product_id'              => $currentOffer->getXmlId() ?: '', // type: string
        'title'                   => $currentOffer->getName(), // type: string, format: trimmed
        'brand'                   => $brand->getName(), // type: string, format: trimmed
//        'category_sap_1'          => '', // type: string //FIXME exponea оставляем на конец работ, надо импортировать привязку товаров к разделам sap и информацию по ним в БД
//        'category_sap_1_id'       => '', // type: string
//        'category_sap_2'          => '', // type: string
//        'category_sap_2_id'       => '', // type: string
//        'category_sap_3'          => '', // type: string
//        'category_sap_3_id'       => '', // type: string
        'category_1'              => $category1->getName(), // type: string
        'category_1_url'          => new FullHrefDecorator($category1->getSectionPageUrl()), // type: string, format: URL
        'category_1_id'           => $category1->getCode(), // type: string
        'category_2'              => $category2->getName(), // type: string
        'category_2_url'          => new FullHrefDecorator($category2->getSectionPageUrl()), // type: string, format: URL
        'category_2_id'           => $category2->getCode(), // type: string
        'category_3'              => $category3->getName(), // type: string
        'category_3_url'          => new FullHrefDecorator($category3->getSectionPageUrl()), // type: string, format: URL
        'category_3_id'           => $category3->getCode(), // type: string
        'categories_path'         => implode(' > ', [$category1->getName(), $category2->getName(), $category3->getName()]), // type: string, format: list of categories concatenated with '>' (path)
        'category_id'             => $category1->getCode(), // type: string
        'categories_ids'          => [$category1->getCode(), $category2->getCode(), $category3->getCode()], // type: list, format: JSON (Array of Strings)
        'price'                   => $currentOffer->getPrice(), // type: number(float?)
        'stock_level'             => $currentOffer->getQuantity(), // type: number, format: integer
        //'location'                => new FullHrefDecorator('/'), // type: string, format: URL. будет тречиться автоматически, не обязательно вставлять в код
        'domain'                  => SITE_SERVER_NAME, // type: string, format: domain
        'pet_type'                => $forWhoString, // type: string
        'pet_age'                 => $petAgeString, // type: string
        'pet_size'                => $petSizeString, // type: string
        'product_spec'            => $feedSpecificationString, // type: string
        'product_farma_type'      => $productFormsString, // type: boolean, format: True/False //TODO exponea По каким правилам мапить значения справочника в булевые значения?
        'product_farma'           => $product->isLicenseRequired(), // type: boolean, format: True/False
        'product_stm'             => $product->getCtm(), // type: boolean, format: True/False //TODO exponea посмотреть, прилетят ли 25-го числа значения true/false (всего до исправления было 46 true)
        'product_food'            => $product->isFood(), // type: boolean, format: True/False
        'product_weight'          => $currentOffer->getCatalogProduct()->getWeight(), // type: number(float?)
        //'product_wear_size'       => '', // type: string //TODO exponea change
        //'rating'                  => 0, // type: number //TODO exponea change
        //'ratings_count'           => 0, // type: number //TODO exponea change
        'product_subscribe_price' => $currentOffer->getSubscribePrice(), // type: string(float)
        'product_available'       => $currentOffer->isAvailable(), // type: bool
        //'delivery_date'           => 1234567890 // type: number //TODO exponea отправлять всё событие батчами через API: https://docs.exponea.com/reference#batch-commands ?
    ];
    if ($discount = $currentOffer->getDiscount()) {
        $exponeaData['discount_percentage'] = $discount; // type: number(float?)
        $exponeaData['discount_value'] = $currentOffer->getDiscountPrice(); // type: number(float?)
        $exponeaData['original_price'] = $currentOffer->getOldPrice() ?: $currentOffer->getBasePrice(); // type: number(float?)
    }

    $exponeaDataEncoded = CUtil::PhpToJSObject($exponeaData);
    ?>
	<script>
        //console.log('📊exponea(view_item)', <?//= $exponeaDataEncoded ?>);
        exponea.track('view_item', <?= $exponeaDataEncoded ?>);
	</script>
    <?php
} catch (Throwable $e) {
	dump($e); //TODO exponea del
	//TODO exponea log critical
}
