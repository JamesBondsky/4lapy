<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/urlrewrite.php');

CHTTP::SetStatus('404 Not Found');
@define('ERROR_404', 'Y');

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$APPLICATION->SetTitle('404 Not Found'); ?>

<main class="b-wrapper" role="main">
    <div class="b-container b-container--error">
        <div class="b-error-page">
            <img src="/static/build/images/content/404.png">
            <p class="b-title b-title--h1">Такой страницы нет</p>
            <p>Проверьте правильность адреса, воспользуйтесь поиском или начните с главной страницы</p><a href="/">Перейти на главную страницу</a>
        </div>
    </div>
</main>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'); ?>
