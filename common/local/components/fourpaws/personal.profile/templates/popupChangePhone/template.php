<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

?>
<section class="b-popup-pick-city b-popup-pick-city--edit-data js-popup-section" data-popup="edit-phone">
    <a class="b-popup-pick-city__close b-popup-pick-city__close--edit-data js-close-popup"
       href="javascript:void(0);"
       title="Закрыть"></a>
    <div class="b-registration b-registration--edit-data">
        <header class="b-registration__header">
            <h1 class="b-title b-title--h1 b-title--registration">Изменение телефона</h1>
        </header>
        <div class="b-registration__form">
            <?php $oldPhone = $phone = $arResult['CUR_USER']['PERSONAL_PHONE'];
            require_once 'include/phone.php';
            require_once 'include/confirm.php';
            ?>
        </div>
    </div>
</section>
