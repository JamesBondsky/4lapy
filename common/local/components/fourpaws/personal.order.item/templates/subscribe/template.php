<?php

use Bitrix\Main\Application;
use Bitrix\Main\Web\Uri;
use Bitrix\Sale\Order as BitrixOrder;
use FourPaws\App\Application as SymfoniApplication;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\DateHelper;
use FourPaws\Helpers\WordHelper;
use FourPaws\PersonalBundle\Entity\Order;
use FourPaws\PersonalBundle\Entity\OrderItem;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use FourPaws\PersonalBundle\Entity\OrderSubscribeItem;
use FourPaws\SaleBundle\Enum\OrderPayment;
use FourPaws\SaleBundle\Enum\OrderStatus;
use FourPaws\SaleBundle\Service\OrderPropertyService;
use FourPaws\SaleBundle\Service\OrderService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * @global CMain                                  $APPLICATION
 * @var array                                     $arParams
 * @var array                                     $arResult
 * @var FourPawsPersonalCabinetOrderItemComponent $component
 * @var CBitrixComponentTemplate                  $this
 * @var string                                    $templateName
 * @var string                                    $componentPath
 */

/** @var Order $order */
$order = $arResult['ORDER'];

/** @var OrderSubscribe $orderSubscribe */
$orderSubscribe = $arParams['ORDER_SUBSCRIBE'] ?? null;
$isOrderSubscribePage = $orderSubscribe ? true : false;

$attr = '';
if ($orderSubscribe) {
    //$attr .= ' data-first-subscribe="' . $orderSubscribe->getDateCreate() . '"';
    $attr .= ' data-interval="' . $orderSubscribe->getDeliveryTime() . '"';
    $attr .= ' data-frequency="' . $orderSubscribe->getFrequency() . '"';
    $attr .= ' data-id="' . $orderSubscribe->getId() . '"';
}

$activeSubscribe = $orderSubscribe->isActive();

if($activeSubscribe){
    $nearestDeliveryDate = $orderSubscribe->getNearestDelivery();
    if($nearestDeliveryDate){
        $nearestDeliveryDate = (new \DateTime($nearestDeliveryDate))->format('d #n# Y');
    } else {
        $arResult['ERROR'] = 'Не найдена ближайшая дата доставки';
    }
}


if(!empty($arResult['ERROR'])){
    ShowError($arResult['ERROR']);
    return;
}
?>

<li<?= $attr ?> class="b-accordion-order-item b-accordion-order-item--subscribe js-permutation-li js-item-content">
    <div class="b-accordion-order-item__visible js-premutation-accordion-content">
        <div class="b-accordion-order-item__info">
            <a class="b-accordion-order-item__open-accordion js-open-accordion"
               href="javascript:void(0);"
               title="">
                <span class="b-accordion-order-item__arrow">
                    <span class="b-icon b-icon--account">
                        <?= new SvgDecorator('icon-arrow-account', 25, 25) ?>
                    </span>
                </span>

                <span class="b-accordion-order-item__img-wrap">
                    <img class="b-accordion-order-item__img"
                         src="<?= $arResult['ITEMS'][0]['IMAGE'] ?>"
                         alt="<?= $arResult['ITEMS'][0]['NAME'] ?>"
                         title="<?= $arResult['ITEMS'][0]['NAME'] ?>"
                         role="presentation"/>
                </span>
            </a>
            <?php
            $countItems = count($arResult['ITEMS']);
            if ($countItems > 1) { ?>
                <div class="b-accordion-order-item__info-order">+ ещё <?= $countItems - 1 ?> <?= WordHelper::declension($countItems - 1,
                        [
                            'товар',
                            'товара',
                            'товаров',
                        ]) ?>
                </div>
            <?php } ?>
        </div>
        <div class="b-accordion-order-item__adress">
            <div class="b-accordion-order-item__number-order <?php if(!$activeSubscribe) { ?>b-accordion-order-item__number-order--disabled<?php } ?>">
                <span>Доставка</span>
                <?php
                echo $orderSubscribe->getDeliveryFrequencyEntity()
                    ->getValue();
                ?>
            </div>
            <? if($activeSubscribe) { ?>
                <div class="b-accordion-order-item__date b-accordion-order-item__date--new">
                    <?php
                        echo 'Следующая доставка ';
                        echo DateHelper::replaceRuMonth(
                            $nearestDeliveryDate,
                            DateHelper::GENITIVE,
                            true
                        );
                        echo '</span>';
                     ?>
                </div>
            <? } ?>
            <div class="b-adress-info b-adress-info--order">
                <?= $orderSubscribe->getDeliveryPlaceAddress() ?>
            </div>
        </div>
        <div class="b-accordion-order-item__button">
            <a class="b-link b-link--repeat-order b-link--change-subscribe-delivery"
               href="javascript:void(0);"
               title="Редактировать подписку"
               data-popup-id="change-subscribe-delivery"
               data-subscribe-delivery-popup="edit"
               data-subscribe-id="<?=$orderSubscribe->getId()?>">
                <span class="b-link__text b-link__text--change-subscribe-delivery">Редактировать <span>подписку</span></span>
            </a>
            <?php if($activeSubscribe) { ?>
                <a class="b-link b-link--repeat-order b-link--change-subscribe-delivery js-open-popup"
                   href="javascript:void(0);"
                   title="Остановить подписку"
                   data-subscribe-id="<?=$orderSubscribe->getId()?>"
                   data-popup-id="stop-subscribe-delivery">
                    <span class="b-link__text b-link__text--change-subscribe-delivery">Остановить <span>подписку</span></span>
                </a>
            <?php } else { ?>
                <a class="b-link b-link--repeat-order b-link--repeat-order"
                   href="javascript:void(0);"
                   title="Возобновить подписку"
                   data-subscribe-id="<?=$orderSubscribe->getId()?>"
                   data-popup-id="renew-subscribe-delivery">
                    <span class="b-link__text b-link__text--repeat-order">Возобновить <span>подписку</span></span>
                </a>
            <?php } ?>
        </div>
        <div class="b-accordion-order-item__operation">
            <div class="b-accordion-order-item__sum">
                <?php echo WordHelper::numberFormat(round($orderSubscribe->getPrice(), 2)); ?>
                <span class="b-ruble b-ruble--account-accordion">&nbsp;₽</span>
            </div>
        </div>
    </div>
    <div class="b-accordion-order-item__hidden js-hidden-order">
        <ul class="b-list-order">
            <?php
            foreach ($arResult['ITEMS'] as $item) {
                if ($item['DETAIL_PAGE_URL']) { ?>
                    <a href="<?= $item['DETAIL_PAGE_URL'] ?>">
                <?php } ?>
                <li class="b-list-order__item">
                    <div class="b-list-order__image-wrapper">
                        <?php if ($item['IMAGE']) { ?>
                            <img class="b-list-order__image js-image-wrapper"
                                 src="<?= $item['IMAGE'] ?>" alt="<?= $item['NAME'] ?>"
                                 title="<?= $item['NAME'] ?>"
                                 role="presentation"/>
                        <?php } ?>
                    </div>
                    <div class="b-list-order__wrapper">
                        <div class="b-list-order__info">
                            <?php if ($item['HAS_STOCK']) { ?>
                                <div class="b-list-order__action">Сейчас
                                    участвует в акции
                                </div>
                            <?php } ?>
                            <div class="b-clipped-text b-clipped-text--account">
                                <span>
                                    <?php if (!empty($item['BRAND'])) { ?>
                                    <span class="span-strong"><?= $item['BRAND'] ?>  </span>
                                <?php } ?><?= $item['NAME'] ?>
                                </span>
                            </div>
                            <div class="b-list-order__option">
                                <?php /*if (!empty($item['FLAVOUR'])) { ?>
                                    <div class="b-list-order__option-text">
                                        Вкус:
                                        <span><?= $item['FLAVOUR'] ?></span>
                                    </div>
                                <?php }*/

                                /*if (!empty($item->getOfferSelectedProp())) { */?><!--
                                    <div class="b-list-order__option-text">
                                        <?/*= $item->getOfferSelectedPropName() */?>:
                                        <span><?/*= $item->getOfferSelectedProp() */?></span>
                                    </div>
                                --><?php /*}*/

                                if ($item['WEIGHT']) { ?>
                                    <div class="b-list-order__option-text">Вес:
                                        <span><?= WordHelper::showWeight($item['WEIGHT'], true) ?></span>
                                    </div>
                                <?php }

                                if (!empty($item['ARTICLE'])) { ?>
                                    <div class="b-list-order__option-text">Артикул:
                                        <span><?= $item['ARTICLE'] ?></span>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="b-list-order__price">
                            <div class="b-list-order__sum b-list-order__sum--item"><?= $item['SUM'] ?>
                                <span class="b-ruble b-ruble--account-accordion">&nbsp;₽</span>
                            </div>
                            <?php if ($item['QUANTITY'] > 1) { ?>
                                <div class="b-list-order__calculation"><?= $item['PRICE'] ?> ₽
                                    × <?= $item['QUANTITY'] ?> шт
                                </div>
                            <?php } ?>
                            <div class="b-list-order__bonus js-order-item-bonus-<?= $item['ID'] ?>"></div>
                        </div>
                    </div>
                </li>
                <?php if (!empty($item['DETAIL_PAGE_URL'])) { ?>
                    </a>
                <?php } ?>
            <?php } ?>
        </ul>
        <div class="b-accordion-order-item__calculation-full">
            <ul class="b-characteristics-tab__list">
                <li class="b-characteristics-tab__item b-characteristics-tab__item--account">
                    <div class="b-characteristics-tab__characteristics-text b-characteristics-tab__characteristics-text--account">
                        <span>Товары</span>
                        <div class="b-characteristics-tab__dots"></div>
                    </div>
                    <div class="b-characteristics-tab__characteristics-value b-characteristics-tab__characteristics-value--account">
                        <?= WordHelper::numberFormat(round($orderSubscribe->getPrice(), 2)) ?>
                        <span class="b-ruble b-ruble--calculation-account">&nbsp;₽</span>
                    </div>
                </li>
                <?php if ($orderSubscribe->getDeliveryPrice() > 0) { ?>
                    <li class="b-characteristics-tab__item b-characteristics-tab__item--account">
                        <div class="b-characteristics-tab__characteristics-text b-characteristics-tab__characteristics-text--account">
                            <span>Доставка</span>
                            <div class="b-characteristics-tab__dots"></div>
                        </div>
                        <div class="b-characteristics-tab__characteristics-value b-characteristics-tab__characteristics-value--account">
                            <?= WordHelper::numberFormat(round($orderSubscribe->getDeliveryPrice(), 2)) ?>
                            <span class="b-ruble b-ruble--calculation-account">&nbsp;₽</span>
                        </div>
                    </li>
                    <?php
                }
                ?>
                <li class="b-characteristics-tab__item b-characteristics-tab__item--account">
                    <div class="b-characteristics-tab__characteristics-text b-characteristics-tab__characteristics-text--account b-characteristics-tab__characteristics-text--last">
                        <span>Итого к оплате</span>
                        <div class="b-characteristics-tab__dots"></div>
                    </div>
                    <div class="b-characteristics-tab__characteristics-value b-characteristics-tab__characteristics-value--account b-characteristics-tab__characteristics-value--last">
                        <?= WordHelper::numberFormat(round(($orderSubscribe->getDeliveryPrice() + $orderSubscribe->getPrice()), 2)) ?>
                        <span class="b-ruble b-ruble--calculation-account">&nbsp;₽</span>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <div class="b-accordion-order-item__mobile-bottom js-button-permutation-mobile">
    </div>
</li>
<?php
