<?php 

/**
 * @var $sViewportCookie - Значение куки отвечающе за переключение вьпорта с мобильного на десктоп.
 */
$sViewportCookie = $_COOKIE['viewport'] ?? null;

?>

<div class="js-change-viewport b-panel-change-viewport <?= $sViewportCookie !== null ? 'hide-panel' : '' ?>">
    <div class="b-panel-change-viewport__content">
        <button class="js-open-popup b-panel-change-viewport__btn" type="button" data-popup-id="change-view">
            Перейти в полноэкранный режим
        </button>
        <span class="b-panel-change-viewport__close js-close-panel-change-viewport"></span>
    </div>
</div>