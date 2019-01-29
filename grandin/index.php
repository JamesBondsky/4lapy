<?php

use FourPaws\Helpers\ProtectorHelper;


require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->SetPageProperty('title', '');
$APPLICATION->SetPageProperty('description', 'Для участия в акции купите любой корм Grandin на сумму от 1800 рублей и зарегистрируйте покупку  на сайте акции grandin.4lapy.ru.');
$APPLICATION->SetTitle('Как выиграть запас корма Grandin на год вперед?');
?>

<section id="registr-check" data-id-section-landing="registr-check" class="registr-check-landing">
    <div class="container-landing">

        <?if ($USER->IsAuthorized()) {?>
            <? $arUser = \CUser::GetById($USER->GetID())->Fetch(); ?>

            <div class="registr-check-landing__form-wrap" data-wrap-form-registr-chek-landing="true">
                <div class="landing-title landing-title_dark">
                    Регистрация чека
                </div>
                <div class="registr-check-landing__form-info">
                    Все поля обязательны для заполнения
                </div>
                <form data-form-registr-check-landing="true" class="form-landing registr-check-landing__form js-form-validation" method="post" action="/ajax/grandin/request/add/" name="" enctype="multipart/form-data">
                    <? $token = ProtectorHelper::generateToken(ProtectorHelper::TYPE_GRANDIN_REQUEST_ADD); ?>
                    <input class="js-no-valid" type="hidden" name="<?=$token['field']?>" value="<?=$token['token']?>">


                    <div class="form-group">
                        <input type="dateDatepicker" id="DATE_REG_CHECK_GRANDIN" name="date" value="" placeholder="Дата чека" data-datepicker-landing="true" >
                        <div class="b-error">
                            <span class="js-message"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <input data-price-check-landing="true" type="minPrice" data-min-price-landing="1800" id="SUM_REG_CHECK_GRANDIN" name="sum" value="" placeholder="Сумма чека (не менее 1800р)" >
                        <div class="b-error">
                            <span class="js-message"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="text" id="SURNAME_REG_CHECK_GRANDIN" name="surname" value="<?=$arUser['LAST_NAME']?:''?>" placeholder="Фамилия" >
                        <div class="b-error">
                            <span class="js-message"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="text" id="NAME_REG_CHECK_GRANDIN" name="name" value="<?=$arUser['NAME']?:''?>" placeholder="Имя" >
                        <div class="b-error">
                            <span class="js-message"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="tel" id="PHONE_REG_CHECK_GRANDIN" name="phone" value="<?=$arUser['PERSONAL_PHONE']?:''?>" placeholder="Телефон" >
                        <div class="b-error">
                            <span class="js-message"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="email" id="EMAIL_REG_CHECK_GRANDIN" name="email" value="<?=$arUser['EMAIL']?:''?>" placeholder="E-mail" >
                        <div class="b-error">
                            <span class="js-message"></span>
                        </div>
                    </div>

                    <div class="form-group form-group_select js-wrap-select-form-registr-check-landing">
                    	<label for="petType">Мой питомец</label>
                        <select class="b-select__block" id="PET_TYPE_REG_CHECK_GRANDIN" name="petType" data-select-form-registr-check-landing="true">
                            <option value="" disabled="disabled" selected="selected">Выберите вид</option>
                            <option value="1">Кошка</option>
                            <option value="2">Собака мелкой породы</option>
                            <option value="3">Собака средней или крупной породы</option>
                        </select>

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