<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
} ?>

<section class="b-popup-wrapper__wrapper-modal js-popup-section" data-popup="alert-popup" style="display: none;">
    <div class="b-registration b-registration--popup js-popup-alert-title success" data-popup="alert-popup">
        <a class="b-registration__close js-close-popup" href="javascript:void(0)" title="закрыть"></a>
        <header class="b-registration__header">
            <span class="b-title b-title--h1 b-title--popup-success">Успех</span>
            <span class="b-title b-title--h1 b-title--popup-error">Ошибка</span>
        </header>
        <div class="b-registration__content b-registration__content--simple-text">
            <div class="b-registration__text-instruction js-popup-simple-text"></div>
        </div>
    </div>
</section>

<?php /** @todo js fix! remove it. */ ?>
<div style="display: none" class="js-phone-change-one"></div>
