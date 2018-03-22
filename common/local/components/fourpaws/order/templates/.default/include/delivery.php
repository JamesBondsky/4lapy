<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array $arParams
 * @var array $arResult
 * @var OrderStorage $storage
 * @var \FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface $delivery
 */

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\PersonalBundle\Entity\Address;
use FourPaws\SaleBundle\Entity\OrderStorage;

$storage = $arResult['STORAGE'];

/** @var ArrayCollection $addresses */
$addresses = $arResult['ADDRESSES'];
$selectedAddressId = null;
$showNewAddressForm = false;
$showNewAddressFormHeader = false;

if (!$addresses || $addresses->isEmpty()) {
    $showNewAddressForm = true;
} else {
    if ($storage->getAddressId()) {
        $selectedAddressId = $storage->getAddressId();
    } elseif ($storage->getStreet()) {
        $showNewAddressForm = true;
        $showNewAddressFormHeader = true;
    } else {
        $selectedAddressId = $addresses->first()->getId();
    }
}

?>

<div class="b-input-line b-input-line--delivery-address-current js-hide-if-address"
    <?= $showNewAddressForm ? 'style="display: none"' : '' ?>>
    <div class="b-input-line__label-wrapper">
        <span class="b-input-line__label">Адрес доставки</span>
    </div>
    <?php /** @var Address $address */ ?>
    <?php foreach ($addresses as $address) {
        ?>
        <div class="b-radio b-radio--tablet-big">
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
        <?php
    } ?>
    <div class="b-radio b-radio--tablet-big">
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
    <div class="b-input-line b-input-line--street">
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
                   value="<?= $storage->getStreet() ?>"/>
            <div class="b-error"><span class="js-message"></span>
            </div>
        </div>
    </div>
    <div class="b-radio-tab__address-house">
        <div class="b-input-line b-input-line--house b-input-line--house-address js-small-input">
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
        <div class="b-input-line b-input-line--house">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="order-address-part">Корпус
                </label>
            </div>
            <div class="b-input b-input--registration-form">
                <input class="b-input__input-field b-input__input-field--registration-form js-housing js-no-valid"
                       id="order-address-part"
                       name="building"
                       type="text"
                       value="<?= $storage->getBuilding() ?>"/>
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
                <input class="b-input__input-field b-input__input-field--registration-form js-entrance js-no-valid"
                       id="order-address-entrance"
                       name="porch"
                       value="<?= $storage->getPorch() ?>"/>
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
                <input class="b-input__input-field b-input__input-field--registration-form js-floor js-no-valid"
                       id="order-address-floor"
                       name="floor"
                       type="text"
                       value="<?= $storage->getFloor() ?>"/>
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
                <input class="b-input__input-field b-input__input-field--registration-form js-office js-no-valid"
                       id="order-address-apart"
                       name="apartment"
                       type="text"
                       value="<?= $storage->getApartment() ?>"/>
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="b-input-line b-input-line--desired-date" data-url="<?= $arResult['URL']['DELIVERY_INTERVALS'] ?>">
    <div class="b-input-line__label-wrapper">
        <span class="b-input-line__label">Желаемая дата доставки</span>
    </div>
    <div class="b-select b-select--recall b-select--feedback-page">
        <select class="b-select__block b-select__block--recall b-select__block--feedback-page js-select-recovery js-change-date"
                name="deliveryDate">
            <option value="" disabled="disabled" selected="selected">выберите
            </option>
            <?php
            /** @noinspection PhpUnhandledExceptionInspection */
            $start = $delivery->getPeriodFrom();
            $end = $delivery->getPeriodTo();
            $time = time();
            $i = 0;
            for ($i = 0; $i < ($end - $start); $i++) {
                $date = (new DateTime())->modify('+' . ($start + $i) . ' days');
                $dateString = FormatDate('l, d.m.Y', $date->getTimestamp()); ?>
                <option value="<?= $i ?>" <?= ($storage->getDeliveryDate() === $i) ? 'selected="selected"' : '' ?>>
                    <?= $dateString ?>
                </option>
                <?php
            } ?>
        </select>
    </div>
</div>
<?php
if (!$delivery->getIntervals()->isEmpty()) {
    $availableIntervals = $delivery->getAvailableIntervals($storage->getDeliveryDate());
    ?>
    <div class="b-input-line b-input-line--interval">
        <div class="b-input-line__label-wrapper b-input-line__label-wrapper--interval">
            <span class="b-input-line__label">интервал</span>
        </div>
        <div class="b-select b-select--recall b-select--feedback-page b-select--interval">
            <select class="b-select__block b-select__block--recall b-select__block--feedback-page b-select__block--interval js-select-recovery"
                    name="deliveryInterval">
                <option value="" disabled="disabled" selected="selected">
                    выберите
                </option>
                <?php
                /** @var Interval $interval */
                foreach ($availableIntervals as $i => $interval) {
                    ?>
                    <option value="<?= $i + 1 ?>" <?= ($storage->getDeliveryInterval() === $i + 1) ? 'selected="selected"' : '' ?>>
                        <?= (string)$interval ?>
                    </option>
                    <?php
                } ?>
            </select>
        </div>
    </div>
    <?php
} ?>
<div class="b-input-line b-input-line--textarea b-input-line--address-textarea js-no-valid">
    <div class="b-input-line__label-wrapper">
        <label class="b-input-line__label" for="order-comment">
            Комментарий к заказу
        </label>
    </div>
    <div class="b-input b-input--registration-form">
        <textarea class="b-input__input-field b-input__input-field--textarea b-input__input-field--registration-form"
                  id="order-comment"
                  name="comment"><?= $storage->getComment() ?></textarea>
        <div class="b-error">
            <span class="js-message"></span>
        </div>
    </div>
</div>
