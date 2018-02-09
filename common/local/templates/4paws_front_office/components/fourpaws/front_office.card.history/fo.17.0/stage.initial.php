<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var FourPawsFrontOfficeCardRegistrationComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 */

if ($arResult['CAN_ACCESS'] !== 'Y') {
    ShowError('При обработке запроса произошла ошибка: отказано в доступе');
    return;
}

if ($arResult['IS_AJAX_REQUEST'] !== 'Y') {
    echo '<div id="refreshingBlockContainer">';
}

// форма
include __DIR__.'/inc.form.php';

if ($arResult['IS_AJAX_REQUEST'] !== 'Y') {
    echo '</div>';
}

if ($arResult['USE_AJAX'] === 'Y' && $arResult['IS_AJAX_REQUEST'] !== 'Y') {
    ?>
    <script data-name="front_office_card_history" type="text/javascript">
        var cardHistoryComponent = new FourPawsFrontOfficeCardHistoryComponent({
            siteId: '<?=\CUtil::JSEscape(SITE_ID)?>',
            siteTemplateId: '<?=\CUtil::JSEscape(SITE_TEMPLATE_ID)?>',
            componentPath: '<?=\CUtil::JSEscape($componentPath)?>',
            template: '<?=\CUtil::JSEscape($arResult['JS']['signedTemplate'])?>',
            parameters: '<?=\CUtil::JSEscape($arResult['JS']['signedParams'])?>',
            sessid: '<?=\CUtil::JSEscape(bitrix_sessid())?>',
            containerSelector: '#refreshingBlockContainer'
        });

        $(document).ready(
            function() {
                // запрос данных по карте
                $(cardHistoryComponent.containerSelector).on(
                    'click',
                    '#ajaxSubmitButton',
                    function(event) {
                        event.preventDefault();

                        var submitButton = $(this);
                        var submitForm = submitButton.closest('form');
                        submitButton.attr('disabled', true);
                        submitForm.find('.form-page__submit-wrap').addClass('loading');

                        var formData = submitForm.serializeArray();
                        var sendData = {};
                        $.each(
                            formData,
                            function(i, field) {
                                sendData[field.name] = field.value;
                            }
                        );

                        cardHistoryComponent.sendRequest(
                            sendData,
                            {
                                callbackComplete: function(jqXHR, textStatus, component) {
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

                // запрос детализации чека
                $(cardHistoryComponent.containerSelector).on(
                    'click',
                    '.order-list__dropdown',
                    function(event) {
                        event.preventDefault();

                        var actionElement = $(this);
                        var chequeId = actionElement.data('id');
                        var orderListElement = $('.order-detail[data-id="'+chequeId+'"] tbody');
                        var isOrderListElementFilled = orderListElement && orderListElement.children().get(0);
                        if (!actionElement.hasClass('open') && !isOrderListElementFilled) {
                            actionElement.addClass('preloader');
                            cardHistoryComponent.sendRequest(
                                {
                                    formName: 'cardHistory',
                                    action: 'postForm',
                                    sessid: cardHistoryComponent.sessid,
                                    getChequeItems: 'Y',
                                    chequeId: chequeId
                                },
                                {
                                    callbackComplete: function(jqXHR, textStatus, component) {
                                        if (textStatus == 'success') {
                                            orderListElement.html(jqXHR.responseText);
                                            toggleOrderList(actionElement);
                                        }
                                        actionElement.removeClass('preloader');
                                    },
                                    callbackError: function(jqXHR, textStatus, component) {
                                        if (jqXHR.status == 403) {
                                            alert('Отказано в доступе');
                                        } else if (jqXHR.status == 401) {
                                            alert('Пожалуйста, авторизуйтесь');
                                        } else {
                                            alert('Request error: '+jqXHR.status+' '+jqXHR.statusText);
                                        }
                                    }
                                }
                            );
                        } else {
                            toggleOrderList(actionElement);
                        }
                    }
                );

            }
        );

        toggleOrderList = function(actionElement) {
            var dataId = actionElement.data('id');
            actionElement.toggleClass('open');
            $('.order-detail[data-id="'+dataId+'"]').slideToggle(400);
        };
    </script>
    <?php
}
