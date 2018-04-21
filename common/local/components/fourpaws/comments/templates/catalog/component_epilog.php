<?php

use Bitrix\Main\Application;
use FourPaws\App\Application as SymfoniApplication;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$request = Application::getInstance()->getContext()->getRequest();
if ($request->get('new-review') === 'y') { ?>
    <script>
        $(function () {
            if($('ul.b-tab-title__list a[data-tab=reviews]').length > 0) {
                <?php /** без задержки не работает */?>
                setTimeout(function () {
                    $('ul.b-tab-title__list a[data-tab=reviews]').trigger('click');
                    if($('div.b-tab-content div[data-tab-content=reviews] button.js-add-review').length > 0) {
                        $('div.b-tab-content div[data-tab-content=reviews] button.js-add-review').trigger('click');
                    }
                }, 50);
            }
        });
    </script>
<?php }
$uniqueCommentString = $arParams['TYPE'] . '_' . $arParams['HL_ID'] . '_' . $arParams['OBJECT_ID'];
/** @var CCommentsComponent $component */
$arResult['AUTH'] = $component->userAuthService->isAuthorized();
if (!$arResult['AUTH']) {
    $recaptchaService = SymfoniApplication::getInstance()->getContainer()->get('recaptcha.service');?>
    <script type="text/javascript">
        if($('.js-comments-auth-block-<?=$uniqueCommentString?>').length > 0) {
            $('.js-comments-auth-block-<?=$uniqueCommentString?>').css('display', 'block');
        }
        if($('.js-comments-auth-form-<?=$uniqueCommentString?>').length > 0) {
            $('.js-comments-auth-form-<?=$uniqueCommentString?>').css('display', 'block');
        }
        if($('.js-comments-captcha-block-<?=$uniqueCommentString?>').length > 0) {
            $('.js-comments-captcha-block-<?=$uniqueCommentString?>').html('<?=$recaptchaService->getCaptcha();?>').css('display', 'block');
        }
    </script>
<?php } else { ?>
    <script type="text/javascript">
        if($('.js-comments-auth-form-<?=$uniqueCommentString?>').length > 0) {
            $('.js-comments-auth-form-<?=$uniqueCommentString?>').remove();
        }
    </script>
<?php }