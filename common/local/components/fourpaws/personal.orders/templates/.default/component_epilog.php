<?php

use Bitrix\Sale\BasketItem;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Helpers\WordHelper;
use FourPaws\PersonalBundle\Entity\Order;
use FourPaws\SaleBundle\Service\BasketService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** установка бонусов за товар */
/** @var ArrayCollection $activeOrders
 * @var ArrayCollection $closedOrders
 * @var FourPawsPersonalCabinetOrdersComponent $component
 * @var Order $order
 */
$activeOrders = $this->arResult['ACTIVE_ORDERS'];
$closedOrders = $this->arResult['CLOSED_ORDERS'];

$discount = $component->getCurrentUserService()->getDiscount();
if ($discount > 0) {
    foreach ($activeOrders as $order) {
        foreach ($order->getItems() as $item) {
            $bonus = $bonus = $component->getItemBonus($item, $discount);
            if (!empty($bonus)) { ?>
                <script type="text/javascript">
                    $(function () {
                        var $jsBonus = $('.js-order-item-bonus-<?=$order->isManzana() ? 'manzana-' : ''?><?=$item->getId()?>');
                        if ($jsBonus.length > 0) {
                            $jsBonus.html('<?=$bonus?>');
                        }
                    });
                </script>
            <?php }
        }
    }
    foreach ($closedOrders as $order) {
        foreach ($order->getItems() as $item) {
            $bonus = $bonus = $component->getItemBonus($item, $discount);
            if (!empty($bonus)) { ?>
                <script type="text/javascript">
                    $(function () {
                        var $jsBonus = $('.js-order-item-bonus-<?=$order->isManzana() ? 'manzana-' : ''?><?=$item->getId()?>');
                        if ($jsBonus.length > 0) {
                            $jsBonus.html('<?=$bonus?>');
                        }
                    });
                </script>
            <?php }
        }
    }
}