<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->SetPageProperty('title', '');
$APPLICATION->SetPageProperty('description', '');
$APPLICATION->SetTitle('');
?>

<section data-id-section-landing="registr-check" class="registr-check-landing">
    <div class="container-landing">
        <!-- Если НЕ авторизован -->
        <div class="registr-check-landing__message">
            <div class="landing-title landing-title_dark">
                Регистрируйте чеки<br/> и&nbsp;выигрывайте призы каждую неделю
            </div>
        </div>
        <!-- End Если НЕ авторизован -->

        <!-- Если авторизован -->
        <div class="registr-check-landing__form-wrap" data-wrap-form-registr-chek-landing="true">
            <div class="landing-title landing-title_dark">
                Регистрация чека
            </div>
            <div class="registr-check-landing__form-info">
                Все поля обязательны для заполнения
            </div>
            <form data-form-registr-chek-landing="true" class="form-landing registr-check-landing__form js-form-validation" method="post" action="/" name="" enctype="multipart/form-data">
                <div class="form-group">
                    <input type="dateDatepicker" id="DATE_REG_CHECK_GRANDIN" name="DATE_REG_CHECK_GRANDIN" value="" placeholder="Дата чека" data-datepicker-landing="true" >
                    <div class="b-error">
                        <span class="js-message"></span>
                    </div>
                </div>
                <div class="form-group">
                    <input data-price-check-landing="true" type="price" id="SUM_REG_CHECK_GRANDIN" name="SUM_REG_CHECK_GRANDIN" value="" placeholder="Сумма чека (не менее 1800р)" >
                    <div class="b-error">
                        <span class="js-message"></span>
                    </div>
                </div>
                <div class="form-group">
                    <input type="text" id="SURNAME_REG_CHECK_GRANDIN" name="SURNAME_REG_CHECK_GRANDIN" value="" placeholder="Фамилия" >
                    <div class="b-error">
                        <span class="js-message"></span>
                    </div>
                </div>
                <div class="form-group">
                    <input type="text" id="NAME_REG_CHECK_GRANDIN" name="NAME_REG_CHECK_GRANDIN" value="" placeholder="Имя" >
                    <div class="b-error">
                        <span class="js-message"></span>
                    </div>
                </div>
                <div class="form-group">
                    <input type="tel" id="PHONE_REG_CHECK_GRANDIN" name="PHONE_REG_CHECK_GRANDIN" value="" placeholder="Телефон" >
                    <div class="b-error">
                        <span class="js-message"></span>
                    </div>
                </div>
                <div class="form-group">
                    <input type="email" id="EMAIL_REG_CHECK_GRANDIN" name="EMAIL_REG_CHECK_GRANDIN" value="" placeholder="E-mail" >
                    <div class="b-error">
                        <span class="js-message"></span>
                    </div>
                </div>

                <div class="read-rules">
                    <input type="checkbox" id="READ_RULES_REG_CHECK_GRANDIN" name="READ_RULES_REG_CHECK_GRANDIN" value="Y" checked> 
                    <label for="READ_RULES_REG_CHECK_GRANDIN"><span></span> с <a href="#" target="_blank">правилами акции</a> ознакомлен</label>
                    <div class="b-error">
                        <span class="js-message"></span>
                    </div>
                </div>

                <div class="registr-check-landing__btn-form">
                    <button type="submit" class="landing-btn landing-btn_dark" >Отправить</button>
                </div>
            </form>

            <div class="registr-check-landing__response" data-response-form-landing="true"></div>
        </div>
        <!-- End Если авторизован -->
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>