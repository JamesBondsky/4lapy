<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die; ?>

<?php if ($arResult['ELEMENT']) { ?>
    <?php
    /**
        переменные шаблона
        $arResult['ELEMENT']['LINK']
        $arResult['NAME']

        $arResult['IMAGES'][$arResult['ELEMENT']['PICTURE']]
        $arResult['IMAGES'][$arResult['ELEMENT']['MOBILE_PICTURE']]
        $arResult['IMAGES'][$arResult['ELEMENT']['TABLET_PICTURE']]
     */
    ?>
    <div class="b-promo-top-full js-promo-top-full hide">
        <div class="b-container b-promo-top-full__container">
            <a href="<?= $arResult['ELEMENT']['LINK'] ?>" class="b-promo-top-full__image-wrap">
                <img src="<?= $arResult['IMAGES'][$arResult['ELEMENT']['PICTURE']] ?>" alt="<?= $arResult['NAME'] ?>" class="b-promo-top-full__image"/>
                <img src="<?= $arResult['IMAGES'][$arResult['ELEMENT']['TABLET_PICTURE']] ?>" alt="<?= $arResult['NAME'] ?>" class="b-promo-top-full__image b-promo-top-full__image--tablet"/>
                <img src="<?= $arResult['IMAGES'][$arResult['ELEMENT']['MOBILE_PICTURE']] ?>" alt="<?= $arResult['NAME'] ?>" class="b-promo-top-full__image b-promo-top-full__image--mobile"/>
            </a>
            <div class="b-promo-top-full__close js-close-promo-top-full"></div>
        </div>
    </div>
<?php } ?>
