<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array $arResult
 * @var array $arParams
 */

use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\WordHelper;

$itemCount = count($arResult['BASKET']);

if (true !== $arParams['IS_AJAX']) {
    echo '<div class="b-header-info__item b-header-info__item--cart">';
} ?>
    <a class="b-header-info__link js-link-header-basket-mini<?= $itemCount ? ' js-open-popover ' : '' ?> js-toggle-popover-mobile-header"
       href="<?= $arParams['PATH_TO_BASKET'] ?>" title="Корзина">
        <span class="b-icon">
            <?= new SvgDecorator('icon-cart', 16, 16) ?>
        </span>
        <span class="b-header-info__inner">Корзина</span>
        <span class="b-header-info__number js-count-products">
            <?= $itemCount ?>
        </span>
    </a>
<?php if ($itemCount) { ?>
    <div class="b-popover b-popover--cart js-popover js-content-popover-mobile-header">
        <div class="b-cart-popover">
            <a class="b-button b-button--popover-cart" href="<?= $arParams['PATH_TO_BASKET'] ?>" title="Перейти в корзину">
                Перейти в корзину
            </a>
            <?php
            if ($itemCount) {
                foreach ($arResult['BASKET'] as $item) { ?>
                    <div class="b-cart-item">
                        <div class="b-cart-item__image-wrapper">
                            <?php if ($item['IMAGE']) { ?>
                                <img class="b-cart-item__image"
                                     src="<?= $item['IMAGE'] ?>"
                                     alt="<?= $item['NAME'] ?>"
                                     title="<?= $item['NAME'] ?>"/>
                            <?php } ?>
                        </div>
                        <div class="b-cart-item__info">
                            <div class="b-clipped-text b-clipped-text--cart-popover">
                                <a class="b-cart-item__name"
                                   href="<?= $item['DETAIL_PAGE_URL'] ?>"
                                   title="<?= $item['NAME'] ?>">
                                    <?php if ($item['BRAND']) { ?>
                                        <span class="span-strong"><?= $item['BRAND'] ?> </span>
                                    <?php } ?>
                                    <?= $item['NAME'] ?>
                                </a>
                            </div>
                            <?php if ($item['WEIGHT'] > 0) { ?>
                                <span class="b-cart-item__weight">
                                    <?= WordHelper::showWeight(
                                        $item['WEIGHT'] * $item['QUANTITY'], true
                                    ) ?>
                                </span>
                            <?php } ?>
                            <span class="b-cart-item__amount">(<?= $item['QUANTITY'] ?> шт.)</span>
                        </div>
                    </div>
                <?php }
            } ?>
        </div>
    </div>
<?php }

if (true !== $arParams['IS_AJAX']) {
    echo '</div>';
}
