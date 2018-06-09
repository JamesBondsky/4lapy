<?php
/**
 * Created by PhpStorm.
 * Date: 26.04.2018
 * Time: 17:52
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

use FourPaws\BitrixOrm\Model\ResizeImageDecorator;
use FourPaws\Catalog\Model\Bundle;
use FourPaws\Catalog\Model\BundleItem;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\WordHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var Bundle $bundle */
$bundle = $arResult['BUNDLE']; ?>
<div class="b-advice">
    <?php if (!empty($bundle->getName())) { ?>
        <h2 class="b-title b-title--advice"><?= $bundle->getName() ?></h2>
    <?php } ?>
    <div class="b-advice__list">
        <div class="b-advice__list-items js-advice-list">
            <?php /** @var BundleItem $product */
            $i = -1;
            $countItems = \count($bundle->getProducts());
            foreach ($bundle->getProducts() as $product) {
                $i++;
                $offer = $product->getOffer(); ?>
                <div class="b-advice__item js-advice-item"
                     data-offerid="<?= $offer->getId() . '_' . $product->getQuantity(); ?>">
                    <?php if ($i > 0){ ?>
                    <a href="<?= $offer->getLink() ?>">
                        <?php } ?>
                        <div class="b-advice__image-wrapper">
                            <?php /** @var ResizeImageDecorator $image */
                            $image = $offer->getResizeImages(140, 140)->first(); ?>
                            <img
                                    class="b-advice__image"
                                    src="<?= $image->getSrc(); ?>"
                                    alt="<?= $offer->getName(); ?>"
                                    title="<?= $offer->getName(); ?>"
                                    role="presentation"/>
                        </div>
                        <span class="b-advice__block">
                            <div class="product-link">
                                <span class="b-clipped-text b-clipped-text--advice">
                                    <span>
                                        <?= $offer->getName(); ?>
                                    </span>
                                </span>
                            </div>
                            <span class="b-advice__info">
                                <?php $weight = $offer->getCatalogProduct()->getWeight();
                                if ($weight > 0) { ?>
                                    <span class="b-advice__weight"><?= WordHelper::showWeight($weight) ?></span>
                                <?php }
                                $oldPrice = $offer->getOldPrice();
                                $price = $offer->getPrice();
                                if ($oldPrice > 0 && $oldPrice > $price) {
                                    ?>
                                    <span class="b-advice__old-price">
                                        <span class="js-value"><?= $oldPrice ?></span>
                                        <span class="b-ruble b-ruble--advice b-ruble--light">₽</span>
                                    </span>
                                    <?php /** делаем перевод на новую строку далее ибо не помещается - по красоте поправить верстку*/
                                    ?>
                                    <br/>
                                <?php } ?>
                                <span class="b-advice__cost">
                                    <?= $price; ?>
                                    <span class="b-ruble b-ruble--advice">₽</span>
                                </span>
                                <span> x <?= $product->getQuantity() ?></span>
                            </span>
                        </span>
                        <?php if ($i > 0){ ?>
                    </a>
                <?php } ?>
                </div>
                <?php if ($i < $countItems - 1) { ?>
                    <div class="b-advice__sign b-advice__sign--plus"></div>
                <?php }
            } ?>
        </div>
        <div class="b-advice__list-cost">
            <div class="b-advice__sign b-advice__sign--equally">
            </div>
            <div class="b-advice__cost-wrapper">
                <span class="b-advice__total-price">
                    <?php if ($arResult['OLD_SUM'] > 0 && $arResult['OLD_SUM'] > $arResult['SUM']) { ?>
                        <span class="b-advice__old-price js-advice-oldprice">
                            <span class="js-value"><?= $arResult['OLD_SUM'] ?></span>
                            <span class="b-ruble b-ruble--total b-ruble--light">₽</span>
                        </span>
                    <?php } ?>
                    <span class="b-advice__new-price js-advice-newprice">
                        <span class="js-value"><?= $arResult['SUM']; ?></span>
                        <span class="b-ruble b-ruble--total">₽</span>
                    </span>
                    <span class="b-advice__new-price js-advice-bonus">
                        <span class="js-value"><?= $arResult['BONUS_FORMATTED']; ?></span>
                    </span>
                </span>
                <a class="b-advice__basket-link js-advice2basket-bundle"
                   href="javascript:void(0)"
                   title=""
                   data-url="/ajax/sale/basket/bulkAddBundle/"
                >
                    <span class="b-advice__basket-text">В корзину</span>
                    <span class="b-icon b-icon--advice">
                      <?= new SvgDecorator('icon-cart', 20, 20) ?>
                    </span>
                </a>
            </div>
        </div>
    </div>
</div>
