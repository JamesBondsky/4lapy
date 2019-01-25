<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->SetPageProperty('title', '');
$APPLICATION->SetPageProperty('description', '');
$APPLICATION->SetTitle('');
?>

<section id="registr-check" data-id-section-lending="registr-check" class="registr-check-lending">
    <div class="container-lending">

        <?if ($USER->IsAuthorized()) {?>

            <div class="registr-check-landing__form-wrap" data-wrap-form-registr-chek-landing="true">
                <div class="landing-title landing-title_dark">
                    Регистрация чека
                </div>
                <div class="registr-check-landing__form-info">
                    Все поля обязательны для заполнения
                </div>
                <form data-form-registr-chek-landing="true" class="form-landing registr-check-landing__form js-form-validation" method="post" action="/ajax/grandin/request/add/" name="" enctype="multipart/form-data">
                    <div class="form-group">
                        <input type="dateDatepicker" id="DATE_REG_CHECK_GRANDIN" name="date" value="" placeholder="Дата чека" data-datepicker-landing="true" >
                        <div class="b-error">
                            <span class="js-message"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <input data-price-check-landing="true" type="price" id="SUM_REG_CHECK_GRANDIN" name="sum" value="" placeholder="Сумма чека (не менее 1800р)" >
                        <div class="b-error">
                            <span class="js-message"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="text" id="SURNAME_REG_CHECK_GRANDIN" name="surname" value="" placeholder="Фамилия" >
                        <div class="b-error">
                            <span class="js-message"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="text" id="NAME_REG_CHECK_GRANDIN" name="name" value="" placeholder="Имя" >
                        <div class="b-error">
                            <span class="js-message"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="tel" id="PHONE_REG_CHECK_GRANDIN" name="phone" value="" placeholder="Телефон" >
                        <div class="b-error">
                            <span class="js-message"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="email" id="EMAIL_REG_CHECK_GRANDIN" name="email" value="" placeholder="E-mail" >
                        <div class="b-error">
                            <span class="js-message"></span>
                        </div>
                    </div>

                    <div class="read-rules">
                        <input type="checkbox" id="READ_RULES_REG_CHECK_GRANDIN" name="rules" value="Y" checked>
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

        <?} else {?>

            <div class="registr-check-landing__message">
                <div class="landing-title landing-title_dark">
                    Регистрируйте чеки<br/> и&nbsp;выигрывайте призы каждую неделю
                </div>
            </div>

        <?}?>


    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>