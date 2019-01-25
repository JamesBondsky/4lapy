<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->SetPageProperty('title', '');
$APPLICATION->SetPageProperty('description', '');
$APPLICATION->SetTitle('');
?>

<section id="registr-check" data-id-section-lending="registr-check" class="registr-check-lending">
    <div class="container-lending">

        <?if ($USER->IsAuthorized()) {?>

            <div class="registr-check-lending__form-wrap" data-wrap-form-registr-chek-lending="true">
                <div class="lending-title lending-title_dark">
                    Регистрация чека
                </div>
                <div class="registr-check-lending__form-info">
                    Все поля обязательны для заполнения
                </div>
                <form data-form-registr-chek-lending="true" class="form-lending registr-check-lending__form" method="post" action="/" name="" enctype="multipart/form-data">
                    <div class="form-group">
                        <input type="text" id="DATE_REG_CHECK_GRANDIN" name="DATE_REG_CHECK_GRANDIN" value="" placeholder="Дата чека" required >
                    </div>
                    <div class="form-group">
                        <input type="text" id="SUM_REG_CHECK_GRANDIN" name="SUM_REG_CHECK_GRANDIN" value="" placeholder="Сумма чека (не менее 1800р)" required >
                    </div>
                    <div class="form-group">
                        <input type="text" id="SURNAME_REG_CHECK_GRANDIN" name="SURNAME_REG_CHECK_GRANDIN" value="" placeholder="Фамилия" required >
                    </div>
                    <div class="form-group">
                        <input type="text" id="NAME_REG_CHECK_GRANDIN" name="NAME_REG_CHECK_GRANDIN" value="" placeholder="Имя" required >
                    </div>
                    <div class="form-group">
                        <input type="text" id="PHONE_REG_CHECK_GRANDIN" name="PHONE_REG_CHECK_GRANDIN" value="" placeholder="Телефон" required >
                    </div>
                    <div class="form-group">
                        <input type="email" id="EMAIL_REG_CHECK_GRANDIN" name="EMAIL_REG_CHECK_GRANDIN" value="" placeholder="E-mail" required >
                    </div>

                    <div class="read-rules">
                        <input type="checkbox" id="READ_RULES_REG_CHECK_GRANDIN" name="READ_RULES_REG_CHECK_GRANDIN" value="Y" checked>
                        <label for="READ_RULES_REG_CHECK_GRANDIN"><span></span> с <a href="#" target="_blank">правилами акции</a> ознакомлен</label>
                    </div>

                    <div class="registr-check-lending__btn-form">
                        <input type="submit" class="lending-btn lending-btn_dark" value="Отправить">
                    </div>
                </form>

                <div class="registr-check-lending__response" data-response-form-lending="true"></div>
            </div>

        <?} else {?>

            <div class="registr-check-lending__message">
                <div class="lending-title lending-title_dark">
                    Регистрируйте чеки<br/> и&nbsp;выигрывайте призы каждую неделю
                </div>
            </div>

        <?}?>


    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>