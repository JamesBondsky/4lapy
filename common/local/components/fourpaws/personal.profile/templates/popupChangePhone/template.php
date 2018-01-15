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
        <form class="b-registration__form js-form-validation js-phone-change"
              data-url="/ajax/personal/profile/changePhone/"
              data-url-sms="/ajax/personal/profile/changePhone/"
              method="post">
            <input type="hidden" name="ID" value="<?=$arResult['CUR_USER']['ID']?>">
            <?php $phone = $arResult['CUR_USER']['PERSONAL_PHONE'];
            require_once 'include/phone.php';
            require_once 'include/confirm.php';
            ?>
            <a class="b-link b-link--subscribe-delivery js-open-popup js-open-popup--subscribe-delivery js-open-popup"
               href="javascript:void(0)"
               title="Изменить"
               data-popup-id="edit-phone-step">
                <span class="b-link__text b-link__text--subscribe-delivery js-open-popup">Изменить</span>
            </a>
            <button
                    class="b-button b-button--subscribe-delivery js-sms-step">Подтвердить
            </button>
        </form>
    </div>
</section>
