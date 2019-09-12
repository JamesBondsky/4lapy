<?

use FourPaws\Helpers\WordHelper;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<? foreach($arResult['ELEMENTS'] as $i => $element) { ?>

    <? if($i == 0) { ?>
        <div class="col-md-12 animate-box shelter__row-items">
    <? } ?>


    <? if($i != 0 && $i % 4 == 0) { ?>
        </div>

        <? if($i == 8) { ?>
            <div class="roll_block">
        <? } ?>

        <div class="col-md-12 animate-box shelter__row-items">
    <? } ?>


    <div class="col-md-3 animate-box">
        <div class="shelter_wrap">
            <div class="shelter_logo-wrap">
                <div class="shelter_logo">
                    <img src="<?=$element['IMG']?>" alt="<?=$element['NAME']?>" />
                </div>
            </div>
            <div class="shelter__info">
                <div class="shelter_tag">
                    <span><?=$element['PROPERTIES']['TYPE']['VALUE']?></span>
                </div>
                <div class="shelter_about">
                    <a href="javascript:void(0);" data-popup-id="shelter_popup" class="js-open-popup" data-content-id="<?=$i?>"><?=$element['NAME']?></a>
                </div>
                <div class="shelter_note">
                    <span><?=$element['CITY']?></span>
                    <? if($element['PROPERTIES']['PETS_AMOUNT']['VALUE'] > 0) { ?>
                    | <span><?=$element['PROPERTIES']['PETS_AMOUNT']['VALUE']?> <?=WordHelper::declension($element['PROPERTIES']['PETS_AMOUNT']['VALUE'], ['питомец', 'питомца', 'питомцев'])?></span>
                    <? } ?>
                    <? if($element['PROPERTIES']['LIFETIME']['VALUE'] > 0) { ?>
                    &nbsp;|&nbsp; <span><?=$element['PROPERTIES']['LIFETIME']['VALUE']?> <?=WordHelper::declension($element['PROPERTIES']['LIFETIME']['VALUE'], ['год', 'года', 'лет'])?></span>
                    <? } ?>
                </div>
            </div>
        </div>

        <? // контент для поп-апа ?>
        <div class="js-popup-content" data-id="<?=$i?>" style="display: none">
            <a class="b-popup-pick-city__close b-popup-pick-city__close--authorization js-close-popup" href="javascript:void(0);" title="Закрыть"></a>
            <div class="shelter_wrap">
                <div class="shelter_logo">
                    <img src="<?=$element['IMG']?>" alt="<?=$element['NAME']?>">
                </div>
                <div class="shelter_tag">
                    <span><?=$element['PROPERTIES']['TYPE']['VALUE']?></span>
                </div>
                <div class="shelter_about">
                    <?=$element['NAME']?>
                </div>

                <div class="shelter_about_text">
                    <?=$element['DESCRIPTION']?>
                </div>
                <div class="shelter_note">
                    <span><?=$element['CITY']?></span>
                    <? if($element['PROPERTIES']['PETS_AMOUNT']['VALUE'] > 0) { ?>
                        | <span><?=$element['PROPERTIES']['PETS_AMOUNT']['VALUE']?> <?=WordHelper::declension($element['PROPERTIES']['PETS_AMOUNT']['VALUE'], ['питомец', 'питомца', 'питомцев'])?></span>
                    <? } ?>
                    <? if($element['PROPERTIES']['LIFETIME']['VALUE'] > 0) { ?>
                        &nbsp;|&nbsp; <span><?=$element['PROPERTIES']['LIFETIME']['VALUE']?> <?=WordHelper::declension($element['PROPERTIES']['LIFETIME']['VALUE'], ['год', 'года', 'лет'])?></span>
                    <? } ?>
                </div>
            </div>
        </div>

    </div>

<? } ?>

</div> <!--col-md-12-->
</div> <!--roll_block-->



