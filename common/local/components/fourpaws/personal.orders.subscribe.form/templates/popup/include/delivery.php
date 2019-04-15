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
use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;
use FourPaws\PersonalBundle\Entity\Address;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\LocationBundle\LocationService;

$storage = $arResult['STORAGE'];
$deliveryService = $component->getDeliveryService();
/** @var ArrayCollection $addresses */
$addresses = $arResult['ADDRESSES'];
$selectedAddressId = 0;
$showNewAddressForm = false;
$showNewAddressFormHeader = false;

if ($addresses && $addresses->isEmpty()) {
    $selectedAddressId = $addresses->first()->getId();
    $showNewAddressFormHeader = true;
} else {
    $address = (new Address());
}

$orderPrice = $delivery->getStockResult()->getOrderable()->getPrice();
$nextDeliveries = $component->getDeliveryService()->getNextDeliveries($delivery, 10);
?>
<script>
    window.dadataConstraintsLocations = <?= $arResult['DADATA_CONSTRAINTS'] ?>;
</script>
<div class="b-input-line b-input-line--delivery-address-current js-hide-if-address <?= $showNewAddressForm ? 'hide' : '' ?>"
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
                <?= ($selectedAddressId && $selectedAddressId === $address->getId()) ? 'checked="checked"' : '' ?>
                   value="<?= $address->getId() ?>"/>
            <label class="b-radio__label b-radio__label--tablet-big"
                   for="order-address-<?= $address->getId() ?>">
                <span class="b-radio__text-label">
                    <?= $address->getFullAddress() ?>
                </span>
            </label>
        </div>
        <?php
    } ?>
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
</div>
<div class="b-radio-tab__new-address js-form-new-address js-hidden-valid-fields active" <?= $showNewAddressForm ? 'style="display:block"' : '' ?>>
    <div class="b-input-line b-input-line--new-address">
        <div class="b-input-line__label-wrapper b-input-line__label-wrapper--back-arrow">
            <?php if ($showNewAddressFormHeader) {
                ?>
                <span class="b-input-line__label">Новый адрес доставки</span>
                <a class="b-link b-link--back-arrow js-back-list-address"
                   href="javascript:void(0);"
                   title="Назад">
                <span class="b-icon b-icon--back-long">
                    <?= new SvgDecorator('icon-back-form', 13, 11) ?>
                </span>
                    <span class="b-link__back-word">Вернуться </span>
                    <span class="b-link__mobile-word">к списку</span>
                </a>
                <?php
            } ?>
        </div>
    </div>
    <div class="b-input-line b-input-line--street js-order-address-street">
        <div class="b-input-line__label-wrapper">
            <label class="b-input-line__label" for="order-address-street">Улица
            </label><span class="b-input-line__require">(обязательно)</span>
        </div>
        <div class="b-input b-input--registration-form">
            <input class="b-input__input-field b-input__input-field--registration-form"
                   type="text"
                   id="order-address-street"
                   placeholder=""
                   name="street"
                   data-url=""
                   value=""/>
            <div class="b-error"><span class="js-message"></span>
            </div>
        </div>
    </div>
    <div class="b-radio-tab__address-house">
        <div class="b-input-line b-input-line--house b-input-line--house-address js-small-input js-only-number js-order-address-house">
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
                       value=""/>
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
        </div>
        <div class="b-input-line b-input-line--house">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="order-address-part">Корпус
                </label>
            </div>
            <div class="b-input b-input--registration-form">
                <input class="b-input__input-field b-input__input-field--registration-form js-regular-field js-only-number js-housing js-no-valid"
                       id="order-address-part"
                       name="building"
                       type="text"
                       value=""/>
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
        </div>
        <div class="b-input-line b-input-line--house">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="order-address-entrance">Подъезд
                </label>
            </div>
            <div class="b-input b-input--registration-form">
                <input class="b-input__input-field b-input__input-field--registration-form js-regular-field js-only-number js-entrance js-no-valid"
                       id="order-address-entrance"
                       name="porch"
                       value=""/>
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
        </div>
        <div class="b-input-line b-input-line--house">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="order-address-floor">Этаж
                </label>
            </div>
            <div class="b-input b-input--registration-form">
                <input class="b-input__input-field b-input__input-field--registration-form js-regular-field js-only-number js-floor js-no-valid"
                       id="order-address-floor"
                       name="floor"
                       type="text"
                       value=""/>
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
        </div>
        <div class="b-input-line b-input-line--house">
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
                       value=""/>
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
        </div>
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
<div class="b-delivery-type-time" data-container-delivery-type-time="true">
    <ul class="b-radio-tab">
        <li class="b-radio-tab__tab b-radio-tab__tab--default-dostavista" data-content-type-time-delivery="default">
            <div class="delivery-block__type visible" data-delivery="<?= $delivery->getPrice() ?>" data-full="<?= $orderPrice ?>" data-type="oneDelivery">
                <div class="b-input-line b-input-line--desired-date" data-url="<?= $arResult['URL']['DELIVERY_INTERVALS'] ?>">
                    <div class="b-input-line__label-wrapper">
                        <span class="b-input-line__label">Желаемая дата первой доставки</span>
                    </div>
                    <div class="b-select b-select--recall b-select--feedback-page">
                        <?php
                        $selectorDelivery = $delivery;
                        $selectorStorage = $storage;
                        $selectorName = 'deliveryDate';
                        include 'delivery_date_select.php'
                        ?>
                    </div>
                </div>
                <?php if (!$delivery->getIntervals()->isEmpty()) {
                    $selectorDelivery = $delivery;
                    $selectorStorage = $storage;
                    $selectorName = 'deliveryInterval';
                    include 'delivery_interval_select.php';
                } ?>
            </div>
        </li>
    </ul>
</div>