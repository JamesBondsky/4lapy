<?php $sViewportHideCookie = $_COOKIE['viewport_hide'] ?? null; ?>

<div class="js-change-viewport b-panel-change-viewport <?= $sViewportHideCookie !== null ? 'hide-panel' : '' ?>">
    <div class="b-panel-change-viewport__content">
        <button class="js-open-popup b-panel-change-viewport__btn" type="button" data-popup-id="change-view" data-change-viewport-mode='true' data-type="desktop">
            Перейти в полноэкранный режим
        </button>
        <span class="b-panel-change-viewport__close js-close-panel-change-viewport"></span>
    </div>
</div>