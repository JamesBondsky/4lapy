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

if (!$addresses || $addresses->isEmpty()) {
    $showNewAddressForm = true;
    $selectedAddressId = 0;
    $storage->setAddressId(0);
} else {
    if ($storage->getAddressId()) {
        $selectedAddressId = $storage->getAddressId();
    } elseif ($storage->getStreet()) {
        $showNewAddressForm = true;
    } else {
        $selectedAddressId = $addresses->first()->getId();
    }
}

if ($storage->getUserId() && !$addresses->isEmpty()) {
    $showNewAddressFormHeader = true;
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
                       value="<?= $storage->getStreet() ?>"/>
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
                    <input class="b-input__input-field b-input__input-field--registration-form js-regular-field js-only-number js-housing js-no-valid"
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
                    <input class="b-input__input-field b-input__input-field--registration-form js-regular-field js-only-number js-entrance js-no-valid"
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
                    <input class="b-input__input-field b-input__input-field--registration-form js-regular-field js-only-number js-floor js-no-valid"
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
        <? if ($deliveryDostavista) { ?>
            <div class="b-delivery-type-time__info">
                <?= str_replace(['[time]', '[price]'], [round($deliveryDostavista->getPeriodTo() / 60), ($deliveryDostavista->getPrice() > 0) ? 'за ' . $deliveryDostavista->getPrice() . ' ₽' : 'бесплатно'], $deliveryDostavista->getData()['TEXT_EXPRESS_DELIVERY']); ?>
            </div>
            <div class="b-delivery-type-time__info_detail">
                <?= $deliveryDostavista->getData()['TEXT_EXPRESS_DETAIL']; ?>
            </div>
            <div class="b-choice-recovery b-choice-recovery--order-step b-choice-recovery--delivery-type-time">
                <input data-set-delivery-type="<?= $deliveryDostavista->getDeliveryId() ?>"
                       class="b-choice-recovery__input"
                       id="order-express-courier-delivery"
                       type="radio"
                       name="typeTimeDeliveryId"
                       data-delivery="<?= $deliveryDostavista->getPrice() ?>"
                       data-full="<?= $delivery->getStockResult()->getOrderable()->getPrice() ?>"
                       data-type-time-delivery="express">
                <label class="b-choice-recovery__label b-choice-recovery__label--left b-choice-recovery__label--order-step" for="order-express-courier-delivery">
                <span class="b-choice-recovery__main-text">
                    <span class="b-choice-recovery__main-text">Экспресс</span>
                </span>
                    <span class="b-choice-recovery__addition-text">
                    В&nbsp;течении <?= round($deliveryDostavista->getPeriodTo() / 60) ?>&nbsp;часов, <?= $deliveryDostavista->getPrice() ?>&nbsp;₽
                </span>
                    <span class="b-choice-recovery__addition-text b-choice-recovery__addition-text--mobile">
                    В&nbsp;течении <?= round($deliveryDostavista->getPeriodTo() / 60) ?>&nbsp;часов, <?= $deliveryDostavista->getPrice() ?>&nbsp;₽
                </span>
                </label>
                <input checked="checked"
                       class="b-choice-recovery__input"
                       data-set-delivery-type="<?= $delivery->getDeliveryId() ?>"
                       id="order-default-courier-delivery"
                       type="radio"
                       name="typeTimeDeliveryId"
                       data-delivery="<?= $delivery->getPrice() ?>"
                       data-full="<?= $delivery->getStockResult()->getOrderable()->getPrice() ?>"
                       data-type-time-delivery="default">
                <label class="b-choice-recovery__label b-choice-recovery__label--right b-choice-recovery__label--order-step" for="order-default-courier-delivery">
                    <span class="b-choice-recovery__main-text">Обычная</span>
                    <span class="b-choice-recovery__addition-text js-cur-pickup">
                    <?= /** @noinspection PhpUnhandledExceptionInspection */
                    DeliveryTimeHelper::showTime($delivery) ?>,
                    <span class="js-delivery--price"><?= $delivery->getPrice() ?></span>₽
                </span>
                    <span class="b-choice-recovery__addition-text b-choice-recovery__addition-text--mobile js-cur-pickup-mobile">
                    <?= /** @noinspection PhpUnhandledExceptionInspection */
                    DeliveryTimeHelper::showTime($delivery, ['SHORT' => true]) ?>,
                    <span class="js-delivery--price"><?= $delivery->getPrice() ?></span>₽
                </span>
                </label>
            </div>
        <? } ?>
        <ul class="b-radio-tab">
            <? if ($deliveryDostavista) { ?>
                <li class="b-radio-tab__tab b-radio-tab__tab--express-dostavista" data-content-type-time-delivery="express">
                    <div class="b-input-line">
                        <div class="b-input-line__label-wrapper">
                            <span class="b-input-line__label">Время доставки</span>
                        </div>
                        <div class="b-input b-input--registration-form b-input--time-express-delivery">
                            <input class="b-input__input-field b-input__input-field--time-express-delivery js-no-valid"
                                   id="time-express-delivery"
                                   name="express_time_delivery"
                                   disabled="disabled"
                                   value="Сегодня, <?= (new DateTime())->format('d.m.Y') ?> — в течении <?= round($deliveryDostavista->getPeriodTo() / 60) ?> часов с момента заказа"
                            />
                            <div class="b-error">
                                <span class="js-message"></span>
                            </div>
                        </div>
                    </div>
                    <div class="b-input-line b-input-line--textarea b-input-line--address-textarea js-no-valid">
                        <div class="b-input-line__label-wrapper">
                            <label class="b-input-line__label" for="order-comment">
                                Комментарий к заказу
                            </label>
                        </div>
                        <div class="b-input b-input--registration-form">
                        <textarea class="b-input__input-field b-input__input-field--textarea b-input__input-field--registration-form"
                                  id="comment-express-delivery"
                                  name="comment_dostavista"
                                  placeholder="Укажите здесь дополнительную информацию к заказу.
Например, если для доставки заказа курьеру необходимо въехать на территорию с пропускным режимом. В таком случае курьер свяжется с Вами заранее для заказа пропуска на территорию."><?= $storage->getComment() ?></textarea>
                            <div class="b-error">
                                <span class="js-message"></span>
                            </div>
                        </div>
                    </div>
                </li>
            <? } ?>
            <li class="b-radio-tab__tab b-radio-tab__tab--default-dostavista" data-content-type-time-delivery="default">
                <div class="delivery-block__type <?= (!empty($arResult['SPLIT_RESULT']) && $storage->isSplit()) ? 'js-hidden-valid-fields' : 'visible' ?>" data-delivery="<?= $delivery->getPrice() ?>" data-full="<?= $orderPrice ?>" data-type="oneDelivery">
                    <div class="b-input-line b-input-line--desired-date" data-url="<?= $arResult['URL']['DELIVERY_INTERVALS'] ?>">
                        <div class="b-input-line__label-wrapper">
                            <span class="b-input-line__label">Желаемая дата доставки</span>
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
                    <div class="b-input-line b-input-line--textarea b-input-line--address-textarea js-no-valid">
                        <div class="b-input-line__label-wrapper">
                            <label class="b-input-line__label" for="order-comment">
                                Комментарий к заказу
                            </label>
                        </div>
                        <div class="b-input b-input--registration-form">
                    <textarea class="b-input__input-field b-input__input-field--textarea b-input__input-field--registration-form b-input__input-field--focus-placeholder"
                              id="order-comment"
                              name="comment"
                              placeholder="Укажите здесь дополнительную информацию к заказу.
Например, если для доставки заказа курьеру необходимо въехать на территорию с пропускным режимом. В таком случае курьер свяжется с Вами заранее для заказа пропуска на территорию."><?= $storage->getComment() ?></textarea>
                            <div class="b-error">
                                <span class="js-message"></span>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($arResult['SPLIT_RESULT'])) {
                        /** @var DeliveryResultInterface $delivery1 */
                        $delivery1 = $arResult['SPLIT_RESULT']['1']['DELIVERY'];
                        ?>
                        <div class="change-delivery-type"><span class="js-change-delivery-type" data-type="twoDeliveries">Доставить быстрее</span>
                            <p>Заказ будет разделён на два, с ближайшей
                                доставкой <?= /** @noinspection PhpUnhandledExceptionInspection */
                                DeliveryTimeHelper::showTime($delivery1) ?></p>
                        </div>
                    <?php } ?>
                </div>
            </li>
        </ul>
    </div>


<?php if (!empty($arResult['SPLIT_RESULT'])) {
    $delivery1 = $arResult['SPLIT_RESULT']['1']['DELIVERY'];
    $storage1 = $arResult['SPLIT_RESULT']['1']['STORAGE'];
    $delivery2 = $arResult['SPLIT_RESULT']['2']['DELIVERY'];
    $storage2 = $arResult['SPLIT_RESULT']['2']['STORAGE'];
    $nextDeliveries = $component->getDeliveryService()->getNextDeliveries($delivery1, 10);
    ?>
    <div class="delivery-block__type <?= !$storage->isSplit() ? 'js-hidden-valid-fields' : 'visible' ?>"
         data-delivery="<?= $delivery1->getPrice() ?>"
         data-full="<?= $orderPrice ?>"
         data-type="twoDeliveries">
        <div class="b-input-line b-input-line--desired-date" data-url="<?= $arResult['URL']['DELIVERY_INTERVALS'] ?>">
            <div class="b-input-line__label-wrapper"><span
                        class="b-input-line__label">Желаемая дата доставки первого заказа</span>
            </div>
            <div class="b-select b-select--recall b-select--feedback-page js-select-recovery js-pickup-date">
                <?php
                $selectorStorage = $storage1;
                $selectorName = 'deliveryDate1';
                include 'delivery_date_select.php'
                ?>
            </div>
        </div>
        <?php if (!$delivery->getIntervals()->isEmpty()) {
            $selectorStorage = $storage1;
            $selectorName = 'deliveryInterval1';
            include 'delivery_interval_select.php';
        } ?>
        <div class="b-input-line b-input-line--textarea b-input-line--address-textarea js-no-valid">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="order-comment1">Комментарий к заказу
                </label>
            </div>
            <div class="b-input b-input--registration-form"><textarea
                        class="b-input__input-field b-input__input-field--textarea b-input__input-field--registration-form"
                        id="order-comment1" name="comment1"><?= $storage->getComment() ?></textarea>
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
        </div>
        <?php
        $nextDeliveries = $component->getDeliveryService()->getNextDeliveries($delivery2, 10);
        ?>
        <div class="b-input-line b-input-line--desired-date" data-url="<?= $arResult['URL']['DELIVERY_INTERVALS'] ?>">
            <div class="b-input-line__label-wrapper"><span
                        class="b-input-line__label">Желаемая дата доставки второго заказа</span>
            </div>
            <div class="b-select b-select--recall b-select--feedback-page js-select-recovery js-pickup-date">
                <?php
                $selectorDelivery = $delivery2;
                $selectorStorage = $storage2;
                $selectorName = 'deliveryDate2';
                include 'delivery_date_select.php'
                ?>
            </div>
        </div>
        <?php if (!$delivery->getIntervals()->isEmpty()) {
            $selectorDelivery = $delivery2;
            $selectorStorage = $storage2;
            $selectorName = 'deliveryInterval2';
            include 'delivery_interval_select.php';
        } ?>
        <div class="b-input-line b-input-line--textarea b-input-line--address-textarea js-no-valid">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="order-comment2">Комментарий к заказу
                </label>
            </div>
            <div class="b-input b-input--registration-form"><textarea
                        class="b-input__input-field b-input__input-field--textarea b-input__input-field--registration-form"
                        id="order-comment2" name="comment2"><?= $storage->getSecondComment() ?></textarea>
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
        </div>
        <div class="change-delivery-type"><span class="js-change-delivery-type" data-type="oneDelivery">Объединить заказы</span>
        </div>
    </div>
<?php } ?>
