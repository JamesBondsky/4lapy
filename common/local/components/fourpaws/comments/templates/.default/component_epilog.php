<?php

use FourPaws\App\Application as SymfoniApplication;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** вынесем авторизационные области из кеша */
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