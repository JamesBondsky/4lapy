<?php
/**
 * Created by PhpStorm.
 * Date: 26.04.2018
 * Time: 17:52
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

use FourPaws\Decorators\SvgDecorator;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

?>


<div class="b-advice">
    <h2 class="b-title b-title--advice">Соберите набор и получите подарки</h2>
    <p class="b-advice__description">
        <?= $arResult['PROMO_DESCRIPTION']; ?>
    </p>
    <div class="b-advice__list">
        <div class="b-advice__list-items js-advice-list">
            <div class="b-advice__item js-advice-item js-item-tmp"
                 data-price="<?= $arResult['PRICE']; ?>"
                 data-offer-id="<?= $arResult['OFFER_ID']; ?>"
                 data-url="/ajax/catalog/product-info/groupSet/"
                 data-index="0">
                <span class="b-advice__image-wrapper">
                    <img
                            class="b-advice__image"
                            src="<?= $arResult['IMG']; ?>"
                            alt="<?= $arResult['NAME']; ?>"
                            title="<?= $arResult['NAME']; ?>"
                            role="presentation"/>
                </span>
                <span class="b-advice__block">
                    <span class="product-link">
                        <span class="b-clipped-text b-clipped-text--advice">
                            <span>
                                <?= $arResult['NAME']; ?>
                            </span>
                        </span>
                    </span>
                    <span class="b-advice__info">
                        <span class="b-advice__weight"><?= $arResult['WEIGHT']; ?></span>
                        <span class="b-advice__cost"><?= $arResult['PRICE']; ?><span
                                    class="b-ruble b-ruble--advice">₽</span></span>
                    </span>
                </span>
            </div>
            <?php
            $i = 0;
            while (++$i <= $arResult['EMPTY_SLOTS']) {
                ?>
                <div class="b-advice__sign b-advice__sign--plus"></div>
                <div class="b-advice__item blank js-advice-item" data-index="<?= $i; ?>">
                    <div class="b-advice__image-wrapper">
                    </div>
                    <span class="b-advice__block"><span class="b-advice__product-link"><span
                                    class="b-advice__blank-text">                                                                      <br/>                                                       <br/>                                           </span></span><span
                                class="b-advice__info"><span
                                    class="b-advice__blank-text">                  </span></span><a
                                class="b-button b-button--advice-set js-group-set"
                                href="javascript:void(0);"
                                title="Выбрать товар"
                                data-popup-id="popup-choose-gift">Выбрать товар</a></span>
                </div>
                <?php
            }
            ?>
        </div>
        <script id="advice-item-template" type="text/template">
            <div class="b-advice__item js-tempalate js-advice-item"
                 data-price="{{cost}}"
                 data-offerid="{{offerid}}"
                 data-advicecount="2">
                <div class="b-advice__image-wrapper js-tempalate"><img class="b-advice__image"
                                                                       src="{{image}}"
                                                                       alt=""
                                                                       title=""
                                                                       role="presentation"/>
                </div>
                <span class="b-advice__block js-tempalate"><a class="b-advice__product-link js-tempalate"
                                                              href="javascript:void(0)"
                                                              title=""><span
                                class="b-clipped-text b-clipped-text--advice"><span><strong>{{title}}  </strong>{{description}}</span></span></a><span
                            class="b-advice__info js-tempalate"><span
                                class="b-advice__weight">{{weight}}</span><span
                                class="b-advice__cost">{{cost}}<span
                                    class="b-ruble b-ruble--advice">₽</span></span></span><a
                            class="b-button b-button--advice-set js-group-set"
                            href="javascript:void(0);"
                            title="Другой товар"
                            data-popup-id="popup-choose-gift">Другой товар</a></span>
            </div>
        </script>
        <script id="advice-item-blank" type="text/template">
            <div class="b-advice__item js-tempalate blank js-advice-item" data-advicecount="2">
                <div class="b-advice__image-wrapper">
                </div>
                <span class="b-advice__block">
                    <span class="b-advice__product-link">
                        <span class="b-advice__blank-text">
                            <br/><br/>
                        </span>
                    </span>
                    <span class="b-advice__info"><span class="b-advice__blank-text"></span>
                    </span>
                    <a
                            class="b-button b-button--advice-set js-group-set"
                            href="javascript:void(0);"
                            title="Выбрать товар"
                            data-popup-id="popup-choose-gift">Выбрать товар</a></span>
            </div>
        </script>
        <div class="b-advice__list-cost">
            <div class="b-advice__sign b-advice__sign--equally">
            </div>
            <div class="b-advice__cost-wrapper">
                <span class="b-advice__total-price">
                    <span class="b-advice__old-price js-advice-oldprice" style="display:none;">
                        <span class="js-value">6 445</span>
                        <span class="b-ruble b-ruble--total b-ruble--light">₽</span>
                    </span>
                    <span class="b-advice__new-price js-advice-newprice">
                        <span class="js-value"><?= $arResult['PRICE']; ?></span>
                        <span class="b-ruble b-ruble--total">₽</span>
                    </span>
                </span>
                <a class="b-advice__basket-link js-advice2basket"
                   href="javascript:void(0)"
                   title=""
                   data-url="/common/static/build/json/ajax-sale-basket-add.json">
                    <span class="b-advice__basket-text">В корзину</span>
                    <span class="b-icon b-icon--advice">
                      <?= new SvgDecorator('icon-cart', 20, 20) ?>
                    </span>
                </a>
            </div>
        </div>
    </div>
</div>
