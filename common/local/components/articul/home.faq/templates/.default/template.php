<?
/**
 * @var CHomeFaqComponent $component
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
} ?>

<? if(!empty($arResult['ELEMENTS'])) { ?>
    <section class="questions-comfortable-living">
        <div class="b-container">
            <h2 class="title-comfortable-living title-comfortable-living_questions">Вопросы и ответы</h2>
            <div class="questions-comfortable-living__accordion">
            <? foreach ($arResult['ELEMENTS'] as $item) { ?>
                <div class="item-accordion">
                    <div class="item-accordion__header js-toggle-accordion">
                        <span class="item-accordion__header-inner"><?=$item['NAME']?></span>
                    </div>
                    <div class="item-accordion__block js-dropdown-block">
                        <div class="item-accordion__block-content">
                            <div class="item-accordion__block-text">
                                <?=$item['PREVIEW_TEXT']?>
                            </div>
                            <? if($item['PREVIEW_PICTURE']) { ?>
                            <div class="item-accordion__block-img">
                                <img src="<?=\CFile::GetPath($item['PREVIEW_PICTURE'])?>" alt="<?=$item['NAME']?>" />
                            </div>
                            <? } ?>
                        </div>
                    </div>
                </div>
            <? } ?>
            </div>
        </div>
    </section>
<? } ?>