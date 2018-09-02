<?php

/** @noinspection PhpIncludeInspection */
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';


global $APPLICATION;
$APPLICATION->SetTitle('Отчет по наличию товаров');

/** @noinspection PhpIncludeInspection */
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
\CUtil::InitJSCore(["jquery"]);
?>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <div class="adm-detail-content-wrap">
        <div class="adm-detail-content">
            <form method="post" action="/ajax/catalog/product-report/availability" id="product-report-form">
                <textarea class="typearea"
                          name="articles"
                          style="width:100%;height:200px;"
                          placeholder="Укажите артикулы (через пробел или каждый с новой строки)"
                ></textarea>
                <br>
                <input type="submit" class="adm-btn-save" value="Сгенерировать отчет">
            </form>
            <div id="product-report-progressbar" style="height: 30px; width: 100%"></div>
            <div style="display:none">
                <a id="product-report-link">Скачать отчет</a>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            let step = 1;

            function sendRequest() {
                let $form = $('#product-report-form');
                let data = $form.serializeArray();
                data.push({
                    name: 'step',
                    value: step
                });
                $.ajax({
                    url: $form.attr('action'),
                    type: 'post',
                    dataType: 'json',
                    data: data,
                    success: function (data) {
                        $( "#product-report-progressbar" ).progressbar({
                            value: data.progress
                        });
                        step++;
                        if (data.progress != 100) {
                            sendRequest();
                        } else {
                            $('#product-report-link').attr('href', data.link).closest('div').show();
                        }
                    },
                    error: function (data) {
                        console.dir(data);
                    }
                })
            }

            $('#product-report-form').submit(function (e) {
                e.preventDefault();
                sendRequest();
            })
        })
    </script>
<?php
/** @noinspection PhpIncludeInspection */
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
