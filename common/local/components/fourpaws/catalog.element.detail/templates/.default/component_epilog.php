<?php

use Bitrix\Sale\BasketItem;
use FourPaws\App\Application;
use FourPaws\Catalog\Model\Offer;
use FourPaws\SaleBundle\Service\BasketService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** установка бонусов за товар */
/** @var Offer $currentOffer */
$currentOffer = $templateData['currentOffer'];
$bonus = $currentOffer->getBonusFormattedText((int)$component->getCurrentUserService()->getDiscount());
if (!empty($bonus)) { ?>
    <script type="text/javascript">
        $(function() {
            $('.js-bonus-<?=$currentOffer->getId()?>').html('<?=$bonus?>');
        });
    </script>
<?php }

/** установка количество товаров в корзине для офферов */
$container = Application::getInstance()->getContainer();
$basketService = $container->get(BasketService::class);
$basket = $basketService->getBasket();

/** @var BasketItem $basketItem */
foreach ($basket->getBasketItems() as $basketItem) {?>
    <script type="text/javascript">
        $(function() {
            var $offerInCart = $('.js-offer-in-cart-<?=$basketItem->getProductId()?>');
            $offerInCart.find('.b-weight-container__number').html('<?=$basketItem->getQuantity()?>');
            $offerInCart.css('display', 'inline-block');
        });
    </script>
<?php }


/** @todo сделать установку нет в наличии у офферов тут */

/** @todo сделать установку акций для офферов тут */
