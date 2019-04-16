<?php

use Bitrix\Sale\BasketItem;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Components\CatalogElementDetailComponent;
use FourPaws\Helpers\WordHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array $arResult
 * @var Offer $currentOffer
 * @var Product $product
 * @var CatalogElementDetailComponent $this
 */
$currentOffer = $arResult['CURRENT_OFFER'];
$product = $arResult['PRODUCT'];

$userService = $this->getCurrentUserService();
$basketService = $this->getBasketService();

/**
 * TODO 1 запрос к user_table. Нужно бы убрать.
 */
$bonus = $currentOffer->getBonusFormattedText($userService->getDiscount());

?>
    <script<?= ($arParams['IS_POPUP']) ? ' data-epilog-handlers="true"' : '' ?>>

        if(epilogHandlers === undefined){
            // класс для комплексного выполнения всех обработчиков
            var epilogHandlers = {
                handlers: [],
                add: function (handler) {
                    this.handlers[this.handlers.length] = handler;
                },
                execute: function () {
                    this.handlers.forEach(function (handler) {
                        if (typeof handler === 'function') {
                            handler();
                        }
                    });
                    this.handlers = [];
                },
            };
        }


        <? if (!empty($bonus)) { ?>
        epilogHandlers.add(function () {
            var $jsBonus = $('.js-bonus-<?=$currentOffer->getId()?>');
            if ($jsBonus.length > 0) {
                $jsBonus.html('<?=$bonus?>');
            }
        });
        <?php } ?>

        epilogHandlers.add(function () {
            $('.js-current-offer-price-old').html('<?= $currentOffer->getCatalogOldPrice() ?>');
            $('.js-current-offer-price').html('<?= $currentOffer->getCatalogPrice() ?>');
            $('.js-plus-minus-count')
                .data('cont-max', '<?=$currentOffer->getQuantity()?>')
                .data('one-price', '<?=$currentOffer->getPrice()?>');
            <? if($currentOffer->getPrice() != $currentOffer->getSubscribePrice()){ ?>
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

        <? if(!$arParams['IS_POPUP']) { ?>
        $(function(){
            //epilogHandlers.execute();
        });
        <? } ?>
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
