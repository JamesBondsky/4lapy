<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\App\Application as App;

?>
<section class="b-popup-pick-city b-popup-pick-city--edit-data js-popup-section" data-popup="edit-phone">
    <a class="b-popup-pick-city__close b-popup-pick-city__close--edit-data js-close-popup"
       href="javascript:void(0);"
       title="Закрыть"></a>
    <div class="b-registration b-registration--edit-data">
        <header class="b-registration__header">
            <h1 class="b-title b-title--h1 b-title--registration">Изменение телефона</h1>
        </header>
        <form class="b-registration__form js-form-validation js-phone-change" data-url="/ajax/user/auth/changePhone/" method="post">
            <?php $phone = $arResult['CUR_USER']['PERSONAL_PHONE'];
            require_once App::getDocumentRoot() . $templateFolder . '/include/phone.php'; ?>
            <button
                    class="b-button b-button--subscribe-delivery js-sms-step">Подтвердить
            </button>
        </form>
    </div>
</section>
