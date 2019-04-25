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
    <input name='exchange_type' type='hidden' value='import' id='exchange_type'>
    <label for='file_name'>Имя файла для чтения</label>
    <input name='file_name' type='text' value='users.csv' id='file_name'><br><br>
    <label for='cnt'>Количество записей обрабатываемых за один раз</label>
    <input name='cnt' type='number' value='300' min='10' max='500' id='cnt'><br><br>
    <label for='step'>Номер страницы, с которой начать выгрузку</label>
    <input name='step' type='number' value='0' min='0' max='0' id='step'><br><br>
    <progress id='status-bar' value='0' max='100'></progress>
    <span id='percentages'></span><br><br>
    <span id='time'></span><br><br>
    <input type='submit' id='start' value='Обработать csv-файл'>
    <button type='button' disabled='disabled' id='stop'>Стоп</button>
</form>

<script>
    ;(function ($) {
        'use strict';
        window.UserExporter = function (form) {
            this.$form = form;
            this.$webFormCode = this.$form.find('[name=web_form_code]');
            this.$fileNameInput = this.$form.find('[name=file_name]');
            this.$ctnInput = this.$form.find('[name=cnt]');
            this.$submitBtn = this.$form.find('#start');
            this.$stopBtn = this.$form.find('#stop');
            this.$statusBar = this.$form.find('#status-bar');
            this.$percentages = this.$form.find('#percentages');
            this.$time = this.$form.find('#time');
            this.$step = this.$form.find('#step');
        };
        UserExporter.prototype = {
            STEP: 0,
            PAGE_COUNT: null,
            METHOD: 'POST',
            URL: '../ajax/AjaxUserControl.php',
            TIME: 0,
            STOP: false,
            USERS_ADDED: 0,
            USERS_FOUND: 0,
            PETS_ADDED: 0,
            PETS_FOUND: 0,
            TOTAL_PETS: 0,
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
                if (response == 0) {
                    console.log('Файл не найден!');
                    return false;
                }
                this.PAGE_COUNT = response;
                console.log('pageCount', this.PAGE_COUNT);
                this.$step.prop('max', this.PAGE_COUNT);
                this.$statusBar.prop({
                    'value': this.STEP,
                    'max': this.PAGE_COUNT - this.STEP
                });
                this.ajaxPostPartData();
            },
            ajaxPostPartData: function () {
                /*
                Записываем часть элементов
                */
                let that = this;
                let data = $('#user-import-form').serialize();
                data += '&step=process_elements_on_page&page_number=' + this.STEP +
                    '&file_name=' + this.$fileNameInput.val() +
                    '&users_added=' + this.USERS_ADDED +
                    '&users_found=' + this.USERS_FOUND +
                    '&pets_added=' + this.PETS_ADDED +
                    '&pets_found=' + this.PETS_FOUND +
                    '&total_pets=' + this.TOTAL_PETS;
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
                this.USERS_ADDED = res.users_added;
                this.USERS_FOUND = res.users_found;
                this.PETS_ADDED = res.pets_added;
                this.PETS_FOUND = res.pets_found;
                this.TOTAL_PETS = res.total_pets;
                console.log('');
                console.log('success process part ' + this.STEP + ' of ' + this.PAGE_COUNT + '. Element processed: ' + (parseInt(this.STEP) * parseInt(this.$ctnInput.val())));
                console.log('Users added: ' + this.USERS_ADDED);
                console.log('Users found: ' + this.USERS_FOUND);
                console.log('Pets added: ' + this.PETS_ADDED);
                console.log('Pets found: ' + this.PETS_FOUND);
                console.log('Total pets: ' + this.TOTAL_PETS);
                console.log('');
                this.$statusBar.prop('value', this.STEP);
                this.$step.val(this.STEP);
                this.$percentages.text(Math.floor(this.STEP / this.PAGE_COUNT * 100) + '%');
                if (parseInt(this.STEP) === parseInt(this.PAGE_COUNT) || this.STOP) {
                    console.log('done');
                    this.$submitBtn.prop('disabled', false);
                    this.$stopBtn.prop('disabled', true);
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
                } else {
                    this.ajaxPostPartData();
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