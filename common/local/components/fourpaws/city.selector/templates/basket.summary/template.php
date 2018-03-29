<?php

use FourPaws\Decorators\SvgDecorator;

?>
<div class="b-information-order__delivery-info">
    <span class="b-information-order__where-delivery">Где хотите забрать?</span>
    <a class="b-information-order__city js-open-popup" href="javascript:void(0)"
       title="<?= $arResult['SELECTED_CITY']['NAME'] ?>" data-popup-id="pick-city">
        <?= $arResult['SELECTED_CITY']['NAME'] ?>
        <span class="b-icon b-icon--where-delivery">
            <?= new SvgDecorator('icon-arrow-down', 10, 10); ?>
        </span>
    </a>
</div>
