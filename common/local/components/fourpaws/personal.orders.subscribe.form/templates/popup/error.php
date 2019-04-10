<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}


?>
<div class="b-popup-subscribe-delivery__content">
    <div class="b-popup-subscribe-delivery__top">
        <? include __DIR__. '/header.php' ?>

        <div class="b-container">
            <div class="b-popup-subscribe-delivery__inner">
                Произошла ошибка:
                <?
                    foreach($arResult['ERROR'] as $error){
                        ShowError($error);
                    }
                ?>
                <div class="b-popup-subscribe-delivery__btns">
                    <a href="javascript:void(0);" class="b-button b-button--cancel-subscribe-delivery js-close-popup"
                       title="Закрыть">
                        Закрыть
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

