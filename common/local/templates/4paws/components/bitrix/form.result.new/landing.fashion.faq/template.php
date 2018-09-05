<?php

use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\ReCaptchaBundle\Service\ReCaptchaService;

/**
 * @var array $arResult
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (!\is_array($arResult['QUESTIONS']) || empty($arResult['QUESTIONS'])) {
    return;
} ?>

<div class="remained_questions__wrapper js-form-content">
    <div class="content_dropdown mobile_mq js-content-dropdown-trigger">
        <div class="content_dropdown__title">Остались вопросы?
            <div class="content_dropdown__arrow">
                <?= new SvgDecorator('icon-up-arrow') ?>
            </div>
        </div>
    </div>
    <div class="content_dropdown__content--expanded js-content-dropdown-content">
        <div class="remained_questions">
            <div class="remained_questions__title tablet_up_mq">Остались вопросы?</div>
            <form data-url="/ajax/form/faq/add/" method="post"
                  class="js-form-validation js-form-faq remained_questions__form">
                <div class="remained_questions__default">
                    <?= bitrix_sessid_post() ?>
                    <input name="WEB_FORM_ID" value="<?= $arResult['arForm']['ID'] ?>" type="hidden">
                    <div class="remained_questions__paragraph">Напишите нам и мы ответим Вам в самое ближайшее время
                    </div>
                    <?php foreach ($arResult['QUESTIONS'] as $fieldSid => $question) {
                        if ($question['STRUCTURE'][0]['FIELD_TYPE'] === 'hidden') {
                            echo $question['HTML_CODE'];
                        } else {
                            switch ($question['STRUCTURE'][0]['FIELD_TYPE']) {
                                case 'text':
                                case 'email':
                                    $fieldName = 'form_' . $question['STRUCTURE'][0]['FIELD_TYPE'] . '_'
                                                 . $question['STRUCTURE'][0]['ID'];
                                    $type = 'text';
                                    if ($fieldSid === 'phone') {
                                        $type = 'tel';
                                    }

                                    $classes = ['remained_questions__input'];

                                    switch ($fieldSid) {
                                        case 'name':
                                            $prefixPlaceholder = 'Ваше ';
                                            $classes[] = 'js-small-input-two';
                                            break;
                                        case 'email':
                                            $prefixPlaceholder = 'Ваша ';
                                            $classes[] = 'js-small-input-one';
                                            break;
                                        default:
                                            $prefixPlaceholder = 'Ваш ';
                                            break;
                                    } ?>
                                    <div class="remained_questions__input_block js-form-field-block-<?= $fieldSid ?>">
                                        <label class="remained_questions__label"
                                               for="remained_questions_<?= $fieldSid ?>"><?= $question['CAPTION'] ?></label>
                                        <input class="<?= implode(' ', $classes) ?>"
                                               required
                                               type="<?= $type ?>" name="<?= $fieldName ?>"
                                               placeholder="<?= $prefixPlaceholder . ToLower($question['CAPTION']) ?>"
                                               id="remained_questions_<?= $fieldSid ?>"
                                               value="<?= $arResult['CUR_USER'][$fieldSid] ?>">
                                        <div class="b-error"><span class="js-message"></span></div>
                                    </div>
                                    <?php break;
                                case 'textarea':
                                    $fieldName =
                                        'form_' . $question['STRUCTURE'][0]['FIELD_TYPE'] . '_'
                                        . $question['STRUCTURE'][0]['ID']; ?>
                                    <div class="remained_questions__textarea_block js-form-field-block-name">
                                        <label class="remained_questions__label" for="remained_questions_message">Сообщение</label>
                                        <textarea name="<?= $fieldName ?>" maxlength="1000"
                                                  class="remained_questions__textarea"
                                                  placeholder="Введите Ваше сообщение"
                                                  id="remained_questions_message"></textarea>
                                        <div class="b-error"><span class="js-message"></span></div>
                                    </div>
                                    <?php break;
                            } ?>
                            <?php
                        }
                    }

                    if ($arResult['isUseCaptcha']) { ?>
                        <div class="b-feedback-page__capcha remained_questions__input_block">
                            <?php try {
                                echo Application::getInstance()
                                    ->getContainer()
                                    ->get(ReCaptchaService::class)
                                    ->getCaptcha();
                            } catch (ApplicationCreateException $e) {
                                /** ошибка - капчу не вывести */
                            } ?>
                        </div>
                    <?php } ?>


                    <div class="remained_questions__input_block js-form-field-block-name">
                        <label class="remained_questions__checkbox_label" for="remained_questions_agree">
                            <div class="remained_questions__checkbox--checked">
                                <div class="remained_questions__checkbox_icon"></div>
                            </div>
                            <div class="remained_questions__checkbox_text">Я даю согласие на получение информации о
                                выгодных предложениях
                            </div>
                            <input type="checkbox" id="remained_questions_agree" checked class="js-no-valid">
                        </label>
                    </div>
                    <input class="remained_questions__button--secondary js-add-question js-no-valid" type="submit"
                           value="Отправить">
                </div>
                <div class="bottom_separator desktop_mq"></div>
            </form>
            <div class="b-message-block b-hidden">
                <div class="b-message-block__icon">
                    <span class="b-icon b-icon--feedback">
                        <?= new SvgDecorator('icon-check-color', 25, 25) ?>
                    </span>
                </div>
                <p class="b-message-block__text-thanks"></p>
            </div>
        </div>
    </div>
</div>
