<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<main class="b-wrapper" role="main">
    <div class="b-container b-container--error">
        <div class="b-error-page">
            <div class="b-error-page">
                <img src="/static/build/images/content/404.png">
                <p class="b-title b-title--h1">Мы вас не узнали</p>
                <p>Для просмотра этой страницы нужно авторизоваться</p>
                <a href="#" class="js-open-popup" data-popup-id="authorization">Войти</a>
            </div>
        </div>
    </div>
</main>
<?php /** показываем попап если выбило эту форму
 * запуск с задержкой - без нее не рабит */ ?>
<script type="text/javascript">
    $(function () {
        setTimeout(function () {
            $('header div.b-header-info div.b-header-info__item--person a.js-open-popup').trigger('click');
        }, 50);
    });
</script>
