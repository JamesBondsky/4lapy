<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array $arParams
 * @var array $arResult
 * @var OrderStorage $storage
 * @var DeliveryResultInterface $delivery
 * @var FourPawsOrderComponent $component
 */

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResultInterface;
use FourPaws\PersonalBundle\Entity\Address;
use FourPaws\SaleBundle\Entity\OrderStorage;

$storage = $arResult['STORAGE'];
/** @var ArrayCollection $addresses */
$addresses = $arResult['ADDRESSES'];
$selectedAddressId = 0;
$showNewAddressForm = false;
$showNewAddressFormHeader = false;

if (!$addresses || $addresses->isEmpty()) {
    $showNewAddressForm = true;
    $selectedAddressId = 0;
    $storage->setAddressId(0);
} else if ($storage->getAddressId()) {
    $selectedAddressId = $storage->getAddressId();
} else if ($storage->getStreet()) {
    $showNewAddressForm = true;
} else {
    $selectedAddressId = $addresses->first()->getId();
}

if ($storage->getUserId() && !$addresses->isEmpty()) {
    $showNewAddressFormHeader = true;
}

?>
<script>
    window.dadataConstraintsLocations = <?= $arResult['DADATA_CONSTRAINTS'] ?>;
</script>

<div class="b-input-line b-input-line--delivery-address-current js-hide-if-address <?= $showNewAddressForm ? 'hide js-no-valid' : '' ?>"
    <?= $showNewAddressForm ? 'style="display: none"' : '' ?>>
    <div class="b-input-line__label-wrapper">
        <span class="b-input-line__label">Адрес доставки</span>
    </div>
    <?php /** @var Address $address */ ?>
    <?php foreach ($addresses as $address) {
        ?>
        <div class="b-radio b-radio--tablet-big js-item-saved-delivery-address">
            <input class="b-radio__input"
                   type="radio"
                   name="addressId"
                   id="order-address-<?= $address->getId() ?>"
                <?= $selectedAddressId === $address->getId() ? 'checked="checked"' : '' ?>
                   value="<?= $address->getId() ?>"/>
            <label class="b-radio__label b-radio__label--tablet-big"
                   for="order-address-<?= $address->getId() ?>">
                    <span class="b-radio__text-label">
                        <?= $address->getFullAddress() ?>
                    </span>
            </label>
        </div>
    <?php } ?>
    <div class="b-radio b-radio--tablet-big js-item-saved-delivery-address">
        <input class="b-radio__input"
               type="radio"
               name="addressId"
               id="order-address-another"
               data-radio="4"
            <?= $selectedAddressId === 0 ? 'checked="checked"' : '' ?>
               value="0">
        <label class="b-radio__label b-radio__label--tablet-big js-order-address-another"
               for="order-address-another">
            <span class="b-radio__text-label">Доставить по другому адресу…</span>
        </label>
    </div>
    <div class="b-delivery-type-time__warning b-delivery-type-time__warning-hidden js-dostavista-address-error">
        <div class="b-delivery-type-time__warning-title b-delivery-type-time__warning-title--detail">
            Экспресс-доставка по выбранному адресу невозможна,
        </div>
        <div class="b-delivery-type-time__warning-title b-delivery-type-time__warning-title--detail">
            выберите другой адрес доставки внутри МКАД или воспользуйтесь самовывозом
        </div>
    </div>
</div>
<div class="b-radio-tab__new-address js-form-new-address js-hidden-valid-fields active" <?= $showNewAddressForm ? 'style="display:block"' : '' ?>>
    <div class="b-input-line b-input-line--new-address">
        <div class="b-input-line__label-wrapper b-input-line__label-wrapper--back-arrow">
            <?php if ($showNewAddressFormHeader) {
                ?>
                <span class="b-input-line__label">Адрес доставки внутри МКАД</span>
                <a class="b-link b-link--back-arrow js-back-list-address"
                   href="javascript:void(0);"
                   title="Назад">
                    <span class="b-icon b-icon--back-long">
                        <?= new SvgDecorator('icon-back-form', 13, 11) ?>
                    </span>
                    <span class="b-link__back-word">Вернуться </span>
                    <span class="b-link__mobile-word">к списку</span>
                </a>
            <?php } ?>
        </div>
    </div>
    <div class="b-input-line b-input-line--street js-order-address-street">
        <div class="b-input-line__label-wrapper">
            <label class="b-input-line__label" for="order-address-street">Улица
            </label><span class="b-input-line__require">(обязательно)</span>
        </div>
        <div class="b-input b-input--registration-form">
            <input class="b-input__input-field b-input__input-field--registration-form<?php if ($storage->getStreet()) { ?> ok suggestions-input<?php } ?>"
                   type="text"
                   id="order-address-street"
                   placeholder=""
                   name="street"
                   data-url=""
                   data-streetv="1"
                   data-errormsg="Не меньше 3 символов без пробелов"
                   value="<?= $storage->getStreet() ?>"
                   <?php if ($storage->getStreet()){ ?>data-street="<?= str_replace(['ул ', 'пер ', 'пр-кт ', 'кв-л ', 'б-р ', ' наб', 'наб '], '', $storage->getStreet()) ?>"<?php } ?>/>
            <div class="b-error"><span class="js-message"></span>
            </div>
        </div>
    </div>
    <div class="b-radio-tab__address-house">
        <div class="b-input--dostavista-form b-input-line--house b-input-line--house-address js-small-input js-only-number js-order-address-house">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="order-address-house">Дом
                </label><span class="b-input-line__require">(обязательно)</span>
            </div>
            <div class="b-input b-input--registration-form">
                <input class="b-input__input-field b-input__input-field--registration-form"
                       type="text"
                       id="order-address-house"
                       placeholder=""
                       name="house"
                       data-url=""
                       value="<?= $storage->getHouse() ?>"/>
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
        </div>
        <div class="b-input--dostavista-form b-input-line--house">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="order-address-part">Корпус
                </label>
            </div>
            <div class="b-input b-input--registration-form">
                <input class="b-input__input-field b-input__input-field--registration-form js-regular-field js-only-number js-housing js-no-valid"
                       id="order-address-part"
                       name="building"
                       type="text"
                       value="<?= $storage->getBuilding() ?>"/>
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
        </div>
        <div class="b-input--dostavista-form b-input-line--house">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="order-address-entrance">Подъезд
                </label>
            </div>
            <div class="b-input b-input--registration-form">
                <input class="b-input__input-field b-input__input-field--registration-form js-regular-field js-only-number js-entrance js-no-valid"
                       id="order-address-entrance"
                       name="porch"
                       value="<?= $storage->getPorch() ?>"/>
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
        </div>
        <div class="b-input--dostavista-form b-input-line--house">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="order-address-floor">Этаж
                </label>
            </div>
            <div class="b-input b-input--registration-form">
                <input class="b-input__input-field b-input__input-field--registration-form js-regular-field js-only-number js-floor js-no-valid"
                       id="order-address-floor"
                       name="floor"
                       type="text"
                       value="<?= $storage->getFloor() ?>"/>
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
        </div>
        <div class="b-input--dostavista-form b-input-line--house">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="order-address-apart">Кв.,
                    офис
                </label>
            </div>
            <div class="b-input b-input--registration-form">
                <input class="b-input__input-field b-input__input-field--registration-form js-regular-field js-only-number js-office js-no-valid"
                       id="order-address-apart"
                       name="apartment"
                       type="text"
                       value="<?= $storage->getApartment() ?>"/>
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
        </div>
    </div>
    <div class="b-input--dostavista-warning b-input--dostavista-warning-hidden js-dostavista-address-warning">
        <span>
            Доставка из магазина  возможна только внутри МКАД, воспользуйтесь самовывозом
        </span>
    </div>
    <div class="b-input-line b-radio-tab__address-map js-courierdelivery-map hidden">
        <div class="b-radio-tab-map b-radio-tab-map--order">
            <div class="b-radio-tab-map__label-wrapper">
                <a href="javascript:void(0);" class="b-radio-tab-map__label js-toogle-courierdelivery-map">
                    <span class="b-radio-tab-map__label-inner">
                        Место доставки на карте
                    </span>
                    <span class="b-icon b-icon--map">
                        <?= new SvgDecorator('icon-arrow-down', 10, 12) ?>
                    </span>
                </a>
            </div>
            <div class="b-radio-tab-map__map-wrapper">
                <div class="b-radio-tab-map__map" id="map_courier_delivery"></div>
            </div>
        </div>
    </div>
</div>

<div class="js-dostavista-available b-choice-recovery-hidden">
    <?php if ($deliveryDostavista) { // для достависты ?>
        <div style="display: none;" data-msg-order-delivery-address="dostavista">
            <div class="b-delivery-type-time__info js-info-express-detail" style="display: block;">
                <div class="b-delivery-type-time__info-title <?= ($deliveryDostavista->getData()['TEXT_EXPRESS_DETAIL']) ? 'b-delivery-type-time__info-title--detail' : '' ?>">
                    <?= str_replace(['[time]', '[price]'], [round($deliveryDostavista->getPeriodTo() / 60), ($deliveryDostavista->getPrice() > 0) ? 'за ' . $deliveryDostavista->getPrice() . ' ₽' : 'бесплатно'], $deliveryDostavista->getData()['TEXT_EXPRESS_DELIVERY']); ?>
                </div>
                <?php if ($deliveryDostavista->getData()['TEXT_EXPRESS_DETAIL']) { ?>
                    <div class="b-delivery-type-time__info-detail js-content-info-express-detail">
                        <?= $deliveryDostavista->getData()['TEXT_EXPRESS_DETAIL']; ?>
                    </div>
                    <div class="b-delivery-type-time__info-toggle js-btn-toggle-info-express-detail"></div>
                <?php } ?>
            </div>
            <div class="b-choice-recovery b-choice-recovery--order-step b-choice-recovery--delivery-type-time">
                <input class="b-choice-recovery__input" id="order-express-courier-delivery-stub" type="radio">
                <label class="b-choice-recovery__label b-choice-recovery__label--left b-choice-recovery__label--order-step b-choice-recovery-stub" for="order-express-courier-delivery-stub" style="width: 100%;">
                    <span class="b-choice-recovery__main-text">
                        <span class="b-choice-recovery__main-text">Экспресс</span>
                    </span>
                    <span class="b-choice-recovery__addition-text js-dostavista-addition-text">
                        В&nbsp;течение <?= round($deliveryDostavista->getPeriodTo() / 60) ?>&nbsp;часов, <?= $deliveryDostavista->getPrice() ?>&nbsp;₽
                    </span>
                    <span class="b-choice-recovery__addition-text b-choice-recovery__addition-text--mobile js-dostavista-addition-text">
                        В&nbsp;течение <?= round($deliveryDostavista->getPeriodTo() / 60) ?>&nbsp;часов, <?= $deliveryDostavista->getPrice() ?>&nbsp;₽
                    </span>
                </label>
            </div>
        </div>
    <?php } ?>

    <?php if ($expressDelivery) { // для экспресс доставки ?>
        <div style="display: block;" data-msg-order-delivery-address="express">
            <div class="b-delivery-type-time__info js-info-express-detail" style="display: block;">
                Вам доступна Экспресс-доставка в течение <span data-time-order-delivery-address="express">90</span>&nbsp;минут за <?= $expressDelivery->getPrice() ?>&nbsp;₽
            </div>
            <div class="b-choice-recovery b-choice-recovery--order-step b-choice-recovery--delivery-type-time">
                <input class="b-choice-recovery__input" id="order-express-courier-delivery-stub" type="radio">
                <label class="b-choice-recovery__label b-choice-recovery__label--left b-choice-recovery__label--order-step b-choice-recovery-stub" for="order-express-courier-delivery-stub" style="width: 100%;">
                    <span class="b-choice-recovery__main-text">
                        <span class="b-choice-recovery__main-text">Экспресс</span>
                    </span>
                    <span class="b-choice-recovery__addition-text js-express-delivery-addition-text">
                        В&nbsp;течение <span data-time-order-delivery-address="express">90</span>&nbsp;минут, <?= $expressDelivery->getPrice() ?>&nbsp;₽
                    </span>
                    <span class="b-choice-recovery__addition-text b-choice-recovery__addition-text--mobile js-express-delivery-addition-text">
                        В&nbsp;течение <span data-time-order-delivery-address="express">90</span>&nbsp;минут, <?= $expressDelivery->getPrice() ?>&nbsp;₽
                    </span>
                </label>
            </div>
        </div>
    <?php } ?>

    <div class="b-delivery-type-time">
        <ul class="b-radio-tab">
            <li class="b-radio-tab__tab">
                <div class="b-input-line">
                    <div class="b-input-line__label-wrapper">
                        <span class="b-input-line__label">Время доставки</span>
                    </div>
                    <?php if ($deliveryDostavista) { // достависта ?>
                        <div class="b-input b-input--registration-form b-input--time-express-delivery" data-input-time-express-delivery="dostavista" style="display: none;">
                            <input class="b-input__input-field b-input__input-field--time-express-delivery js-no-valid"
                                   id="time-express-delivery"
                                   name="express_time_delivery"
                                   disabled="disabled"
                                   value="Сегодня, <?= (new DateTime())->format('d.m.Y') ?> — в течение <?= round($deliveryDostavista->getPeriodTo() / 60) ?> часов с момента заказа"/>
                            <div class="b-error">
                                <span class="js-message"></span>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if ($expressDelivery) { // экспресс доставка ?>
                        <div class="b-input b-input--registration-form b-input--time-express-delivery"
                             data-input-time-express-delivery="express"
                             data-start-text-input-time-express-delivery="Сегодня, <?= (new DateTime())->format('d.m.Y') ?> — в течение"
                             data-finish-text-input-time-express-delivery="минут с момента заказа">
                            <input class="b-input__input-field b-input__input-field--time-express-delivery js-no-valid"
                                   id="time-express-delivery"
                                   name="express_time_delivery"
                                   disabled="disabled"
                                   value="Сегодня, <?= (new DateTime())->format('d.m.Y') ?> — в течение 90 минут с момента заказа"/>
                            <div class="b-error">
                                <span class="js-message"></span>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <div class="b-input-line b-input-line--textarea b-input-line--address-textarea js-no-valid">
                    <div class="b-input-line__label-wrapper">
                        <label class="b-input-line__label" for="order-comment">
                            Комментарий к заказу
                        </label>
                    </div>
                    <div class="b-input b-input--registration-form">
                        <textarea class="b-input__input-field b-input__input-field--textarea b-input__input-field--registration-form b-input__input-field--step2-order"
                                  id="comment-express-delivery"
                                  name="comment_dostavista"
                                  placeholder="Укажите здесь ваши комментарии.
Например, если для доставки необходим въезд на закрытую территорию. Курьер свяжется с Вами для оформления пропуска.
При отсутствии пропуска – доставка будет осуществляться до КПП/шлагбаума."><?= $storage->getComment() ?></textarea>
                        <div class="b-error">
                            <span class="js-message"></span>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</div>
