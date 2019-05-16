<? require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

global $APPLICATION;
global $USER;

if (!$USER->IsAdmin()) {
    die('Скрипт доступен только администратору!');
}

CJSCore::Init(['jquery2']);
$APPLICATION->ShowHead();
?>
<form id='user-import-form' method='post'>
    <input name='exchange_type' type='hidden' value='export' id='exchange_type'>
    <label for='file_name'>Имя файла</label>
    <input name='file_name' type='text' value='users.csv' id='file_name'><br><br>
    <label for='cnt'>Количество записей обрабатываемых за один раз</label>
    <input name='cnt' type='number' value='1000' min='1000' max='10000' id='cnt'><br><br>
    <label for='step'>Номер страницы, с которой начать выгрузку</label>
    <input name='step' type='number' value='0' min='0' max='0' id='step'><br><br>
    <label for='date_register'>Дата регистрации, с которой выбирать пользователей</label>
    <input name='date_register' type='date' value='2018-05-01' id='date_register'><br><br>
    <label for='date_register_to'>Дата регистрации, до которой выбирать пользователей</label>
    <input name='date_register_to' type='date' value='<?= date('Y-m-d')?>' id='date_register_to'><br><br>
    <progress id='status-bar' value='0' max='100'></progress>
    <span id='percentages'></span><br><br>
    <span id='time'></span><br><br>
    <input type='submit' id='start' value='Сформировать csv-файл'>
    <button type='button' disabled='disabled' id='stop'>Стоп</button>
    <br><br>
    <a style='display: none;' href='' download='' id='file-link'>Скачать файл</a> <span id='file-size'></span>
</form>

<script>
    ;(function ($) {
        'use strict';
        window.UserExporter = function (form) {
            this.$form = form;
            this.$webFormCode = this.$form.find('[name=web_form_code]');
            this.$fileNameInput = this.$form.find('[name=file_name]');
            this.$dateRegisterInput = this.$form.find('[name=date_register]');
            this.$dateRegisterToInput = this.$form.find('[name=date_register_to]');
            this.$ctnInput = this.$form.find('[name=cnt]');
            this.$submitBtn = this.$form.find('#start');
            this.$stopBtn = this.$form.find('#stop');
            this.$statusBar = this.$form.find('#status-bar');
            this.$percentages = this.$form.find('#percentages');
            this.$time = this.$form.find('#time');
            this.$fileLink = this.$form.find('#file-link');
            this.$fileSize = this.$form.find('#file-size');
            this.$step = this.$form.find('#step');
        };
        UserExporter.prototype = {
            STEP: 0,
            PAGE_COUNT: null,
            PETS_IBLOCK_ID: null,
            METHOD: 'POST',
            URL: '../ajax/AjaxUserControl.php',
            TIME: 0,
            STOP: false,
            init: function () {
                let that = this;
                this.$form.submit(function (e) {
                    e.preventDefault();
                    that.TIME = performance.now();
                    that.$time.text('');
                    that.$percentages.text('');
                    that.STEP = parseInt(that.$step.val());
                    that.STOP = false;
                    that.$statusBar.val(0);
                    that.$submitBtn.prop('disabled', true);
                    that.$stopBtn.prop('disabled', false);
                    that.$webFormCode.prop('readonly', true);
                    that.$ctnInput.prop('readonly', true);
                    that.$fileNameInput.prop('readonly', true);
                    that.$dateRegisterInput.prop('readonly', true);
                    that.$dateRegisterToInput.prop('readonly', true);
                    that.$fileLink.attr({
                        'href': '',
                        'download': ''
                    }).hide();
                    that.$fileSize.text('').hide();
                    /*
                    Получаем количество страниц в зависимости от
                    количества обрабатываемых элементов за один шаг
                    */
                    let data = that.$form.serialize() + '&step=get_pages_count';
                    $.ajax({
                        url: that.URL,
                        method: that.METHOD,
                        data: data,
                        context: that,
                        success: that.onAjaxPostCountSuccess
                    });
                });
                this.$stopBtn.click(function (e) {
                    e.preventDefault();
                    that.STOP = true;
                });
                console.log('init');
            },
            onAjaxPostCountSuccess: function (response) {
                let res = JSON.parse(response);
                this.PAGE_COUNT = res.CNT;
                this.PETS_IBLOCK_ID = res.PETS_IBLOCK_ID;
                console.log('pageCount', this.PAGE_COUNT);
                if (this.PAGE_COUNT == 0) {
                    console.log('done');
                    return false;
                }
                this.$step.prop('max', this.PAGE_COUNT);
                this.$statusBar.prop({
                    'value': this.STEP,
                    'max': this.PAGE_COUNT
                });
                this.ajaxPostPartData();
            },
            ajaxPostPartData: function (id = 0) {
                /*
                Записываем часть элементов
                */
                let that = this;
                let data = that.$form.serialize();
                data += '&step=write_elements_on_page&page_number=' + this.STEP + '&file_name=' + this.$fileNameInput.val() + '&id=' + id + '&pets_iblock_id=' + this.PETS_IBLOCK_ID;
                $.ajax({
                    url: that.URL,
                    method: that.METHOD,
                    data: data,
                    context: that,
                    success: that.onAjaxPostPartDataSuccess
                });
            },
            onAjaxPostPartDataSuccess: function (response) {
                let res = JSON.parse(response);
                this.STEP += 1;
                console.log('success write part ' + this.STEP + ' of ' + this.PAGE_COUNT);
                this.$statusBar.prop('value', this.STEP);
                this.$step.val(this.STEP);
                this.$percentages.text(Math.floor(this.STEP / this.PAGE_COUNT * 100) + '%');
                if (parseInt(this.STEP) === parseInt(this.PAGE_COUNT) || this.STOP) {
                    console.log('done');
                    this.$submitBtn.prop('disabled', false);
                    this.$stopBtn.prop('disabled', true);
                    this.$fileLink.attr({
                        'href': '/upload/users/' + this.$fileNameInput.val(),
                        'download': this.$fileNameInput.val()
                    }).show();
                    this.$fileSize.text((res.file_size / (1024 * 1024)).toFixed(2) + ' мб.').show();
                    this.TIME = Math.floor((performance.now() - this.TIME) / 1000); //секунды
                    let stringTime = '';
                    if (Math.floor(this.TIME / 3600) > 0) {
                        stringTime =
                            Math.floor(this.TIME / 3600) + ' ч. '
                            + Math.floor((this.TIME - 3600 * Math.floor(this.TIME / 3600)) / 60) + ' мин. '
                            + this.TIME % 60 + ' сек.';
                    } else if (Math.floor(this.TIME / 60) > 0) {
                        stringTime =
                            Math.floor(this.TIME / 60) + ' мин. '
                            + this.TIME % 60 + ' сек.';
                    } else {
                        stringTime = this.TIME + ' сек.';
                    }
                    this.$time.text(stringTime);
                    if(!this.STOP){
                        this.$webFormCode.prop('readonly', false);
                        this.$ctnInput.prop('readonly', false);
                        this.$step.val('0');
                        this.$fileNameInput.prop('readonly', false);
                        this.$dateRegisterInput.prop('readonly', false);
                        this.$dateRegisterToInput.prop('readonly', false);
                    }
                } else {
                    this.ajaxPostPartData(res.last_id);
                }
            }
        };
        $(document).ready(function () {
            if (typeof UserExporter === 'function') {
                let form = $('#user-import-form');
                if (form.length) {
                    new UserExporter(form).init();
                }
            }
        });
    })(jQuery);
</script>