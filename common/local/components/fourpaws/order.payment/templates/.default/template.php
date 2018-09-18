<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array $arResult
 */

use Adv\Bitrixtools\Tools\BitrixUtils;
use Bitrix\Main\Grid\Declension;
use Bitrix\Sale\Order;
use FourPaws\Helpers\CurrencyHelper;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\Helpers\WordHelper;

$declension = new Declension('товар', 'товара', 'товаров');

?>
<?php if (!empty($arResult['ERRORS'])) { ?>
    <div class="b-container">
        <?php if ($arResult['IS_SUCCESS'] === 'Y') { ?>
            <h1 class="b-title b-title--h1 b-title--order">Переход на страницу оплаты заказа
            </h1>
            <div class="b-order b-order--top-line">
                <div class="b-order__text-block">Сейчас вы будете перенаправлены на страницу оплаты заказа...
                </div>
            </div>
        <?php } else { ?>
            <h1 class="b-title b-title--h1 b-title--order">Ошибка при оплате заказа
            </h1>
            <div class="b-order b-order--top-line">
                <div class="b-order__text-block">
                    Попробуйте оплатить заказ еще раз сейчас или в личном кабинете. Позвоните на горячую линию по номеру
                    <?= PhoneHelper::getCityPhone() ?>, сообщите о проблеме, и наши операторы помогут вам
                </div>
            </div>
        <?php } ?>
    </div>
<?php } elseif ($arParams['PAY'] !== BitrixUtils::BX_BOOL_TRUE) {
    /** @var Order $order */
    $order = $arResult['ORDER'];
    $orderWeight = $order->getBasket()->getWeight();
    $orderQuantity = array_sum($order->getBasket()->getQuantityList());
    $orderPrice = $order->getPrice();
    if ($order->getPaymentCollection()->getInnerPayment()) {
        $orderPrice -= $order->getPaymentCollection()->getInnerPayment()->getSum();
    }

    /** @var Order $relatedOrder */
    $relatedOrder = $arResult['RELATED_ORDER'];
    if ($relatedOrder) {
        $relatedOrderWeight = $relatedOrder->getBasket()->getWeight();
        $relatedOrderQuantity = array_sum($relatedOrder->getBasket()->getQuantityList());
        $relatedOrderPrice = $relatedOrder->getPrice();
        if ($relatedOrder->getPaymentCollection()->getInnerPayment()) {
            $relatedOrderPrice -= $relatedOrder->getPaymentCollection()->getInnerPayment()->getSum();
        }
    }
    ?>
    <div class="b-container">
        <h1 class="b-title b-title--h1 b-title--order">Оплата заказа
        </h1>
        <div class="b-order">
            <form action="<?= $arResult['ORDER_PAY_URL'] ?>" method="get">
                <input type="hidden" name="ORDER_ID" value="<?= $order->getId() ?>">
                <?php if ($arParams['HASH']) { ?>
                    <input type="hidden" name="HASH" value="<?= $order->getHash() ?>">
                <?php } ?>
                <input type="hidden" name="PAY" value="Y">
                <div class="b-order__ord-wrapper">
                    <h2 class="b-title b-title--order-heading b-title--block">Заказ №<?= $order->getField('ACCOUNT_NUMBER') ?>
                    </h2>
                    <div class="b-order__text-block b-order__text-block--gotopay">
                        <p><?= $orderQuantity ?> <?= $declension->get($orderQuantity) ?>
                            <?php if ($orderWeight) { ?>
                                (<?= WordHelper::showWeight($orderWeight) ?>)
                            <?php } ?>
                            на
                            сумму <?= CurrencyHelper::formatPrice($orderPrice) ?></p>
                    </div>
                    <?php if ($order->isPaid()) { ?>
                        <button class="b-button b-button--order-step-3 b-button--next" disabled="disabled">
                            Заказ оплачен
                        </button>
                    <? } else { ?>
                        <button class="b-button b-button--order-step-3 b-button--next">
                            Перейти к оплате
                        </button>
                    <?php } ?>
                </div>
            </form>
            <?php if ($relatedOrder) { ?>
                <hr class="b-hr b-hr--gotopay">
                <form action="<?= $arResult['RELATED_ORDER_PAY_URL'] ?>" method="get">
                    <input type="hidden" name="ORDER_ID" value="<?= $relatedOrder->getId() ?>">
                    <?php if ($arParams['HASH']) { ?>
                        <input type="hidden" name="HASH" value="<?= $relatedOrder->getHash() ?>">
                    <?php } ?>
                    <input type="hidden" name="PAY" value="Y">
                    <div class="b-order__ord-wrapper">
                        <h2 class="b-title b-title--order-heading b-title--block">Заказ №<?= $relatedOrder->getField('ACCOUNT_NUMBER') ?>
                        </h2>
                        <div class="b-order__text-block b-order__text-block--gotopay">
                            <p><?= $relatedOrderQuantity ?>  <?= $declension->get($relatedOrderQuantity) ?>
                                <?php if ($relatedOrderWeight) { ?>
                                    (<?= WordHelper::showWeight($relatedOrderWeight) ?>)
                                <?php } ?>
                                на
                                сумму <?= CurrencyHelper::formatPrice($relatedOrderPrice) ?>
                            </p>
                        </div>
                        <?php if ($relatedOrder->isPaid()) { ?>
                            <button class="b-button b-button--order-step-3 b-button--next" disabled="disabled">
                                Заказ оплачен
                            </button>
                        <? } else { ?>
                            <button class="b-button b-button--order-step-3 b-button--next">
                                Перейти к оплате
                            </button>
                        <?php } ?>
                    </div>
                </form>
            <?php } ?>
        </div>
    </div>
<?php } ?>
