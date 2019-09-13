<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die; ?>

<?php if ($arResult['ELEMENT']) { ?>
    <div class="b-promo-top-full b-promo-top-full--fashion js-promo-top-full hide">
        <div class="b-container b-promo-top-full__fashion-container">
            <div>
                <a href="<?= $arResult['ELEMENT']['LINK'] ?>" target="_blank">
                    <img src="<?= $arResult['IMAGES'][$arResult['ELEMENT']['PICTURE']] ?>" alt="<?= $arResult['NAME'] ?>" class="b-promo-top-full__fashion-image"/>
                    <img src="<?= $arResult['IMAGES'][$arResult['ELEMENT']['MOBILE_PICTURE']] ?>" alt="<?= $arResult['NAME'] ?>" class="b-promo-top-full__fashion-image b-promo-top-full__fashion-image--mobile"/>
                </a>
            </div>

            <div class="b-promo-top-full__close js-close-promo-top-full"></div>
        </div>
    </div>
<?php } ?>
