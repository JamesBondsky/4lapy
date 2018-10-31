<?php 
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\Decorators\SvgDecorator;

?>

<section class="b-popup-change-viewport js-popup-section" data-popup="change-view">
    <div class="b-popup-change-viewport__container">
        <a class="js-close-popup b-popup-change-viewport__close" href="javascript:void(0)" title="закрыть"></a>
        <div class="b-popup-change-viewport__content">
            <span class="b-icon b-icon--icon-desktop">
                <?= new SvgDecorator('icon-desktop', 60, 60) ?>
            </span>
            <div class="b-popup-change-viewport__descr">
                Вы&nbsp;перешли к&nbsp;обычной версии сайта.<br />
                К&nbsp;возврату просмотра мобильной версии и&nbsp;обратно нажмите ссылку в&nbsp;правом нижнем углу.
            </div>
        </div>
    </div>
</section>
