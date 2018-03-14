<?php
/** @var BasketItem $basketItem */

/** @global \FourPaws\Components\BasketComponent $component */

use Bitrix\Sale\BasketItem;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\WordHelper;

$image = $component->getImage($basketItem->getProductId());
$offer = $component->getOffer((int)$basketItem->getProductId());
$useOffer = $offer instanceof Offer && $offer->getId() > 0; ?>
<div class="b-item-shopping js-remove-shopping">
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
                <span class="b-common-item__variant b-common-item__variant--shopping-cart b-common-item__variant--shopping">
                     <span class="b-common-item__name-value">Вес: </span>
                     <span><?= WordHelper::showWeight($basketItem->getWeight(), true) ?></span>
                </span>
                <?php if ($useOffer) {
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
            <?php if ($useOffer && $offer->getQuantity() > 0) {
                $bonus = $component->getItemBonus($offer);
                if ($bonus > 0) {
                    $bonus = floor($bonus); ?>
                    <span class="b-common-item__rank-text b-common-item__rank-text--red b-common-item__rank-text--shopping">+ <?= WordHelper::numberFormat($bonus,
                            0) ?>
                        <?= WordHelper::declension($bonus,
                            ['бонус', 'бонуса', 'бонусов']) ?> </span>
                <?php }
            } ?>
        </div>
    </div>
    <div class="b-item-shopping__operation<?= $offer->getQuantity() > 0 ? ' b-item-shopping__operation--not-available' : '' ?>">
        <?php $maxQuantity = 1000;
        if ($useOffer) {
            $maxQuantity = $offer->getQuantity();
        }
        if ($offer->getQuantity() > 0) { ?>
            <div class="b-plus-minus b-plus-minus--half-mobile b-plus-minus--shopping js-plus-minus-cont">
                <a class="b-plus-minus__minus js-minus" data-url="/ajax/sale/basket/update/"
                   href="javascript:void(0);"></a>

                <input title="" class="b-plus-minus__count js-plus-minus-count"
                       value="<?= WordHelper::numberFormat($basketItem->getQuantity(), 0) ?>"
                       data-one-price="<?= $basketItem->getPrice() ?>"
                       data-cont-max="<?= $maxQuantity ?>"
                       data-basketid="<?= $basketItem->getId(); ?>" type="text"/>

                <a class="b-plus-minus__plus js-plus" data-url="/ajax/sale/basket/update/"
                   href="javascript:void(0);"></a>
            </div>
            <div class="b-select b-select--shopping-cart">
                <?php /** @todo mobile max quantity */
                $maxMobileQuantity = 100;
                if ($maxQuantity < $maxMobileQuantity) {
                    $maxMobileQuantity = $maxMobileQuantity;
                } ?>
                <select title="" class="b-select__block b-select__block--shopping-cart"
                        name="shopping-cart">
                    <option value="" disabled="disabled" selected="selected">выберите</option>
                    <?php for ($i = 0; $i < $maxMobileQuantity; $i++) { ?>
                        <option value="one-click-<?= $i ?>"><?= $i + 1 ?></option>
                        <?php
                    } ?>
                </select>
            </div>
            <div class="b-price">
                <span class="b-price__current"><?= WordHelper::numberFormat($basketItem->getPrice()
                        * $basketItem->getQuantity()) ?>  </span>
                <span class="b-ruble">₽</span>
                <?php if ($basketItem->getDiscountPrice() > 0) { ?>
                    <span class="b-old-price b-old-price--crossed-out">
                        <span class="b-old-price__old"><?= WordHelper::numberFormat($basketItem->getBasePrice()
                                * $basketItem->getQuantity()) ?>  </span>
                        <span class="b-ruble b-ruble--old-weight-price">₽</span>
                    </span>
                <?php } ?>
            </div>
        <?php } else { ?>
            <div class="b-item-shopping__sale-info b-item-shopping__sale-info--width b-item-shopping__sale-info--not-available">
                Нет в наличии
            </div>
        <?php } ?>
        <a class="b-item-shopping__delete js-cart-delete-item" href="javascript:void(0);" title=""
           data-url="/ajax/sale/basket/delete/" data-basketId="<?= $basketItem->getId(); ?>">
            <span class="b-icon b-icon--delete b-icon--shopping">
                <?= new SvgDecorator('icon-delete-cart-product', 12, 14); ?>
            </span>
        </a>
        <?php if($offer->isByRequest() && !empty($arResult['OFFER_MIN_DELIVERY'][$basketItem->getProductId()])){
            /** @todo пока берем ближайшую доставку из быстрого заказа */?>
            <div class="b-item-shopping__sale-info b-item-shopping__sale-info--width">
                Предварительная дата доставки: <span><?=$arResult['OFFER_MIN_DELIVERY'][$basketItem->getProductId()]?></span>
            </div>
        <?}?>
    </div>
</div>