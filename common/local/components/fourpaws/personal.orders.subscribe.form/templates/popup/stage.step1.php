<?php

use Bitrix\Sale\BasketItem;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\DateHelper;
use FourPaws\Helpers\WordHelper;
use FourPaws\PersonalBundle\Entity\Order;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;

global $APPLICATION;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var FourPawsPersonalCabinetOrdersSubscribeFormComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 * @var BasketItem $basketItem
 * @var OfferCollection $offers
 * @var Offer $offer
 */
?>

<div class="b-popup-subscribe-delivery__inner">
    <div class="b-tab-list">
        <ul class="b-tab-list__list js-scroll-tabs-subscribe-delivery">
            <li class="b-tab-list__item js-step-subscribe-delivery active">
                <span class="b-tab-list__step">Шаг </span>1. Товары в подписке
            </li>
            <li class="b-tab-list__item js-step-subscribe-delivery">
                <span class="b-tab-list__step">Шаг </span>2. Доставка и оплата
            </li>
        </ul>
    </div>
    <div class="b-product-subscribe-delivery">
        <div class="b-product-subscribe-delivery__list">

            <? foreach ($arResult['BASKET'] as $id => $basketItem) {
                $itemId = $id+1;
                $offer = $component->getOffer((int)$basketItem->getProductId());
                $useOffer = $offer instanceof Offer && $offer->getId() > 0;
                $image = $component->getImage((int)$offer->getId());
                ?>
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
                                        <?php if ($useOffer) { ?>
                                            <span class="span-strong"><?= $offer->getProduct()->getBrandName() ?>  </span>
                                        <?php } ?>
                                        <?= $basketItem->getField('NAME') ?>
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
                            <? // режем бонусы из-за сложной логики
                            // <span class="b-common-item__rank-text b-common-item__rank-text--red b-common-item__rank-text--shopping js-bonus-42462">+ 72 бонуса</span>
                            ?>
                        </div>
                    </div>

                    <div class="b-item-shopping__operation b-item-shopping__operation<?= $offer->getQuantity() > 0 ? ' b-item-shopping__operation--not-available' : '' ?>">
                        <?php
                        $maxQuantity = 1000;
                        if ($useOffer) {
                            $maxQuantity = $offer->getQuantity();
                        }

                        if ($offer->getQuantity() > 0) { ?>
                            <div class="b-plus-minus b-plus-minus--half-mobile b-plus-minus--shopping js-plus-minus-cont">
                                <a class="b-plus-minus__minus js-minus"
                                   href="javascript:void(0);"></a>
                                <?php
                                /** @todo data-one-price */
                                ?>
                                <input title="" class="b-plus-minus__count js-plus-minus-count"
                                       value="<?= WordHelper::numberFormat($basketItem->getQuantity(),
                                           0) ?>"
                                       data-one-price="<?= $basketItem->getPrice() ?>"
                                       data-cont-max="<?= $maxQuantity ?>"
                                       data-basketid="<?= $itemId; ?>"
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
                                <span class="b-price__current">
                                    <?= WordHelper::numberFormat($basketItem->getPrice() * $basketItem->getQuantity()) ?>
                                </span>
                                <span class="b-ruble">₽</span>
                                <?php
                                //сюда же влетает округление до копеек при пересчете НДС, поэтому 0,01
                                if ($basketItem->getDiscountPrice() >= 0.01) {
                                    ?>
                                    <span class="b-old-price b-old-price--crossed-out">
                                        <span class="b-old-price__old">
                                            <?= WordHelper::numberFormat($basketItem->getBasePrice() * $basketItem->getQuantity()) ?>
                                        </span>
                                        <span class="b-ruble b-ruble--old-weight-price">₽</span>
                                    </span>
                                    <?php
                                }
                                ?>
                            </div>
                            <a class="b-item-shopping__delete js-cart-delete-item" href="javascript:void(0);"
                               title=""
                               data-basketId="<?= $itemId; ?>">
                                <span class="b-icon b-icon--delete b-icon--shopping">
                                    <?= new SvgDecorator('icon-delete-cart-product', 12, 14); ?>
                                </span>
                            </a>
                        <?php } else { ?>
                            <div class="b-item-shopping__sale-info b-item-shopping__sale-info--width b-item-shopping__sale-info--not-available">
                                Нет в наличии
                            </div>
                        <?php } ?>

                        <?php if (!($offer->getQuantity() > 0)) { ?>
                            <a class="b-item-shopping__delete js-cart-delete-item" href="javascript:void(0);"
                               title=""
                               data-url="<?= $basketDeleteUrl ?>" data-basketId="<?= $itemId; ?>">
                                <span class="b-icon b-icon--delete b-icon--shopping">
                                    <?= new SvgDecorator('icon-delete-cart-product', 12, 14); ?>
                                </span>
                            </a>
                        <?php } ?>

                        <?php
                        if ($isDiscounted || $basketItem->getQuantity() > 1) {
                            ?>
                            <div class="b-item-shopping__sale-info">
                                <?php
                                if ((float)$basketItem->getBasePrice() - (float)$basketItem->getPrice() >= 0.01) {
                                    ?>
                                    <span class="b-old-price b-old-price--inline b-old-price--crossed-out">
                                    <span class="b-old-price__old"><?= WordHelper::numberFormat($basketItem->getBasePrice()) ?> </span>
                                    <span class="b-ruble b-ruble--old-weight-price">₽</span>
                                </span>
                                    <?php
                                }
                                ?>
                                <span class="b-old-price b-old-price--inline">
                                <span class="b-old-price__old"><?= WordHelper::numberFormat($basketItem->getPrice()) ?> </span>
                                <span class="b-ruble b-ruble--old-weight-price">₽</span>
                            </span>
                                <span class="b-old-price b-old-price--inline b-old-price--on">
                                <span class="b-old-price__old"><?= $basketItem->getQuantity() ?> </span>
                                <span class="b-ruble b-ruble--old-weight-price">шт</span>
                            </span>
                                <?php
                                // информацию о скидки мы не можем получить
//                                if ($basketItem->getBasePrice() !== $basketItem->getPrice()) {
//                                    $informationText = 'Товар со скидкой';
//                                    $descriptions = $component->getPromoLink($basketItem, true);
//                                    if (\count($descriptions) === 1) {
//                                        $informationText = 'Скидка: <br>';
//                                    } elseif (\count($descriptions) > 1) {
//                                        $informationText = 'Скидки: <br>';
//                                    }
//                                    foreach ($descriptions as $description) {
//                                        $informationText .= $description['name'] . '<br>';
//                                    }
//                                    ?>
<!--                                            <a class="b-information-link js-popover-information-open js-popover-information-open"-->
<!--                                               href="javascript:void(0);" title="">-->
<!--                                                <span class="b-information-link__icon">i</span>-->
<!--                                                <div class="b-popover-information js-popover-information">-->
<!--                                                    --><?//= $informationText; ?>
<!--                                                </div>-->
<!--                                            </a>-->
<!--                                        --><?php //} ?>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            <? } ?>

            <div class="b-product-subscribe-delivery__add" data-open-catalog-in-popup="true">
                <div class="add-product-subscribe js-open-catalog-subscribe"
                     data-popup-id="catalog-subscribe-delivery">
                    <div class="add-product-subscribe__plus"></div>
                    <div class="add-product-subscribe__info">
                        <div class="add-product-subscribe__title">
                            Добавить товар
                        </div>
                        <div class="add-product-subscribe__descr">
                            При добавлении нового товара и&nbsp;изменении количества товаров будет необходимо
                            обновить параметры доставки
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="b-popup-subscribe-delivery__btns">
        <a href="javascript:void(0);" class="b-button b-button--back-subscribe-delivery" title="Назад">
            Назад
        </a>
        <a href="javascript:void(0);" class="b-button b-button--next-subscribe-delivery" title="Далее">
            Далее
        </a>
        <a href="javascript:void(0);" class="b-button b-button--cancel-subscribe-delivery js-close-popup"
           title="Отменить">
            Отменить
        </a>
    </div>
</div>
