<?php

/**
 * @var BasketItem $basketItem
 * @var float $userDiscount
 * @var Offer $offer
 * @var bool $isOnlyPickup
 *
 * @global BasketComponent $component
 */

use Bitrix\Sale\BasketItem;
use Bitrix\Sale\BasketPropertyItem;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Components\BasketComponent;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\Enum\IblockElementXmlId;
use FourPaws\Helpers\WordHelper;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\PersonalBundle\Service\StampService;

$basketUpdateUrl = '/ajax/sale/basket/update/';
$basketDeleteUrl = '/ajax/sale/basket/delete/';
$propertyValues = $basketItem->getPropertyCollection()->getPropertyValues();
$basketItemId = $basketItem->getId();

/** у отделнных скидками строчек нет айди, поэтому берем айди той строчки от которой отделили */
if (!$basketItemId && $propertyValues['DETACH_FROM']) {
    $basketItemId = (int)$propertyValues['DETACH_FROM']['VALUE'];
}

$promoLinks = $component->getPromoLink($basketItem);
$image = $component->getImage((int)$basketItem->getProductId());
$useOffer = $offer instanceof Offer && $offer->getId() > 0;
$isDiscounted = (float)$basketItem->getBasePrice() - (float)$basketItem->getPrice() >= 0.01;

$canUseStamps = isset(StampService::EXCHANGE_RULES[$offer->getXmlId()]);
$stampLevels = ($canUseStamps) ? StampService::EXCHANGE_RULES[$offer->getXmlId()] : null;
$useStamps = false;
if (isset($basketItem->getPropertyCollection()->getPropertyValues()['USE_STAMPS'])) {
    $useStamps = (bool)$basketItem->getPropertyCollection()->getPropertyValues()['USE_STAMPS']['VALUE'];
}

/**
 * @todo promo from property; after - promo from PromoLink;
 */
if ($useOffer && (($offer->getQuantity() > 0 && !$basketItem->isDelay()) || $offer->isByRequest())) {
    // kek, самое место для сбора данных
    $templateData['OFFERS'][] = ['ID' => $offer->getId(), 'QUANTITY' => $basketItem->getQuantity()];
} ?>
<div class="b-item-shopping js-remove-shopping js-item-shopping" data-productid="<?= $basketItemId; ?>">
    <?php
    if (\is_iterable($promoLinks)) {
        foreach ($promoLinks as $oneLink) {
            ?>
            <div class="b-gift-order b-gift-order--shopping js-open-gift">
                <div class="b-gift-order__info">
                    <span class="b-gift-order__text">Товар участвует в акции
                        <span class="b-icon b-icon--shopping-gift js-icon-shopping-gift">
                            <?= new SvgDecorator('icon-arrow-down', 10, 6); ?>
                        </span>
                        <span class="b-gift-order__dash js-dash">- </span>
                        <a href="<?= $oneLink['url']; ?>" class="b-gift-order__text-additional js-dropdown-gift">
                            <?= $oneLink['name']; ?>
                        </a>
                    </span>
                </div>
            </div>
        <?php }
    } ?>

    <?php if ($canUseStamps) { ?>
        <div class="b-mark-order">
            <div class="b-mark-order__info">
                <span class="b-mark-order__text">Используйте марки, чтобы купить товар со скидкой! Вам  доступно — <?= $arResult['ACTIVE_STAMPS_COUNT'] ?></span><? //TODO correct value. If 0 then don't show this text ?>
                <span class="b-icon b-icon--mark"><?= new SvgDecorator('icon-mark', 12, 12) ?></span>
            </div>
        </div>
    <?php } ?>

    <div class="b-common-item b-common-item--shopping-cart b-common-item--shopping">
        <span class="b-common-item__image-wrap b-common-item__image-wrap--shopping-cart">
            <?php if (null !== $image) { ?>
                <img class="b-common-item__image b-common-item__image--shopping-cart"
                     src="<?= $image; ?>"
                     alt="<?= $basketItem->getField('NAME') ?>" title=""/>
            <?php } ?>
        </span>
        <div class="b-common-item__info-center-block b-common-item__info-center-block--shopping-cart b-common-item__info-center-block--shopping">
            <a class="b-common-item__description-wrap b-common-item__description-wrap--shopping"
               href="<? if ($offer->getXmlId() != BasketService::GIFT_DOBROLAP_XML_ID) { ?><?= $basketItem->getField('DETAIL_PAGE_URL'); ?><? } else {?>javascript::void(0);<? } ?>" title="">
            <span class="b-clipped-text b-clipped-text--shopping-cart">
                <span>
                    <?php if ($useOffer) { ?>
                        <span class="span-strong"><?= $offer->getProduct()->getBrandName() ?>  </span>
                    <?php } ?>
                    <?= $basketItem->getField('NAME') ?>
                </span>
            </span>
                <?php
                if ($basketItem->getWeight() > 0) {
                    ?>
                    <span class="b-common-item__variant b-common-item__variant--shopping-cart b-common-item__variant--shopping">
                        <span class="b-common-item__name-value">Вес: </span>
                        <span><?= WordHelper::showWeight($basketItem->getWeight(), true) ?></span>
                    </span>
                    <?php
                }

                if ($useOffer) {
                    $color = $offer->getColor();
                    if ($color !== null) { ?>
                        <span class="b-common-item__variant b-common-item__variant--shopping-cart b-common-item__variant--shopping">
                            <span class="b-common-item__name-value">Цвет: </span>
                            <span><?= $color->getName() ?></span>
                        </span>
                    <?php }
                    $article = $offer->getXmlId();
                    if (!empty($article)) { ?>
                        <span class="b-common-item__variant b-common-item__variant--shopping-cart b-common-item__variant--shopping">
                            <span class="b-common-item__name-value">Артикул: </span>
                            <span class="b-common-item__name-value b-common-item__name-value--shopping-mobile">, Арт. </span><span><?= $article ?></span>
                        </span>
                    <?php }
                } ?>
            </a>
            <?php
            $basketCodes = [];
            $basketCodes[] = $basketItem->getBasketCode();
            if ($arResult['ROWS_MAP'][$basketItem->getBasketCode()]) {
                $basketCodes = array_merge($basketCodes, $arResult['ROWS_MAP'][$basketItem->getBasketCode()]['ROWS']);
            }

            $bonusQty = 0;
            foreach ($basketCodes as $code) {
                /** @var BasketItem $tItem */
                $tItem = $arResult['BASKET']->getItemByBasketCode($code);
                /** @var BasketPropertyItem $basketPropertyItem */
                foreach ($tItem->getPropertyCollection() as $basketPropertyItem) {
                    if ($basketPropertyItem->getField('CODE') === 'HAS_BONUS') {
                        $bonusQty += (int)$basketPropertyItem->getField('VALUE');
                    }
                }
            }


            if ($bonusQty && $useOffer && (($offer->getQuantity() > 0 && !$basketItem->isDelay()) || $offer->isByRequest())) {
                ?>
                <span class="b-common-item__rank-text b-common-item__rank-text--red b-common-item__rank-text--shopping js-bonus-<?= $offer->getId() ?>">
                    <?php if ($arParams['IS_AJAX']) {
                        echo $offer->getBonusFormattedText((int)$userDiscount, $bonusQty, 0);
                    } ?>
                </span>
                <?php
            }
            ?>
        </div>
    </div>
    <?/* Класс b-item-shopping__operation--marks нужен только если будут марки*/ //TODO this logic ?>
    <div class="b-item-shopping__operation<?= ($canUseStamps) ? ' b-item-shopping__operation--marks' : '' ?><?= $offer->getQuantity() > 0 ? ' b-item-shopping__operation--not-available' : '' ?>">
        <?php
        $maxQuantity = 1000;
        if ($useOffer) {
            $maxQuantity = $offer->getQuantity();
        }

        if (!$basketItem->isDelay() && $offer->getQuantity() > 0) { ?>
            <div class="b-item-shopping__operation-inner"><?/* Эта обертка нужна только если будут марки*/ //TODO this logic ?>
                <div class="b-plus-minus b-plus-minus--half-mobile b-plus-minus--shopping js-plus-minus-cont" <? if ($offer->getXmlId() == BasketService::GIFT_DOBROLAP_XML_ID) { ?>style="background:transparent;"<?}?>>
                    <? if ($offer->getXmlId() != BasketService::GIFT_DOBROLAP_XML_ID) { ?>
                        <a class="b-plus-minus__minus js-minus" data-url="<?= $basketUpdateUrl ?>"
                           href="javascript:void(0);"></a>
                        <?php
                        /** @todo data-one-price */
                        ?>
                        <input title="" class="b-plus-minus__count js-plus-minus-count"
                               value="<?= WordHelper::numberFormat($arResult['PRODUCT_QUANTITIES'][$basketItem->getProductId()] ?? $basketItem->getQuantity(),
                                   0) ?>"
                               data-one-price="<?= $basketItem->getPrice() ?>"
                               data-cont-max="<?= $maxQuantity ?>"
                               data-basketid="<?= $basketItemId; ?>"
                               data-url="<?= $basketUpdateUrl ?>"
                               type="text"/>

                        <a class="b-plus-minus__plus js-plus" data-url="<?= $basketUpdateUrl ?>"
                           href="javascript:void(0);"></a>
                    <? } ?>
                </div>
                <div class="b-select b-select--shopping-cart">
                    <?php $maxMobileQuantity = 100;

                    if ($maxQuantity < $maxMobileQuantity) {
                        $maxMobileQuantity = $maxQuantity;
                    } ?>
                    <select title="" class="b-select__block b-select__block--shopping-cart"
                            name="shopping-cart">
                        <option value="" disabled="disabled" selected="selected">выберите</option>
                        <?php for ($i = 0; $i < $maxMobileQuantity; $i++) { ?>
                            <option value="one-click-<?= $i ?>"><?= $i + 1 ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="b-price">
                    <span class="b-price__current">
                        <?= WordHelper::numberFormat(
                            $arResult['ROWS_MAP'][$basketItem->getBasketCode()]['TOTAL_PRICE']
                            ??
                            $basketItem->getPrice() * $basketItem->getQuantity()
                        ) ?>
                    </span>
                    <span class="b-ruble">₽</span>
                    <?php
                    //сюда же влетает округление до копеек при пересчете НДС, поэтому 0,01
                    if ($basketItem->getDiscountPrice() >= 0.01) {
                        ?>
                        <span class="b-old-price b-old-price--crossed-out">
                        <span class="b-old-price__old">
                            <?= WordHelper::numberFormat(
                                $arResult['ROWS_MAP'][$basketItem->getBasketCode()]['BASE_PRICE']
                                ??
                                $basketItem->getBasePrice() * $basketItem->getQuantity()
                            ) ?>
                        </span>
                        <span class="b-ruble b-ruble--old-weight-price">₽</span>
                    </span>
                        <?php
                    }
                    ?>
                </div>
                <? if ($offer->getXmlId() != BasketService::GIFT_DOBROLAP_XML_ID) { ?>
                    <a class="b-item-shopping__delete js-cart-delete-item" href="javascript:void(0);" title=""
                       data-url="<?= $basketDeleteUrl ?>" data-basketId="<?= $basketItemId; ?>">
                        <span class="b-icon b-icon--delete b-icon--shopping">
                            <?= new SvgDecorator('icon-delete-cart-product', 12, 14); ?>
                        </span>
                    </a>
                <? } ?>
            </div><?/* Эта обертка нужна только если будут марки*/ //TODO this logic?>
            <?php if ($canUseStamps && count($stampLevels)) { ?>
                <div class="b-mark-order-price"><? //TODO correct values in this block and conditions to show it ?>
                    <div class="b-mark-order-price__list">
                        <?php foreach ($stampLevels as $stampLevel) { ?>
                            <div class="b-mark-order-price__item">
                                <?= $stampLevel['price'] ?> ₽ — <?= $stampLevel['stamps'] ?>
                                <span class="b-icon b-icon--mark">
                                    <?= new SvgDecorator('icon-mark', 12, 12) ?>
                                </span>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="b-mark-order-price__action">
                        <?php if ($useStamps) { ?>
                        <span data-cancel-charge-marks-cart="true">
                        Отменить<br/> списание 7
                        <span class="b-icon b-icon--mark">
                            <?= new SvgDecorator('icon-mark', 12, 12) ?>
                        </span>
                            <?php } else { ?>
                                <span data-use-marks-cart="true">Использовать марки</span>
                            <?php } ?>
                    </span>
                    </div>
                </div>
            <?php } ?>
            <?php if (in_array($offer->getId(), $arResult['ONLY_PICKUP'], true)) { ?>
                <div class="b-item-shopping__sale-info b-item-shopping__sale-info--width b-item-shopping__sale-info--not-available">
                    Только самовывоз
                </div>
            <?php } ?>
        <?php } else { ?>
            <div class="b-item-shopping__sale-info b-item-shopping__sale-info--width b-item-shopping__sale-info--not-available">
                Нет в наличии
            </div>
        <?php } ?>
        <? if ($offer->getXmlId() != BasketService::GIFT_DOBROLAP_XML_ID) { ?>
            <?php if (!(!$basketItem->isDelay() && $offer->getQuantity() > 0)) { ?>
                <a class="b-item-shopping__delete js-cart-delete-item" href="javascript:void(0);" title=""
                   data-url="<?= $basketDeleteUrl ?>" data-basketId="<?= $basketItemId; ?>">
                    <span class="b-icon b-icon--delete b-icon--shopping">
                        <?= new SvgDecorator('icon-delete-cart-product', 12, 14); ?>
                    </span>
                </a>
            <?php } ?>
        <? } ?>
        <?php
        $basketCodes = [];
        if ($isDiscounted || $basketItem->getQuantity() > 1 || $arResult['PRODUCT_QUANTITIES'][$basketItem->getProductId()] > 1) {
            $basketCodes[] = $basketItem->getBasketCode();
            if ($arResult['ROWS_MAP'][$basketItem->getBasketCode()]) {
                $basketCodes = array_merge($basketCodes, $arResult['ROWS_MAP'][$basketItem->getBasketCode()]['ROWS']);
            }

            foreach ($basketCodes as $code) {
                $tItem = $arResult['BASKET']->getItemByBasketCode($code);

                ?>

                <div class="b-item-shopping__sale-info b-item-shopping__sale-info--count">
                    <?php
                    if ((float)$tItem->getBasePrice() - (float)$tItem->getPrice() >= 0.01) {
                        ?>
                        <span class="b-old-price b-old-price--inline b-old-price--crossed-out">
                        <span class="b-old-price__old"><?= WordHelper::numberFormat($tItem->getBasePrice()) ?> </span>
                        <span class="b-ruble b-ruble--old-weight-price">₽</span>
                    </span>
                        <?php
                    }
                    ?>
                    <span class="b-old-price b-old-price--inline">
                    <span class="b-old-price__old"><?= WordHelper::numberFormat($tItem->getPrice()) ?> </span>
                    <span class="b-ruble b-ruble--old-weight-price">₽</span>
                </span>
                    <span class="b-old-price b-old-price--inline b-old-price--on">
                    <span class="b-old-price__old"><?= $tItem->getQuantity() ?> </span>
                    <span class="b-ruble b-ruble--old-weight-price">шт</span>
                </span>
                    <?php
                    if ($tItem->getBasePrice() !== $tItem->getPrice()) {
                        $informationText = 'Товар со скидкой';
                        $descriptions = $component->getPromoLink($tItem, true);
                        if (\count($descriptions) === 1) {
                            $informationText = 'Скидка: <br>';
                        } elseif (\count($descriptions) > 1) {
                            $informationText = 'Скидки: <br>';
                        }
                        foreach ($descriptions as $description) {
                            $informationText .= $description['name'] . '<br>';
                        }
                        ?>
                        <a class="b-information-link js-popover-information-open js-popover-information-open"
                           href="javascript:void(0);" title="">
                            <span class="b-information-link__icon">i</span>
                            <div class="b-popover-information js-popover-information">
                                <?= $informationText; ?>
                            </div>
                        </a>
                    <?php } ?>
                </div>
                <?php
            }
        }
        ?>
        <?php if ($offer->isByRequest() && !empty($arResult['OFFER_MIN_DELIVERY'][$basketItem->getProductId()])) {
            /** @todo пока берем ближайшую доставку из быстрого заказа */ ?>
            <div class="b-item-shopping__sale-info b-item-shopping__sale-info--width">
                Предварительная дата доставки:
                <span><?= $arResult['OFFER_MIN_DELIVERY'][$basketItem->getProductId()] ?></span>
            </div>
        <?php } ?>
    </div>
</div>
