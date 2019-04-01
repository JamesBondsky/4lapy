<?php

use FourPaws\Helpers\ProtectorHelper;
use FourPaws\AppBundle\AjaxController\LandingController;


require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->SetPageProperty('title', '');
$APPLICATION->SetPageProperty('description', '');
$APPLICATION->SetTitle('');
?>

<section id="registr-check" data-id-section-landing="registr-check" class="registr-check-landing registr-check-landing_white registr-check-landing_canin">
    <div class="container-landing">

        <?if ($USER->IsAuthorized()) {?>
            <? $arUser = \CUser::GetById($USER->GetID())->Fetch(); ?>

            <div class="registr-check-landing__form-wrap" data-wrap-form-registr-chek-landing="true">
                <div class="landing-title landing-title_gray-dark">
                    Регистрация чека
                </div>
                <div class="registr-check-landing__form-info">
                    Все поля обязательны для заполнения
                </div>
                <form data-form-registr-check-landing="true" class="form-landing registr-check-landing__form js-form-validation" method="post" action="/ajax/landing/request/add/" name="" enctype="multipart/form-data">
                    <?$token = ProtectorHelper::generateToken(ProtectorHelper::TYPE_GRANDIN_REQUEST_ADD); ?>
                    <input class="js-no-valid" type="hidden" name="<?=$token['field']?>" value="<?=$token['token']?>">
                    <input class="js-no-valid" type="hidden" name="langindType" value="<?= LandingController::$royalCaninLangind ?>">
                    <div class="form-group">
                        <input type="dateDatepicker" id="DATE_REG_CHECK_CANIN" name="date" value="" placeholder="Дата чека" data-datepicker-landing="true" data-min-date=04/08/2019" data-max-date=05/19/2019" autocomplete="of">
                        <div class="b-error">
                            <span class="js-message"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <input data-price-check-landing="true" type="minPriceLanding" data-min-price-landing="1000" id="SUM_REG_CHECK_CANIN" name="sum" value="" placeholder="Сумма чека (не менее 1000р)" >
                        <div class="b-error">
                            <span class="js-message"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="text" id="SURNAME_REG_CHECK_CANIN" class="js-small-input" name="surname" value="<?=$arUser['LAST_NAME']?:''?>" placeholder="Фамилия" >
                        <div class="b-error">
                            <span class="js-message"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="text" id="NAME_REG_CHECK_CANIN" class="js-small-input" name="name" value="<?=$arUser['NAME']?:''?>" placeholder="Имя" >
                        <div class="b-error">
                            <span class="js-message"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="tel" id="PHONE_REG_CHECK_CANIN" name="phone" value="<?=$arUser['PERSONAL_PHONE']?:''?>" placeholder="Телефон" >
                        <div class="b-error">
                            <span class="js-message"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="emailLanding" id="EMAIL_REG_CHECK_CANIN" name="email" value="<?=$arUser['EMAIL']?:''?>" placeholder="E-mail" >
                        <div class="b-error">
                            <span class="js-message"></span>
                        </div>
                    </div>

                    <div class="read-rules">
                        <input type="checkbox" id="READ_RULES_REG_CHECK_CANIN" name="rules" value="Y" cheform-group form-group_select js-wrap-select-form-registr-check-landingcked>
                        <label for="READ_RULES_REG_CHECK_CANIN"><span></span>Я прочитал(а) и&nbsp;согласен(на) с&nbsp;<a href="#" target="_blank">правилами</a> акции</label>
                        <div class="b-error">
                            <span class="js-message"></span>
                        </div>
                    </div>

                    <div class="registr-check-landing__btn-form">
                        <button type="submit" class="btn-canin" >Отправить</button>
                    </div>
                </form>

                <div class="registr-check-landing__response" data-response-form-landing="true"></div>
            </div>

        <?} else {?>

            <div class="registr-check-landing__message">
                <div class="landing-title landing-title_gray">
                    Регистрируйте чеки<br/> и&nbsp;выигрывайте призы каждую неделю
                </div>
            </div>

        <?}?>
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>