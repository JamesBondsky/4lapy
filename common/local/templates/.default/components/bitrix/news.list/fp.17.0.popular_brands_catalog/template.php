<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true) {
    die();
}

/**
 * Популярные бренды в каталоге
 *
 * @updated: 09.02.2018
 */

/** @var \CBitrixComponentTemplate $this */
/** @var array $arResult */

$this->setFrameMode(true);

if (!$arResult['ITEMS']) {
    return;
}

?>
    <div class="b-catalog__brand">
        <div class="b-line b-line--catalog"></div>
        <section class="b-common-section">
            <div class="b-common-section__title-box b-common-section__title-box--catalog b-common-section__title-box--catalog-popular">
                <h2 class="b-title b-title--catalog b-title--catalog-popular"><?=Loc::getMessage('POPULAR_BRANDS_CATALOG.TITLE')?></h2>
            </div>
            <div class="b-common-section__content b-common-section__content--catalog b-common-section__content--catalog-popular">
                <div class="b-popular-brand"><?php
                    foreach ($arResult['ITEMS'] as $item) {
                        ?>
                        <div class="b-popular-brand-item b-popular-brand-item--catalog">
                            <a class="b-popular-brand-item__link b-popular-brand-item__link--catalog" title="<?=$item['NAME']?>" href="<?=$item['DETAIL_PAGE_URL']?>">
                                <?php
                                if ($item['PRINT_PICTURE']) {
                                    ?>
                                    <img class="b-popular-brand-item__image js-image-wrapper" src="<?=$item['PRINT_PICTURE']['SRC']?>" alt="<?=$item['PRINT_PICTURE']['ALT']?>" title="<?=$item['PRINT_PICTURE']['TITLE']?>">
                                    <?php
                                }
                                ?>
                            </a>
                        </div>
                        <?php
                    }
                ?></div>
            </div>
        </section>
    </div>
<?php
