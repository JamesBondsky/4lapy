<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @global BasketComponent $component */

use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use FourPaws\Components\BasketComponent;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\WordHelper;
use FourPaws\SaleBundle\Entity\UserAccount;
use FourPaws\UserBundle\Entity\User;

/** @var User $user */
$user = $arResult['USER'];
/** @var UserAccount $userAccount */
$userAccount = $arResult['USER_ACCOUNT'];

/** @var Basket $basket */
$basket = $arResult['BASKET'];
$orderableItems = $basket->getOrderableItems();


if (!isset($arParams['IS_AJAX']) || $arParams['IS_AJAX'] !== true) {
    echo '<div class="b-header-info__item b-header-info__item--cart">';
}
?>
    <a class="b-header-info__link" href="<?= $arParams['PATH_TO_BASKET'] ?>" title="Корзина">
        <span class="b-icon">
            <?= new SvgDecorator('icon-cart', 16, 16) ?>
        </span>
        <span class="b-header-info__inner">Корзина</span>
        <span class="b-header-info__number"><?= $orderableItems->count() ?></span>
    </a>
    <div class="b-popover b-popover--cart">
        <div class="b-cart">
            <span class="b-cart__amount"><?= $orderableItems->count() ?> <?= WordHelper::declension($orderableItems->count(),
                    ['Товар', 'Товара', 'Товаров']) ?></span>
            <a class="b-link" href="<?= $arParams['PATH_TO_BASKET'] ?>" title="Редактировать">Редактировать</a>
            <a class="b-button" href="<?= $arParams['PATH_TO_ORDER'] ?>" title="Оформить заказ">Оформить заказ</a>
            <?php
            if (!$orderableItems->isEmpty()) {
                /** @var BasketItem $basketItem */
                foreach ($orderableItems as $basketItem) {
                    /** @todo пропускаем подарки? */
                    if (isset($basketItem->getPropertyCollection()->getPropertyValues()['IS_GIFT'])) {
                        continue;
                    } ?>
                    <div class="b-cart-item">
                        <a class="b-cart-item__name"
                           href="<?= $basketItem->getField('DETAIL_PAGE_URL') ?>"
                           title="Роял Канин корм для собак крупных пород ма…"><?= $basketItem->getField('NAME') ?></a>
                        <span class="b-cart-item__weight"><?= WordHelper::showWeight($basketItem->getWeight() * $basketItem->getQuantity(),
                                true) ?></span>
                        <span class="b-cart-item__amount">(<?= $basketItem->getQuantity() ?> шт.)</span>
                    </div>
                <?php }
            } ?>
        </div>
    </div>
<?php
if (!isset($arParams['IS_AJAX']) || $arParams !== true) {
    echo '</div>';
}
