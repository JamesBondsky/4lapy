<?php
global $error404Message, $error404Title, $error404H1, $error404CustomCloseWorkerTags, $error404DivErrorPageCustomAttributes;
$hasCustom = (!empty($error404Message) || !empty($error404Title) || !empty($error404H1));
include_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/urlrewrite.php';

CHTTP::SetStatus('404 Not Found');
@define('ERROR_404', 'Y');

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->SetTitle(!empty($error404Title) ? $error404Title : '404 Not Found'); ?>

<?php if (!$hasCustom){ ?>
<main class="b-wrapper" role="main">
    <div class="b-container b-container--error">
<?php } ?>
        <div class="b-error-page" <?=$error404DivErrorPageCustomAttributes ?: ''?>>
            <?php /* @todo image resize helper */ ?>
            <img src="/static/build/images/content/404.png">
            <p class="b-title b-title--h1"><?= !empty($error404H1) ? $error404H1 : 'Такой страницы нет' ?></p>
            <?= !empty($error404Message) ? $error404Message : '<p>Проверьте правильность адреса, воспользуйтесь поиском или начните с главной страницы</p><a href="/">Перейти на главную страницу</a>' ?>
        </div>
<?php if (!$hasCustom){ ?>
    </div>
</main>
<?php } ?>

<?php echo $error404CustomCloseWorkerTags ?: '';
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
//}?>
