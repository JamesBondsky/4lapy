<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Application;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\WordHelper;
use FourPaws\UserBundle\Entity\User;

/** @global \FourPaws\Components\FourPawsFastOrderComponent $component */

$isAuth = $arResult['IS_AUTH'];
if ($isAuth) {
    /** @var User $curUser */
    $curUser = $arResult['CUR_USER'];
}

/** @var Basket $basket */
$basket = $arResult['BASKET'];
$orderableItems = $basket->getOrderableItems();
$request = Application::getInstance()->getContext()->getRequest();

$requestType = '';
if ($request->offsetExists('type')) {
    $requestType = $request->get('type');
}
if (!empty($arParams['REQUEST_TYPE'])) {
    $requestType = $arParams['REQUEST_TYPE'];
}
if (empty($type)) {
    $requestType = 'basket';
}

$name = '';
if ($isAuth) {
    $name = $curUser->getName();
}
if ($request->offsetExists('name')) {
    $name = $request->get('name');
}

$phone = '';
if ($isAuth) {
    $phone = $curUser->getPersonalPhone();
}
if ($request->offsetExists('phone')) {
    $phone = $request->get('phone');
}
?>
<div class="b-popup-one-click__close-bar">
    <a class="b-popup-one-click__close js-close-popup" href="javascript:void(0)" title="Закрыть"></a>
    <h1 class="b-title b-title--one-click b-title--one-click-head">Быстрый заказ</h1>
</div>
<form class="b-popup-one-click__form js-form-validation js-phone js-popup-buy-one-click"
      data-url="/ajax/sale/fast_order/create/" method="get">
    <input type="hidden" name="type" value="<?= $requestType ?>">
    <p class="b-popup-one-click__description">Укажите ваше имя и телефон, мы вам перезвоним, чтобы уточнить и
        оформить заказ</p>
    <div class="b-popup-one-click__input-block">
        <label class="b-popup-one-click__label" for="one-click-name">Имя</label>
        <div class="b-input b-input--recall">
            <input class="b-input__input-field b-input__input-field--recall js-small-input-two" type="text" id="one-click-name"
                   placeholder="Ваше имя" name="name" value="<?= $name ?>"/>
            <div class="b-error"><span class="js-message"></span></div>
        </div>
        <div class="b-error"><span class="js-message"></span></div>
    </div>
    <div class="b-popup-one-click__input-block">
        <label class="b-popup-one-click__label" for="one-click-tel">Телефон</label>
        <div class="b-input b-input--recall js-phone-mask">
            <input class="b-input__input-field b-input__input-field--recall js-phone-mask" type="tel"
                   id="one-click-tel" placeholder="Ваш телефон" name="phone"
                   value="<?= $phone ?>"/>
            <div class="b-error"><span class="js-message"></span></div>
        </div>
        <div class="b-error"><span class="js-message"></span></div>
    </div>
    <hr class="b-hr b-hr--one-click"/>
    <?php if (!$orderableItems->isEmpty()) {
        $userDiscount = $component->getCurrentUserService()->getDiscount();?>
        <h2 class="b-title b-title--one-click">Ваш заказ</h2>
        <hr class="b-hr b-hr--one-click2"/>
        <?php $countItems = $orderableItems->count();
        $i = 0;
        /** @var BasketItem $basketItem */
        foreach ($orderableItems as $basketItem) {
            $i++;
            $image = $component->getImage($basketItem->getProductId());
            $offer = $component->getOffer((int)$basketItem->getProductId());
            $useOffer = $offer instanceof Offer && $offer->getId() > 0; ?>
            <div class="b-item-shopping b-item-shopping--one-click <?= $countItems === $i ? ' b-item-shopping--last' : '' ?> js-remove-shopping">
                <?php /** @todo акция
                 * <div class="b-gift-order b-gift-order--shopping js-open-gift">
                 * <div class="b-gift-order__info">
                 * <span class="b-gift-order__text">
                 * Товар участвует в акции  <span class="b-icon b-icon--shopping-gift js-icon-shopping-gift">
                 * <?= new SvgDecorator('icon-arrow-down', 10, 6) ?>
                 * </span>
                 * <span class="b-gift-order__dash js-dash">- </span>
                 * <span class="b-gift-order__text-additional js-dropdown-gift">Собери 8 и получишь скидку</span>
                 * </span>
                 * </div>
                 * </div>
                 */ ?>
                <div class="b-common-item b-common-item--shopping-cart b-common-item--shopping b-common-item--one-click">
                <span class="b-common-item__image-wrap b-common-item__image-wrap--shopping-cart">
                    <img class="b-common-item__image b-common-item__image--shopping-cart"
                         src="<?= $image ?>"
                         alt="<?= $basketItem->getField('NAME') ?>"
                         title="<?= $basketItem->getField('NAME') ?>"/>
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
                        <?php if ($useOffer) {
                            $bonus = $offer->getBonusFormattedText($userDiscount, $basketItem->getQuantity());
                            if (!empty($bonus)) {?>
                                <span class="b-common-item__rank-text b-common-item__rank-text--red b-common-item__rank-text--shopping"><?=$bonus?></span>
                            <?php }
                        } ?>
                    </div>
                </div>
                <div class="b-item-shopping__operation b-item-shopping__operation--one-click">
                    <?php $maxQuantity = 0;
                    if ($useOffer) {
                        $maxQuantity = $offer->getQuantity();
                    } ?>
                    <div class="b-plus-minus b-plus-minus--half-mobile b-plus-minus--shopping js-plus-minus-cont js-no-valid">
                        <a class="b-plus-minus__minus js-minus" href="javascript:void(0);"
                           data-url="/ajax/sale/basket/update/"></a>
                        <input class="b-plus-minus__count js-plus-minus-count"
                               value="<?= WordHelper::numberFormat($basketItem->getQuantity(), 0) ?>"
                               data-cont-max="<?= $maxQuantity ?>"
                               data-one-price="<?= $basketItem->getPrice() ?>"
                               data-basketid="<?= $basketItem->getId(); ?>" type="text" title=""/>
                        <a class="b-plus-minus__plus js-plus" href="javascript:void(0);"
                           data-url="/ajax/sale/basket/update/"></a>
                    </div>
                    <div class="b-select b-select--shopping-cart js-no-valid">
                        <?php /** @todo mobile max quantity */
                        $maxMobileQuantity = 100;
                        if ($maxQuantity < $maxMobileQuantity) {
                            $maxMobileQuantity = $maxMobileQuantity;
                        } ?>
                        <select class="b-select__block b-select__block--shopping-cart js-no-valid" name="one-click"
                                title="">
                            <option value="" disabled="disabled" selected="selected">выберите</option>
                            <?php for ($i = 0; $i < $maxMobileQuantity; $i++) { ?>
                                <option value="one-click-<?= $i ?>"><?= $i + 1 ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="b-price">
                        <span class="b-price__current"><?= WordHelper::numberFormat($basketItem->getPrice() * $basketItem->getQuantity()) ?>  </span>
                        <span class="b-ruble">₽</span>
                        <?php
                        if ($basketItem->getDiscountPrice() > 0) { ?>
                            <span class="b-old-price b-old-price--crossed-out">
                            <span class="b-old-price__old"><?= WordHelper::numberFormat($basketItem->getBasePrice()
                                    * $basketItem->getQuantity()) ?>  </span>
                            <span class="b-ruble b-ruble--old-weight-price">₽</span>
                        </span>
                        <?php } ?>
                    </div>
                    <a class="b-item-shopping__delete js-cart-delete-item" href="javascript:void(0);" title=""
                       data-url="/ajax/sale/basket/delete/" data-basketId="<?= $basketItem->getId(); ?>">
                    <span class="b-icon b-icon--delete-one-click">
                        <?= new SvgDecorator('icon-delete-cart-product', 12, 14); ?>
                    </span>
                    </a>
                    <div class="b-item-shopping__sale-info">
                        <?php if ($basketItem->getDiscountPrice() > 0) { ?>
                            <span class="b-old-price b-old-price--inline b-old-price--crossed-out">
                            <span class="b-old-price__old"><?= WordHelper::numberFormat($basketItem->getBasePrice()) ?>  </span>
                            <span class="b-ruble b-ruble--old-weight-price">₽</span>
                        </span>
                        <?php } ?>
                        <span class="b-old-price b-old-price--inline">
                        <span class="b-old-price__old"><?= WordHelper::numberFormat($basketItem->getPrice()) ?> </span>
                        <span class="b-ruble b-ruble--old-weight-price">₽</span>
                    </span>
                        <span class="b-old-price b-old-price--inline b-old-price--on">
                        <span class="b-old-price__old"><?= WordHelper::numberFormat($basketItem->getQuantity(),
                                0) ?>  </span>
                        <span class="b-ruble b-ruble--old-weight-price">шт</span>
                    </span>
                        <?php /** @todo хз че это
                         * <a class="b-information-link js-popover-information-open js-popover-information-open"
                         * href="javascript:void(0);" title="">
                         * <span class="b-information-link__icon">i</span>
                         * <div class="b-popover-information js-popover-information">На Ваш телефон будет
                         * отправлено сообщение с информацией
                         * </div>
                         * </a>
                         */ ?>
                    </div>
                    <?php if ($useOffer) {
                        $deliveryDate = $component->getDeliveryDate($offer);
                        if (!empty($deliveryDate)) { ?>
                            <div class="b-item-shopping__sale-info b-item-shopping__sale-info--width">
                                Предварительная дата доставки:<span><?= $deliveryDate ?></span>
                            </div>
                        <?php }
                    } ?>
                </div>
            </div>
        <?php } ?>
        <hr class="b-hr b-hr--one-click3"/>
        <dl class="b-popup-one-click__result">
            <dt class="b-popup-one-click__result-dt">
                Итого <?= WordHelper::numberFormat($arResult['TOTAL_QUANTITY'], 0) ?> <?= WordHelper::declension($arResult['TOTAL_QUANTITY'],
                    ['товар', 'товара', 'товаров']) ?> (<?= WordHelper::showWeight($arResult['BASKET_WEIGHT'], true) ?>)
            </dt>
            <dd class="b-popup-one-click__result-dd"><?= WordHelper::numberFormat($arResult['TOTAL_PRICE']) ?> ₽</dd>
        </dl>
    <?php } ?>
    <div class="b-checkbox b-checkbox--one-click">
        <input class="b-checkbox__input" type="checkbox" name="confirm_user" id="one-click-personal"
               value="Я подтверждаю, что даю согласие на обработку персональных данных"/>
        <label class="b-checkbox__name b-checkbox__name--one-click" for="one-click-personal">
            <span class="b-checkbox__text">Я подтверждаю, что даю согласие на обработку персональных данных</span>
        </label>
    </div>
    <div class="b-error b-error--error">
        <span class="js-message"></span>
    </div>
    <button class="b-button b-button--one-click">Отправить</button>
</form>
<div class="b-preloader b-preloader--fixed">
    <div class="b-preloader__spinner">
        <img class="b-preloader__image" src="/static/build/images/inhtml/spinner.svg" alt="spinner" title=""/>
    </div>
</div>
