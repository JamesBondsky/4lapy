<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/urlrewrite.php');

CHTTP::SetStatus('404 Not Found');
@define('ERROR_404', 'Y');

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$APPLICATION->SetTitle('404 Not Found'); ?>

<h1>Страница не найдена</h1>
<div class="b-goto">
    <h3 class="b-goto__header">
        Вам стоит перейти на
        <a class="b-goto__link b-goto__link--another b-goto__link--other" href="/">страницу страниц</a>
    </h3>
</div>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'); ?>
