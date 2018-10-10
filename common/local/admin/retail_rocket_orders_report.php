<?php

/** @noinspection PhpIncludeInspection */
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';


global $APPLICATION;
$APPLICATION->SetTitle('Отчет по заказам для Retail Rocket');

/** @noinspection PhpIncludeInspection */
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
\CUtil::InitJSCore(["jquery"]);
?>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <div class="adm-detail-content-wrap">
        <div class="adm-detail-content">
            <form method="post" action="/ajax/sale/order-report/retail-rocket" id="rr-report-form">
                <label for="rr-report-from">Начальная дата</label>
                <input type="date" id="rr-report-from" name="from" max="<?= date('Y-m-d') ?>">
                <br>
                <input type="submit" class="adm-btn-save" id="rr-report-start" value="Сгенерировать отчет">
            </form>
            <div id="rr-report-progressbar" style="height: 30px; width: 100%"></div>
            <div style="display: none">Обработано <span id="rr-report-stats"></span> заказов</div>
            <div style="display:none">
                <a id="rr-report-link">Скачать отчет</a>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            let step = 1;
            let processed = 0;

            function sendRequest() {
                let $form = $('#rr-report-form');
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
                        if (data.success) {
                            $("#rr-report-progressbar").progressbar({
                                value: data.data.progress
                            });

                            processed += parseInt(data.data.processed);
                            $('#rr-report-stats').text(processed + '/' + data.data.total);
                            $('#rr-report-stats').closest('div').show();

                            if (data.data.progress != 100) {
                                step++;
                                sendRequest();
                            } else {
                                $('#rr-report-link').attr('href', data.data.url).closest('div').show();
                            }
                        }
                    },
                    error: function (data) {
                        console.dir(data);
                    }
                })
            }

            $('#rr-report-form').submit(function (e) {
                e.preventDefault();
                $('#rr-report-start').attr('disabled', 'disabled');
                $("#rr-report-progressbar").progressbar({
                    value: 0
                });
                sendRequest();
            })
        })
    </script>
<?php
/** @noinspection PhpIncludeInspection */
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
