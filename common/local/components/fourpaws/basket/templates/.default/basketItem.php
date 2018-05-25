<?php

/**
 * @var BasketItem         $basketItem
 * @var float              $userDiscount
 * @var Offer              $offer
 * @var bool               $isOnlyPickup
 *
 * @global BasketComponent $component
 */

use Bitrix\Sale\BasketItem;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Components\BasketComponent;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\WordHelper;

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
$isDiscounted = $basketItem->getBasePrice() !== $basketItem->getPrice();
/**
 * @todo promo from property; after - promo from PromoLink;
 */

if ($useOffer && (($offer->getQuantity() > 0 && !$basketItem->isDelay()) || $offer->isByRequest())) {
    $templateData['OFFERS'][] = ['ID' => $offer->getId(), 'QUANTITY' => $basketItem->getQuantity()];
} ?>
<div class="b-item-shopping js-remove-shopping">
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
                        <span class="b-gift-order__text-additional js-dropdown-gift">
                            <?= $oneLink['name']; ?>
                        </span>
                    </span>
                </div>
            </div>
        <?php }
    } ?>
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
               href="<?= $basketItem->getField('DETAIL_PAGE_URL'); ?>" title="">
            <span class="b-clipped-text b-clipped-text--shopping-cart">
                <span>
                    <?php if ($useOffer) { ?>
                        <strong><?= $offer->getProduct()->getBrandName() ?>  </strong>
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
            <?php if ($propertyValues['HAS_BONUS']['VALUE'] && $useOffer && (($offer->getQuantity() > 0 && !$basketItem->isDelay()) || $offer->isByRequest())) { ?>
                <span class="b-common-item__rank-text b-common-item__rank-text--red b-common-item__rank-text--shopping js-bonus-<?= $offer->getId() ?>">
                    <?php if ($arParams['IS_AJAX']) {
                        echo $offer->getBonusFormattedText((int)$userDiscount, $basketItem->getQuantity(), 0);
                    } ?>
                </span>
            <?php } ?>
        </div>
    </div>
    <div class="b-item-shopping__operation<?= $offer->getQuantity() > 0 ? ' b-item-shopping__operation--not-available' : '' ?>">
        <?php
        $maxQuantity = 1000;
        if ($useOffer) {
            $maxQuantity = $offer->getQuantity();
        }

        if (!$basketItem->isDelay() && $offer->getQuantity() > 0) { ?>
            <div class="b-plus-minus b-plus-minus--half-mobile b-plus-minus--shopping js-plus-minus-cont">
                <a class="b-plus-minus__minus js-minus" data-url="<?= $basketUpdateUrl ?>"
                   href="javascript:void(0);"></a>

                <input title="" class="b-plus-minus__count js-plus-minus-count"
                       value="<?= WordHelper::numberFormat($basketItem->getQuantity(), 0) ?>"
                       data-one-price="<?= $basketItem->getPrice() ?>"
                       data-cont-max="<?= $maxQuantity ?>"
                       data-basketid="<?= $basketItemId; ?>"
                       data-url="<?= $basketUpdateUrl ?>"
                       type="text"/>

                <a class="b-plus-minus__plus js-plus" data-url="<?= $basketUpdateUrl ?>"
                   href="javascript:void(0);"></a>
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
                <span class="b-price__current"><?= WordHelper::numberFormat($basketItem->getPrice() * $basketItem->getQuantity()) ?></span>
                <span class="b-ruble">₽</span>
                <?php if ($basketItem->getDiscountPrice() > 0) { ?>
                    <span class="b-old-price b-old-price--crossed-out">
                    <span class="b-old-price__old"><?= WordHelper::numberFormat($basketItem->getBasePrice() * $basketItem->getQuantity()) ?>  </span>
                    <span class="b-ruble b-ruble--old-weight-price">₽</span>
                </span>
                <?php } ?>
            </div>
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
        <a class="b-item-shopping__delete js-cart-delete-item" href="javascript:void(0);" title=""
           data-url="<?= $basketDeleteUrl ?>" data-basketId="<?= $basketItemId; ?>">
            <span class="b-icon b-icon--delete b-icon--shopping">
                <?= new SvgDecorator('icon-delete-cart-product', 12, 14); ?>
            </span>
        </a>
        <?php if ($isDiscounted || $basketItem->getQuantity() > 1) { ?>
            <div class="b-item-shopping__sale-info">
                <?php if ($isDiscounted) { ?>
                    <span class="b-old-price b-old-price--inline b-old-price--crossed-out">
                        <span class="b-old-price__old"><?= WordHelper::numberFormat($basketItem->getBasePrice()) ?> </span>
                        <span class="b-ruble b-ruble--old-weight-price">₽</span>
                    </span>
                <?php } ?>
                <span class="b-old-price b-old-price--inline">
                    <span class="b-old-price__old"><?= WordHelper::numberFormat($basketItem->getPrice()) ?> </span>
                    <span class="b-ruble b-ruble--old-weight-price">₽</span>
                </span>
                <span class="b-old-price b-old-price--inline b-old-price--on">
                    <span class="b-old-price__old"><?= $basketItem->getQuantity() ?> </span>
                    <span class="b-ruble b-ruble--old-weight-price">шт</span>
                </span>
                <?php
                if ($isDiscounted) {
                    $informationText = 'Товар со скидкой';
                    $descriptions = $component->getPromoLink($basketItem, true);
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
        <?php } ?>
        <?php if ($offer->isByRequest() && !empty($arResult['OFFER_MIN_DELIVERY'][$basketItem->getProductId()])) {
            /** @todo пока берем ближайшую доставку из быстрого заказа */ ?>
            <div class="b-item-shopping__sale-info b-item-shopping__sale-info--width">
                Предварительная дата доставки:
                <span><?= $arResult['OFFER_MIN_DELIVERY'][$basketItem->getProductId()] ?></span>
            </div>
        <?php } ?>
    </div>
</div>
