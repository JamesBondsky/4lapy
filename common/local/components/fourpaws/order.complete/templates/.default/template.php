<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Grid\Declension;
use Bitrix\Sale\Order;
use FourPaws\App\Application as App;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\KioskBundle\Service\KioskService;
use FourPaws\SaleBundle\Service\OrderPropertyService;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;

/**
 * @var CMain $APPLICATION
 * @var array $arResult
 * @var array $arParams
 */

/** @var Order $order */
$order = $arResult['ORDER'];

/** @var Order $relatedOrder */
$relatedOrder = $arResult['RELATED_ORDER'];
$bonusCount = $arResult['ORDER_PROPERTIES']['BONUS_COUNT'] + $arResult['RELATED_ORDER_PROPERTIES']['BONUS_COUNT'];

if ($bonusCount > 0) { // самое место
    $bonusCount = floor($bonusCount);//округляем до целого
}

if ($arResult['ECOMMERCE_VIEW_SCRIPT']) {
    echo $arResult['ECOMMERCE_VIEW_SCRIPT'];
}

$emailCurrentUser = null;

try {
    /** @var \FourPaws\UserBundle\Service\UserSearchInterface $userCurrentUserService */
    $userCurrentUserService = Application::getInstance()->getContainer()->get(\FourPaws\UserBundle\Service\UserSearchInterface::class);
    $currentUser = $userCurrentUserService->findOne($order->getUserId());

    $emailCurrentUser = $currentUser->getEmail();
} catch (Exception $e) {}

?>
<div class="b-container">
    <h1 class="b-title b-title--h1 b-title--order">
        <strong><?= $arResult['ORDER_PROPERTIES']['NAME'] ?></strong>, спасибо за заказ!

    </h1>
    <div class="b-order">
        <?php /*
        <div class="b-order__text-block b-order__text-block--top-line b-order__text-block--light js">
            <span class="b-icon b-icon--clock">
              <svg class="b-icon__svg" viewBox="0 0 16 16 " width="16px" height="16px">
                <use class="b-icon__use" xlink:href="icons.svg#icon-clock">
                </use>
              </svg>
            </span>Идёт подсчёт баллов ...
        </div>
         */ ?>
        <?php if ($bonusCount > 0) { ?>
            <div class="b-order__text-block b-order__text-block--top-line js">
                <p>Вы получили <?= $bonusCount ?>
                    <?= (new Declension(
                        'бонусный балл',
                        'бонусных балла',
                        'бонусных баллов'
                    ))->get($bonusCount) ?>.
                    <a class="b-link b-link--inherit b-link--orange"
                       href="/bonus-program/"
                       title="как получить и потратить баллы.">
                        Узнать, как получить и потратить баллы.
                    </a>
                </p>
            </div>
        <?php } ?>
        <hr class="b-hr b-hr--order b-hr--top-line"/>
        <div class="b-order__block b-order__block--no-border">
            <div class="b-order__content b-order__content--no-border b-order__content--step-five">
                <h2 class="b-title b-title--order-heading b-title--block">
                    Заказ № <strong><?= $order->getField('ACCOUNT_NUMBER') ?></strong> оформлен
                </h2>
                <div class="b-order__text-block">

                    <?
                    if ($arResult['ORDER_PROPERTIES']['EMAIL']) {
                        ?>
                        <p>
                            <?= $arResult['ORDER_PROPERTIES']['NAME'] ?>, мы отправили письмо на адрес
                            <strong><?= $arResult['ORDER_PROPERTIES']['EMAIL'] ?></strong> со всеми подробностями заказа.
                        </p>
                        <?
                    }
                    ?>
                </div>
                <?php if ($arResult['isAvatarAuthorized']) { ?>
                    <div class="timer-block js-start-timer" data-url="/front-office/avatar/logout.php">
                        <p>Через <span>20</span>&nbsp;секунд будет выполнен <a href="/front-office/avatar/logout.php">возврат</a>
                        </p>
                    </div>
                <?php }

                if ($arResult['ORDER_PROPERTIES']['COM_WAY'] === OrderPropertyService::COMMUNICATION_PAYMENT_ANALYSIS) { ?>
                    <h2 class="b-title b-title--order-heading b-title--block">Как оплатить заказ</h2>
                    <div class="b-order__text-block">
                        <p>
                            К сожалению, ваш заказ не был оплачен. Способ оплаты был автоматически изменен на
                            постоплату.
                        </p>
                    </div>
                <?php } ?>
                <h2 class="b-title b-title--order-heading b-title--block">Как получить заказ</h2>
                <div class="b-order__text-block">
                    <?php if ($arResult['ORDER_DELIVERY']['IS_DPD_PICKUP']) { ?>
                        <p>Ваш заказ вы можете получить
                            <b>
                                <?= DeliveryTimeHelper::showByDate($arResult['ORDER_DELIVERY']['DELIVERY_DATE']) ?>
                            </b>
                            в пункте самовывоза по
                            адресу
                            <b><?= $arResult['ORDER_DELIVERY']['ADDRESS'] ?></b></p>
                        <?php if ($arResult['ORDER_DELIVERY']['SCHEDULE']) { ?>
                            <p><b>Время работы: </b><?= $arResult['ORDER_DELIVERY']['SCHEDULE'] ?>
                            </p>
                        <?php }
                    } elseif ($arResult['ORDER_DELIVERY']['IS_PICKUP']) { ?>
                        <p>Ваш заказ вы можете получить
                            <b>
                                <?= DeliveryTimeHelper::showByDate($arResult['ORDER_DELIVERY']['DELIVERY_DATE']) ?>
                            </b> в нашем магазине по
                            адресу
                            <b><?= $arResult['ORDER_DELIVERY']['ADDRESS'] ?></b></p>
                        <?php if ($arResult['ORDER_DELIVERY']['SCHEDULE']) {
                            ?>
                            <p><b>Время работы: </b><?= $arResult['ORDER_DELIVERY']['SCHEDULE'] ?>
                            </p>
                        <?php } ?>
                        <p><b>Хранение заказа: </b>Обратите внимание, что заказ будет храниться 3 дня. После этого
                            заказ нужно будет делать заново на нашем сайте или по телефону.</p>
                    <?php } else { ?>
                        <p>Ваш заказ будет доставлен <b><?= DeliveryTimeHelper::showByDate(
                                    $arResult['ORDER_DELIVERY']['DELIVERY_DATE'],
                                    0,
                                    ['SHOW_TIME' => false, 'SHORT' => false]
                                ) ?></b> по
                            адресу
                            <b><?= $arResult['ORDER_DELIVERY']['ADDRESS'] ?></b>
                        </p>
                        <?php if (!$arResult['ORDER_DELIVERY']['IS_DPD_DELIVERY']) { ?>
                            <p>
                                <b>Время доставки: </b><?= $arResult['ORDER_DELIVERY']['DELIVERY_INTERVAL'] ?>
                            </p>
                        <?php }
                    } ?>
                </div>
            </div>
            <?php if ($arResult['ORDER_DELIVERY']['IS_PICKUP'] &&
                $arResult['ORDER_DELIVERY']['SELECTED_SHOP'] instanceof Store
            ) {
                /** @var Store $shop */
                $shop = $arResult['ORDER_DELIVERY']['SELECTED_SHOP']; ?>
                <aside class="b-order__list b-order__list--map">
                    <div class="b-order__map-wrapper"
                         id="map-2"
                         data-coords="[<?= $shop->getLatitude() ?>, <?= $shop->getLongitude() ?>]">
                    </div>
                </aside>
            <?php } ?>
        </div>
        <? if ($arResult['NEED_SHOW_ROYAL_CANIN_BUNNER']) { ?>
            <div class="b-order__action">
                <a href="https://royalcanin.4lapy.ru/" class="b-order__action-banner" target="_blank">
                    <img src="/upload/royal-canin/royal-canin-action.jpg" alt="ROYAL-CANIN"/>
                </a>
                <div class="b-order__text-block">
                    <p><b><?= $arResult['ORDER_PROPERTIES']['NAME'] ?></b>, после оплаты заказа на&nbsp;вашу карту будет начислено 150&nbsp;бонусных баллов, которыми Вы&nbsp;сможете оплатить до&nbsp;90% стоимости следующей покупки
                        кормов Royal&nbsp;Canin.</p>
                    <p>Также Вы&nbsp;сможете зарегистрировать чек и&nbsp;принять участие в&nbsp;розыгрыше ценных подарков и&nbsp;главного приза&nbsp;&mdash; путешествие на&nbsp;родину породы вашего питомца!</p>
                    <p>Подробнее о&nbsp;розыгрыше <a href="https://royalcanin.4lapy.ru/" class="b-link b-link--inherit b-link--orange" target="_blank">royalcanin.4lapy.ru</a></p>
                </div>
            </div>
        <? } ?>
        <div class="b-order__block b-order__block--no-border b-order__block--no-flex">
            <div class="b-order__content b-order__content--no-border b-order__content--no-padding b-order__content--no-flex">
                <?php if ($relatedOrder) { ?>
                    <hr class="b-hr b-hr--order b-hr--step-five"/>
                    <h2 class="b-title b-title--order-heading b-title--block">Заказ
                        №<?= $relatedOrder->getField('ACCOUNT_NUMBER') ?>
                        оформлен</h2>
                    <div class="b-order__text-block">
                        <p>В нем находятся товары "под заказ".
                            <?php if ($arResult['ORDER_PROPERTIES']['EMAIL']) { ?>
                                Мы также отправили на адрес
                                <a class="b-link b-link--blue-bold"
                                   href="mailto:<?= $arResult['ORDER_PROPERTIES']['EMAIL'] ?>"
                                   title=""><?= $arResult['ORDER_PROPERTIES']['EMAIL'] ?>
                                </a>письмо со всеми подробностями заказа.
                            <?php } ?>
                        </p>
                        <p>Условия оплаты и доставки совпадают с заказом выше.</p>
                    </div>
                    <?php if ($arResult['RELATED_ORDER_PROPERTIES']['COM_WAY'] === OrderPropertyService::COMMUNICATION_PAYMENT_ANALYSIS) { ?>
                        <h2 class="b-title b-title--order-heading b-title--block">Как оплатить заказ</h2>
                        <div class="b-order__text-block">
                            <p>
                                К сожалению, ваш заказ не был оплачен. Способ оплаты был автоматически изменен на
                                постоплату.
                            </p>
                        </div>
                    <?php } ?>
                    <h2 class="b-title b-title--order-heading b-title--block">Как получить заказ</h2>
                    <?php if ($arResult['ORDER_DELIVERY']['IS_PICKUP']) { ?>
                        <div class="b-order__text-block">
                            <p>Ваш заказ вы можете получить
                                <b><?= DeliveryTimeHelper::showByDate($arResult['RELATED_ORDER_DELIVERY']['DELIVERY_DATE']) ?></b>
                            </p>
                        </div>
                    <?php } else { ?>
                        <div class="b-order__text-block">
                            <p>Ваш заказ будет доставлен <b><?= DeliveryTimeHelper::showByDate(
                                        $arResult['RELATED_ORDER_DELIVERY']['DELIVERY_DATE'],
                                        0,
                                        ['SHOW_TIME' => false, 'SHORT' => false]
                                    ) ?></b>
                            </p>
                            <?php if (!$arResult['ORDER_DELIVERY']['IS_DPD_DELIVERY']) { ?>
                                <p>
                                    <b>Время
                                        доставки: </b><?= $arResult['RELATED_ORDER_DELIVERY']['DELIVERY_INTERVAL'] ?>
                                </p>
                            <?php } ?>
                        </div>
                    <?php }
                } ?>
                <? if ($arResult['KIOSK_MODE']) { ?>
                    <a href="<?=$arResult['KIOSK_LOGOUT_URL']?>" class="b-button b-button--complete-kiosk">Завершить покупки</a>
                <? } ?>
                <?
                if (empty($emailCurrentUser)) {
                    ?>
                    <hr class="b-hr b-hr--order"/>
                    <?
                    $APPLICATION->IncludeComponent(
                        'fourpaws:expertsender.form',
                        'order.complete',
                        [],
                        false,
                        ['HIDE_ICONS' => 'Y']
                    );
                }
                ?>
                <hr class="b-hr b-hr--order"/>
                <div class="b-order__text-block">
                    <?php if ($arResult['ORDER_REGISTERED']) { ?>
                        <h5 class="b-order__text-list-heading">Также мы создали личный кабинет, где вы можете:</h5>
                        <ul class="b-order__text-list">
                            <li class="b-order__text-item">отслеживать статус заказа;</li>
                            <li class="b-order__text-item">повторять заказы в 1 клик;</li>
                            <li class="b-order__text-item">управлять адресами доставки;</li>
                            <li class="b-order__text-item">узнать баланс вашей бонусной карты.</li>
                        </ul>
                    <?php } ?>
                    <p>
                        Перейти в
                        <a class="b-link b-link--inherit b-link--orange <?= $arResult['IS_AUTH'] ? '' : ' js-open-popup' ?>" <?= $arResult['IS_AUTH'] ? ' href="/personal/index.php"' : ' data-popup-id="authorization" href="javascript:void(0)"' ?>
                           title="личный кабинет">личный
                            кабинет</a>.
                    </p>
                    <p>
                        Что-то забыли? Вы можете добавить товары к заказу -
                        <a class="b-link b-link--inherit b-link--orange" href="/" title="">продолжить покупки</a>.
                    </p>
                    <p>Если у вас остались вопросы, свяжитесь с нами по номеру <?= tplvar('phone_main') ?></p>
                    <?php
                    if (!KioskService::isKioskMode()){
                        $APPLICATION->IncludeFile(
                        'blocks/components/social_share.php',
                        [
                            'shareTitle' => 'Расскажите о нас друзьям',
                            'shareUrl' => '/',
                        ],
                        [
                            'SHOW_BORDER' => false,
                            'NAME' => 'Блок Рассказать в соцсетях',
                            'MODE' => 'php',
                        ]
                        );
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
