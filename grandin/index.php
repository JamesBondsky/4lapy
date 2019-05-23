<?php

use FourPaws\Helpers\ProtectorHelper;
use FourPaws\AppBundle\AjaxController\LandingController;


require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->SetPageProperty('title', '');
$APPLICATION->SetPageProperty('description', 'Для участия в акции купите любой корм Grandin на сумму от 1800 рублей и зарегистрируйте покупку  на сайте акции grandin.4lapy.ru.');
$APPLICATION->SetTitle('Как выиграть запас корма Grandin на год вперед?');
?>

<section id="registr-check" data-id-section-landing="registr-check" class="registr-check-landing">
    <div class="container-landing">

        <div class="registr-check-landing__important-information <?if ($USER->IsAuthorized()) {?>registr-check-landing__important-information--indent<? } ?>">
            <p>Личные данные, вводимые при регистрации в&nbsp;акции, должны совпадать с&nbsp;личными данными,<br class="hidden-mobile" /> к&nbsp;которым привязана бонусная карта Четыре Лапы, также используемая для регистрации в&nbsp;акции.</p>
            <p>Участники, у&nbsp;которых указанная информация не&nbsp;совпадает, автоматически выбывают из&nbsp;общего списка зарегистрированных участников для начисления бонусов и&nbsp;розыгрыша призов.</p>
        </div>

        <?if ($USER->IsAuthorized()) {?>
            <? $arUser = \CUser::GetById($USER->GetID())->Fetch(); ?>

            <div class="registr-check-landing__form-wrap" data-wrap-form-registr-chek-landing="true">
                <div class="landing-title landing-title_dark">
                    Регистрация чека
                </div>
                <div class="registr-check-landing__form-info">
                    Все поля обязательны для заполнения
                </div>
                <form data-form-registr-check-landing="true" class="form-landing registr-check-landing__form js-form-validation" method="post" action="/ajax/landing/request/add/" name="" enctype="multipart/form-data">
                    <? $token = ProtectorHelper::generateToken(ProtectorHelper::TYPE_GRANDIN_REQUEST_ADD); ?>
                    <input class="js-no-valid" type="hidden" name="<?=$token['field']?>" value="<?=$token['token']?>">
                    <input class="js-no-valid" type="hidden" name="landingType" value="<?= LandingController::$grandinLanding ?>">
                    <div class="form-group">
                        <input type="dateDatepicker" id="DATE_REG_CHECK_GRANDIN" name="date" value="" placeholder="Дата чека" data-datepicker-landing="true" >
                        <div class="b-error">
                            <span class="js-message"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <input data-price-check-landing="true" type="minPriceLanding" data-min-price-landing="1800" id="SUM_REG_CHECK_GRANDIN" name="sum" value="" placeholder="Сумма чека (не менее 1800р)" >
                        <div class="b-error">
                            <span class="js-message"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="text" id="SURNAME_REG_CHECK_GRANDIN" class="js-small-input" name="surname" value="<?=$arUser['LAST_NAME']?:''?>" placeholder="Фамилия" >
                        <div class="b-error">
                            <span class="js-message"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="text" id="NAME_REG_CHECK_GRANDIN" class="js-small-input" name="name" value="<?=$arUser['NAME']?:''?>" placeholder="Имя" >
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
                        <input type="emailLanding" id="EMAIL_REG_CHECK_GRANDIN" name="email" value="<?=$arUser['EMAIL']?:''?>" placeholder="E-mail" >
                        <div class="b-error">
                            <span class="js-message"></span>
                        </div>
                    </div>

                    <div class="form-group form-group_select js-wrap-select-form-registr-check-landing">
                        <label for="petType">Мой питомец</label>
                        <select class="b-select__block" id="PET_TYPE_REG_CHECK_GRANDIN" name="petType" data-select-form-registr-check-landing="true">
                            <option value="" disabled="disabled" selected="selected">Выберите вид</option>

                            <? foreach (LandingController::$petTypes as $key => $value) { ?>
                                <option value="<?=$key?>"><?=$value?></option>
                            <?}?>

                        </select>

                        <div class="b-error">
                            <span class="js-message"></span>
                        </div>
                    </div>

                    <div class="read-rules">
                        <input type="checkbox" id="READ_RULES_REG_CHECK_GRANDIN" name="rules" value="Y" checked>
                        <label for="READ_RULES_REG_CHECK_GRANDIN"><span></span> с <a href="/grandin_rules.pdf" target="_blank">правилами</a> акции ознакомлен</label>
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

        <? } ?>

    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>