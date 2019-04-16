<?php

use Bitrix\Main\Application;
use FourPaws\App\Application as SymfoniApplication;
use FourPaws\ReCaptchaBundle\Service\ReCaptchaInterface;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$request = Application::getInstance()->getContext()->getRequest();
?>
<script type="text/javascript" data-epilog-handlers="true">
    if (epilogHandlers === undefined) {
        // класс для комплексного выполнения всех обработчиков
        var epilogHandlers = {
            handlers: [],
            add: function (handler) {
                this.handlers[this.handlers.length] = handler;
            },
            execute: function () {
                this.handlers.forEach(function (handler) {
                    if (typeof handler === 'function') {
                        handler();
                    }
                });
                this.handlers = [];
            },
        };
    }

    <? if ($request->get('new-review') === 'y') { ?>
    epilogHandlers.add(function () {
        if ($('ul.b-tab-title__list a[data-tab=reviews]').length > 0) {
            <?php /** без задержки не работает */?>
            setTimeout(function () {
                $('ul.b-tab-title__list a[data-tab=reviews]').trigger('click');
                if ($('div.b-tab-content div[data-tab-content=reviews] button.js-add-review').length > 0) {
                    $('div.b-tab-content div[data-tab-content=reviews] button.js-add-review').trigger('click');
                }
            }, 50);
        }
    });
    <?php }
    $uniqueCommentString = $arParams['TYPE'] . '_' . $arParams['HL_ID'] . '_' . $arParams['OBJECT_ID'];
    /** @var CCommentsComponent $component */
    $arResult['AUTH'] = $component->userAuthService->isAuthorized();
    ?>

    <? if (!$arResult['AUTH']) {
    $recaptchaService = SymfoniApplication::getInstance()->getContainer()->get(ReCaptchaInterface::class); ?>

    epilogHandlers.add(function () {
        if ($('.js-comments-auth-block-<?=$uniqueCommentString?>').length > 0) {
            $('.js-comments-auth-block-<?=$uniqueCommentString?>').css('display', 'block');
        }
        if ($('.js-comments-auth-form-<?=$uniqueCommentString?>').length > 0) {
            $('.js-comments-auth-form-<?=$uniqueCommentString?>').css('display', 'block');
        }
        if ($('.js-comments-captcha-block-<?=$uniqueCommentString?>').length > 0) {
            $('.js-comments-captcha-block-<?=$uniqueCommentString?>').html('<?=$recaptchaService->getCaptcha();?>').css('display', 'block');
        }
    });

    <?php } else { ?>
    epilogHandlers.add(function () {
        if ($('.js-comments-auth-form-<?=$uniqueCommentString?>').length > 0) {
            $('.js-comments-auth-form-<?=$uniqueCommentString?>').remove();
        }
    });
    <?php } ?>

    $(function () {
        epilogHandlers.execute();
    })
</script>
