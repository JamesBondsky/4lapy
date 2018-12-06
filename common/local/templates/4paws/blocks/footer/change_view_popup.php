<?php 
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\Decorators\SvgDecorator;

?>

<section class="b-popup-change-viewport js-popup-section" data-popup="change-view">
    <div class="b-popup-change-viewport__container">
        <div class="b-popup-change-viewport__close js-close-popup" title="закрыть" data-change-viewport-mode='true' data-type="desktop"></div>
        <div class="b-popup-change-viewport__content">
            <span class="b-icon b-icon--icon-desktop">
                <?= new SvgDecorator('icon-desktop', 60, 60) ?>
            </span>
            <div class="b-popup-change-viewport__descr">
                Вы&nbsp;перешли на&nbsp;полную версию сайта.<br />
                Для возврата на&nbsp;мобильную версию перейдите по&nbsp;ссылке в&nbsp;правом нижнем углу сайта.
            </div>
        </div>
    </div>
</section>
