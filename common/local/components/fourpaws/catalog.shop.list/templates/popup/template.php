<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\Decorators\SvgDecorator;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;

/**
 * @var array $arResult
 * @var array $arParams
 * @var StockResultCollection $resultByShop
 */

?>
<section class="b-popup-wrapper__wrapper-modal b-popup-wrapper__wrapper-modal--catalog-stores js-popup-section"
         data-popup="popup-catalog-stores">
    <section class="b-popup-pick-city b-popup-pick-city--catalog-stores js-popup-section" data-popup="popup-catalog-stores">
        <a class="b-popup-pick-city__close b-popup-pick-city__close--catalog-stores js-close-popup"
           href="javascript:void(0);"
           title="Закрыть"></a>
        <div class="b-availability b-availability--catalog-stores">
            <div class="b-availability__content b-availability__content--catalog-stores js-availability-content">
                <div class="b-availability__info-block">
                    <a class="b-link b-link--popup-back b-link--popup-choose-shop js-close-popup"
                       href="javascript:void(0);">Выберите пункт самовывоза</a>
                    <h4 class="b-availability__header b-availability__header--desktop">
                        <?php if ($arResult['IS_DPD']) { ?>
                            Пункты выдачи
                        <?php } else { ?>
                            Наши магазины
                        <?php } ?>
                        <span class="b-availability__header-amount js-catalog-shop-count">(всего 0)</span>
                    </h4>
                    <h4 class="b-availability__header b-availability__header--tablet active">
                        Выберите <?= $arResult['IS_DPD'] ? 'пункт самовывоза' : 'магазин' ?>
                    </h4>
                    <h4 class="b-availability__header b-availability__header--tablet b-availability__header--popuped">
                        <?= $arResult['IS_DPD'] ? 'Пункт самовывоза' : 'Магазин' ?>
                    </h4>
                    <ul class="b-availability-tab-list b-availability-tab-list--catalog-stores js-availability-list">
                        <li class="b-availability-tab-list__item active">
                            <a class="b-availability-tab-list__link js-product-list"
                               href="javascript:void(0)"
                               aria-controls="shipping-list"
                               title="Списком">Списком</a>
                        </li>
                        <li class="b-availability-tab-list__item">
                            <a class="b-availability-tab-list__link js-product-map"
                               href="javascript:void(0)"
                               aria-controls="on-map"
                               title="На карте">На карте</a>
                        </li>
                    </ul>
                    <div class="b-form-inline b-form-inline--catalog-stores-search">
                        <form class="b-form-inline__form">
                            <div class="b-input b-input--stores-search b-input--catalog-stores-search">
                                <input class="b-input__input-field b-input__input-field--stores-search b-input__input-field--catalog-stores-search"
                                       type="text"
                                       id="stores-search"
                                       placeholder="Поиск по адресу, метро и названию ТЦ"
                                       name="text"
                                       data-url="<?= $arResult['STORE_LIST_URL'] ?>"/>
                                <div class="b-error">
                                    <span class="js-message"></span>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="b-tab-delivery b-tab-delivery--catalog-stores js-content-list js-map-list-scroll js-fix-scroll">
                        <ul class="b-delivery-list b-delivery-list--catalog-stores js-delivery-list">
                            <?php include 'include/shop.php' ?>
                        </ul>
                    </div>
                </div>
                <div class="b-availability__show-block">
                    <div class="b-tab-delivery-map b-tab-delivery-map--catalog-stores js-content-map">
                        <div class="b-tab-delivery-map__map" id="map" data-url="<?= $arResult['STORE_LIST_URL'] ?>">
                        </div>
                        <a class="b-link b-link--close-baloon js-product-list" href="javascript:void(0);" title="">
                            <span class="b-icon b-icon--close-baloon">
                                <?= new SvgDecorator('icon-close-baloon', 18, 18) ?>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>
