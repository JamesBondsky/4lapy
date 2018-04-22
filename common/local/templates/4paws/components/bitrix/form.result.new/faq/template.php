<?php

use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Decorators\SvgDecorator;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
if (!\is_array($arResult['QUESTIONS']) || empty($arResult['QUESTIONS'])) {
    return;
}

?>
<div class="fleas-protection-block__form">
    <div class="fleas-protection-block__form--title">Не нашли ответа на свой вопрос?</div>
    <form data-url="/ajax/form/faq/add/" method="post" class="js-form-validation js-form-faq fleas-protection__form">
        <?= bitrix_sessid_post() ?>
        <input name="WEB_FORM_ID" value="<?= $arResult['arForm']['ID'] ?>" type="hidden">

        <?php foreach ($arResult['QUESTIONS'] as $fieldSid => $question) {
            if ($question['STRUCTURE'][0]['FIELD_TYPE'] === 'hidden') {
                echo $question['HTML_CODE'];
            } else {
                switch ($question['STRUCTURE'][0]['FIELD_TYPE']) {
                    case 'text':
                    case 'email':
                        $fieldName = 'form_' . $question['STRUCTURE'][0]['FIELD_TYPE'] . '_' . $question['STRUCTURE'][0]['ID'];
                        $type = 'text';
                        if ($fieldSid === 'phone') {
                            $type = 'tel';
                        } ?>
                        <div class="fleas-protection-block__form--item b-input-line js-form-field-block-<?=$fieldSid?>">
                            <label class="b-input-line__label"><?= $question['CAPTION'] ?></label>
                            <input value="<?=$arResult['CUR_USER'][$fieldSid]?>" class="b-input__input-field <?=$fieldSid === 'name' ? 'js-small-input-two' : ''?><?=$fieldSid === 'email' ? 'js-small-input-one' : ''?>" type="<?=$type?>" name="<?= $fieldName ?>" placeholder="<?=$type === 'email' ? 'Ваша ' : 'Ваше '?><?= ToLower($question['CAPTION']) ?>"/>
                            <div class="b-error"><span class="js-message"></span></div>
                        </div>
                        <?php break;
                    case 'textarea':
                        $fieldName =
                            'form_' . $question['STRUCTURE'][0]['FIELD_TYPE'] . '_' . $question['STRUCTURE'][0]['ID']; ?>
                        <div class="fleas-protection-block__form--item b-input-line">
                            <label class="b-input-line__label"><?= $question['CAPTION'] ?></label>
                            <textarea name="<?= $fieldName ?>" placeholder="Введите Ваш <?= ToLower($question['CAPTION']) ?>" maxlength="1000"></textarea>
                            <div class="b-error"><span class="js-message"></span></div>
                        </div>
                        <?php break;
                } ?>
                <?php
            }
        } ?>

        <?php if ($arResult['isUseCaptcha']) {
            ?>
            <div class="b-feedback-page__capcha">
                <?php try {
                    echo Application::getInstance()->getContainer()->get('recaptcha.service')->getCaptcha();
                } catch (ApplicationCreateException $e) {
                    /** ошибка - капчу не вывести */
                } ?>
            </div>
            <?php
        } ?>
        <div class="b-checkbox b-checkbox--agree">
            <input class="b-checkbox__input" type="checkbox" name="agree" id="checkbox1" value=""/>
            <label class="b-checkbox__name b-checkbox__name--agree" for="checkbox1">
                <span class="b-checkbox__text-agree">Я ознакомлен(а) и соглашаюсь с условиями
                    <a class="b-checkbox__link-agree" href="/company/user-agreement/" target='_blank'
                       title="пользовательского соглашения">пользовательского соглашения.</a>
                </span>
                <span class="b-checkbox__text-agree">Я даю согласие на
                    <a class="b-checkbox__link-agree"
                       href="/company/confidenciality/" target='_blank'
                       title="обработку персональных данных">обработку персональных данных.</a>
                </span>
            </label>
        </div>
        <div class="b-checkbox b-checkbox--agree">
            <input class="b-checkbox__input js-no-valid" type="checkbox" name="agree" id="checkbox2" value=""/>
            <label class="b-checkbox__name b-checkbox__name--agree" for="checkbox2">
                <span class="b-checkbox__text-agree">Я даю согласие на получение информационных писем от 4 Лап.</span>
            </label>
        </div>
        <div class="fleas-protection-block__form--item">
            <button class="b-button js-add-question">Отправить</button>
        </div>
        <div class="fleas-protection-block__form--info">Так же Вы можете задать вопрос, позвонив по бесплатному
            номеру Горячей линии:<span>8 (800) 770-00-22</span></div>
    </form>
    <div class="b-message-block b-hidden">
        <div class="b-message-block__icon">
            <span class="b-icon b-icon--feedback">
                <?=new SvgDecorator('icon-check-color', 25,25)?>
            </span>
        </div>
        <p class="b-message-block__text-thanks"></p>
    </div>
</div>
