<?php

use Bitrix\Sale\BasketItem;
use FourPaws\App\Application;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Helpers\WordHelper;
use FourPaws\SaleBundle\Service\BasketService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** установка бонусов за товар */
/** @var Offer $currentOffer */
$currentOffer = $arResult['CURRENT_OFFER'];
$bonus = $currentOffer->getBonusFormattedText((int)$component->getCurrentUserService()->getDiscount());
if (!empty($bonus)) { ?>
    <script type="text/javascript">
        $(function () {
            var $jsBonus = $('.js-bonus-<?=$currentOffer->getId()?>');
            if($jsBonus.length > 0) {
                $jsBonus.html('<?=$bonus?>');
            }
        });
    </script>
<?php } ?>
    <script type="text/javascript">
        $(function () {
            $('.js-plus-minus-count')
                .data('cont-max', '<?=$currentOffer->getQuantity()?>')
                .data('one-price', '<?=$currentOffer->getPrice()?>');
        });
    </script>
<?php
/** установка количество товаров в корзине для офферов */
$container = Application::getInstance()->getContainer();
$basketService = $container->get(BasketService::class);
$basket = $basketService->getBasket();

/** @var BasketItem $basketItem */
foreach ($basket->getBasketItems() as $basketItem) { ?>
    <script type="text/javascript">
        $(function () {
            var $offerInCart = $('.js-offer-in-cart-<?=$basketItem->getProductId()?>');
            if($offerInCart.length > 0) {
                $offerInCart.find('.b-weight-container__number').html('<?=$basketItem->getQuantity()?>');
                $offerInCart.css('display', 'inline-block');
            }
        });
    </script>
<?php }
/** @var Product $product */
$product = $arResult['PRODUCT'];
/** перегружаем для актуальности - иначе будут данные из кеша - они могут быть устаревшие */
$offers = $product->getOffers(true,true);
/** @var Offer $offer */
foreach ($offers as $offer) {
    /** установка цен, скидочных цен, акции, нет в наличии */ ?>
    <script type="text/javascript">
        $(function () {
            var $offerLink = $('.js-offer-link-<?=$offer->getId()?>');
            if($offerLink.length > 0) {
                $offerLink.find('.b-weight-container__price').html('<?= WordHelper::numberFormat($offer->getPriceCeil(),
                    0) ?> <span class="b-ruble b-ruble--weight">₽</span>');
                $offerLink.data('price', '<?=WordHelper::numberFormat($offer->getPriceCeil(), 0)?>');
                <?php if(!$offer->isAvailable()) { ?>
                    $offerLink.addClass('unavailable-link');
                    $offerLink.find('.b-weight-container__not').html('Нет в наличии').css('display', 'inline-block');
                <?php } else { ?>
                    <?php if($offer->isShare()) { ?>
                        $offerLink.find('.js-offer-action').html('Акция').css('display', 'inline-block');
                    <?php }

                    /* if($offer->getOldPrice() > $offer->getPrice()) { ?>
                        if ($offerLink.find('.b-weight-container__old-price--big').length > 0) {
                            $offerLink.find('.b-weight-container__old-price--big').html('<?=WordHelper::numberFormat($offer->getOldPriceCeil(),
                                0)?> <span class="b-ruble b-ruble--old-weight-price">₽</span>').css('display', 'inline-block');
                        }
                    <?php } */
                }?>
            }
        });
    </script>
<?php }
