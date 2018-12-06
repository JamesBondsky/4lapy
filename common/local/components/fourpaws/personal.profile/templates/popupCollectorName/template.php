<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

?>
<section class="b-popup-pick-city b-popup-pick-city--edit-data js-popup-section" data-popup="collector-name">
    <a class="b-popup-pick-city__close b-popup-pick-city__close--edit-data js-close-popup"
       href="javascript:void(0);"
       title="Закрыть"></a>
    <div class="b-registration b-registration--edit-data">
        <header class="b-registration__header">
            <div class="b-title b-title--h1 b-title--registration"></div>
        </header>
        <div class="b-registration__form">
            <?php $phone = $arResult['CUR_USER']['PERSONAL_PHONE'];
            /** @noinspection UntrustedInclusionInspection */
            require_once 'include/data.php'; ?>
        </div>
    </div>
</section>
