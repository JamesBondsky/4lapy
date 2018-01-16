<?php

use FourPaws\Decorators\SvgDecorator;
use FourPaws\PersonalBundle\Entity\Address;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * @global CMain $APPLICATION
 */

?>
<div class="b-account-adress">
    <?php /** @var Address $address */
    if(\is_array($arResult['ITEMS']) && !empty($arResult['ITEMS'])) {
        foreach ($arResult['ITEMS'] as $address) {
            $name = $address->getName(); ?>
            <div class="b-account-border-block js-delivery-address js-delivery-address"
                 data-name="<?= $address->getName() ?>"
                 data-city="<?= $address->getCity() ?>"
                 data-street="<?= $address->getStreet() ?>"
                 data-number="<?= $address->getHouse() ?>"
                 data-corpus="<?= $address->getHousing() ?>"
                 data-flat="<?= $address->getFlat() ?>"
                 data-code="<?= $address->getIntercomCode() ?>"
                 data-floor="<?= $address->getFloor() ?>"
                 data-primary="<?= $address->isMain() ?>"
                 data-entrance="<?= $address->getEntrance() ?>"
                 data-id="<?= $address->getId() ?>">
                <div class="b-account-border-block__content js-delivery-address">
                    <?php if (!empty($name)) {
                        ?>
                        <div class="b-account-border-block__title"><?= $name ?></div>
                        <?php
                    } ?>
                    <div class="b-adress-info b-adress-info--subscribe">
                        <?php /** @todo метро выпилено, нет добавления
                         * <span class="b-adress-info__label b-adress-info__label--green"></span>м. Братиславская,*/ ?>
                        <?= $address->getFullAddress() ?>
                        <?php /** @todo доп инфа выпилена, нет добавления
                         * <p class="b-adress-info__mode-operation">пн–вс: 10:00–22:00</p>*/ ?>
                    </div>
                    <?php if ($address->isMain()) {
                        ?>
                        <div class="b-account-border-block__label js-prim-address-check">Основной адрес</div>
                        <?php
                    } ?>
                </div>
                <div class="b-account-border-block__button">
                    <div class="b-account-border-block__wrapper-link">
                        <a class="b-account-border-block__link js-open-popup js-edit-query"
                           href="javascript:void(0);"
                           title="Редактировать"
                           data-popup-id="edit-adress-popup"
                           data-url="/ajax/personal/address/update/"
                        >
                        <span class="b-icon b-icon--account-block">
                            <?= new SvgDecorator('icon-edit', 21, 21) ?>
                        </span>
                            <span>Редактировать</span>n
                        </a>
                    </div>
                    <div class="b-account-border-block__wrapper-link">
                        <a class="b-account-border-block__link js-del-popup"
                           href="javascript:void(0);"
                           title="Удалить">
                        <span class="b-icon b-icon--account-block">
                            <?= new SvgDecorator('icon-trash', 21, 21) ?>
                        </span>
                            <span>Удалить</span>
                        </a>
                    </div>
                </div>
                <div class="b-account-border-block__hidden js-hidden-del">
                    <a class="b-account-border-block__link-delete js-close-hidden"
                       href="javascript:void(0);"
                       title="Удалить">
                    <span class="b-icon b-icon--account-delete">
                        <?= new SvgDecorator('icon-delete-account', 26, 26) ?>
                    </span>
                    </a>
                    <div class="b-account-border-block__title b-account-border-block__title--hidden">
                        Удалить
                        <p><span><?= !empty($name) ? $name : $address->getFullAddress() ?></span>?</p>
                    </div>
                    <a class="b-link b-link--account-del b-link--account-del"
                       href="javascript:void(0)"
                       title="Удалить"
                       data-url="/ajax/personal/address/delete/?id=<?= $address->getId() ?>"
                       data-id="<?= $address->getId() ?>">
                        <span class="b-link__text b-link__text--account-del">Удалить</span>
                    </a>
                </div>
            </div>
            <?php
        }
    }?>
    <div class="b-account-border-block b-account-border-block--dashed b-account-border-block--dashed">
        <div class="b-account-border-block__content b-account-border-block__content--dashed">
            <div class="b-account-border-block__title b-account-border-block__title--dashed">Зачем добавлять адреса?
            </div>
            <ul class="b-account-border-block__list">
                <li class="b-account-border-block__item">Не придется вводить адрес при оформлении заказа;</li>
                <li class="b-account-border-block__item">Можно добавить несколько адресов, например, рабочий и
                                                         домашний.
                </li>
            </ul>
        </div>
        <div class="b-account-border-block__button">
            <a class="b-link b-link--account-tab js-add-query js-open-popup js-open-popup--account-tab"
               href="javascript:void(0)"
               title="Добавить адрес"
               data-popup-id="edit-adress-popup"
               data-url="/ajax/personal/address/add/">
                <span class="b-link__text b-link__text--account-tab">Добавить адрес</span>
            </a>
        </div>
    </div>
</div>