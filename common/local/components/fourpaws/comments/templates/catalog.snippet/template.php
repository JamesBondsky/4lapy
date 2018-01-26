<?php
/**
 * @var \CBitrixComponentTemplate $this
 *
 * @var array                     $arParams
 * @var array                     $arResult
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\WordHelper;

?>
<div class="b-rating b-rating--card">
    <?php for ($i = 1; $i <= 5; $i++) {
        ?>
        <div class="b-rating__star-block<?= $arResult['RATING'] > $i ? ' b-rating__star-block--active' : '' ?>">
            <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
        </div>
        <?php
    } ?>
</div>
<span class="b-common-item__rank-text b-common-item__rank-text--card b-common-item__rank-text--review">На основе <span
            class="b-common-item__rank-num"><?= $arResult['COUNT_COMMENTS'] ?></span> <?= WordHelper::declension(
        $arResult['COUNT_COMMENTS'],
        [
            'отзыва',
            'отзывов',
            'отзывов',
        ]
    ) ?></span>