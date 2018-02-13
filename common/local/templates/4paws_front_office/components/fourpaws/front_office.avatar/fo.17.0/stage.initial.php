<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain                                     $APPLICATION
 * @var array                                        $arParams
 * @var array                                        $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate                     $this
 * @var string                                       $templateName
 * @var string                                       $componentPath
 */

if ($arResult['CAN_ACCESS'] !== 'Y') {
    ShowError('При обработке запроса произошла ошибка: отказано в доступе');
    return;
}

if ($arResult['IS_AVATAR_AUTHORIZED'] === 'Y') {
    echo '<br><p>Вы уже находитесь в режиме "аватар". <a href="'.$arParams['LOGOUT_URL'].'">Выйти из режима</a>.</p>';
    return;
}

if ($arResult['IS_AJAX_REQUEST'] !== 'Y') {
    echo '<div id="refreshingBlockContainer">';
}

// форма
include __DIR__ . '/inc.form.php';

if ($arResult['IS_AJAX_REQUEST'] !== 'Y') {
    echo '</div>';
}

if ($arResult['USE_AJAX'] === 'Y' && $arResult['IS_AJAX_REQUEST'] !== 'Y') {
    ?>
    <script data-name="front_office_avatar" type="text/javascript">
        var avatarComponent = new FourPawsFrontOfficeAvatarComponent({
                                                                         siteId:            '<?=\CUtil::JSEscape(SITE_ID)?>',
                                                                         siteTemplateId:    '<?=\CUtil::JSEscape(SITE_TEMPLATE_ID)?>',
                                                                         componentPath:     '<?=\CUtil::JSEscape($componentPath)?>',
                                                                         template:          '<?=\CUtil::JSEscape($arResult['JS']['signedTemplate'])?>',
                                                                         parameters:        '<?=\CUtil::JSEscape($arResult['JS']['signedParams'])?>',
                                                                         sessid:            '<?=\CUtil::JSEscape(bitrix_sessid())?>',
                                                                         containerSelector: '#refreshingBlockContainer'
                                                                     });
        
        $(document).ready(
            function () {
                // запрос списка пользователей
                $(avatarComponent.containerSelector).on(
                    'click',
                    '#ajaxSubmitButton',
                    function (event) {
                        event.preventDefault();
    
                        var submitButton = $(this);
                        var submitForm   = submitButton.closest('form');
                        submitButton.attr('disabled', true);
                        submitForm.find('.form-page__submit-wrap').addClass('loading');
    
                        var formData = submitForm.serializeArray();
                        var sendData = {};
                        $.each(
                            formData,
                            function (i, field) {
                                sendData[field.name] = field.value;
                            }
                        );
    
                        avatarComponent.sendRequest(
                            sendData,
                            {
                                callbackComplete: function (jqXHR, textStatus, component) {
                                    if (textStatus == 'success') {
                                        $(component.containerSelector).html(jqXHR.responseText);
                                        $('html, body').animate(
                                            {
                                                scrollTop: $(document).height()
                                            },
                                            200
                                        );
                                        //submitButton.removeAttr('disabled');
                                        //submitForm.find('.form-page__submit-wrap').removeClass('loading');
                                    }
                                }
                            }
                        );
                    }
                );
    
                // запрос авторизации от имени пользователя
                $(avatarComponent.containerSelector).on(
                    'click',
                    '._action-auth',
                    function (event) {
                        event.preventDefault();
    
                        var actionElement = $(this);
                        var userId        = actionElement.data('id');
                        actionElement.addClass('preloader');
    
                        var actionContainer = actionElement.closest('.user-list');
                        if (actionContainer.hasClass('loading')) {
                            return;
                        }
    
                        var submitButton = $('#ajaxSubmitButton');
                        var submitForm   = submitButton.closest('form');
                        submitButton.attr('disabled', true);
                        submitForm.find('.form-page__submit-wrap').addClass('loading');
    
                        var sendRequest = true;
                        if (sendRequest) {
                            actionContainer.addClass('loading');
                            avatarComponent.sendRequest(
                                {
                                    formName: 'avatar',
                                    action:   'userAuth',
                                    sessid:   avatarComponent.sessid,
                                    userId:   userId
                                },
                                {
                                    dataType:         'json',
                                    callbackComplete: function (jqXHR, textStatus, component) {
                                        if (textStatus == 'success') {
                                            var result = $.parseJSON(jqXHR.responseText);
                                            if (result.message !== '') {
                                                alert(result.message);
                                            }
                                            if (result.success === 'Y') {
                                                if (result.redirectUrl) {
                                                    window.location.href = result.redirectUrl;
                                                }
                                            }
                                        } else {
                                            alert('Не удалось распознать ответ сервера');
                                        }
                                        actionElement.removeClass('preloader');
                                        actionContainer.removeClass('loading');
                                        submitButton.attr('disabled', false);
                                        submitForm.find('.form-page__submit-wrap').removeClass('loading');
                                    },
                                    callbackError:    function (jqXHR, textStatus, component) {
                                        if (jqXHR.status == 403) {
                                            alert('Отказано в доступе');
                                        } else if (jqXHR.status == 401) {
                                            alert('Пожалуйста, авторизуйтесь');
                                        } else {
                                            //alert('Request error: ' + jqXHR.status + ' ' + jqXHR.statusText);
                                        }
                                    }
                                }
                            );
                        }
                    }
                );
            }
        );
    </script>
    <?php
}
