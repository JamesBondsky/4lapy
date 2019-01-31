<?php

use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\ReCaptchaBundle\Service\ReCaptchaService;


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (!\is_array($arResult['QUESTIONS']) || empty($arResult['QUESTIONS'])) {
    return;
}
?>

<form data-form-feedback-landing="true" class="form-landing feedback-landing__form js-form-validation"
      name="<?= $arResult['arForm']['SID'] ?>" data-url="/ajax/form/add/feedback/" action="/ajax/form/add/feedback/"
      method="post"
      enctype="multipart/form-data">
    <?= bitrix_sessid_post() ?>
    <input name="WEB_FORM_ID" value="<?= $arResult['arForm']['ID'] ?>" type="hidden">

    <?
        foreach ($arResult['QUESTIONS'] as $fieldSid => $question) {
            if ($question['STRUCTURE'][0]['FIELD_TYPE'] === 'hidden') {
                echo $question['HTML_CODE'];
            }
        }
    ?>

    <?
        foreach ($arResult['QUESTIONS'] as $fieldSid => $question) {
            if ($question['STRUCTURE'][0]['FIELD_TYPE'] === 'dropdown') {
                $fieldName = 'form_' . $question['STRUCTURE'][0]['FIELD_TYPE'] . '_' . $fieldSid;

                foreach ($arResult['arAnswers'][$fieldSid] as $item) {
                    if ($item['MESSAGE'] == 'Другое') {
                        $value = $item['ID'];
                    }
                }

                ?>
                <input type="hidden" name="<?=$fieldName?>" value="<?=$value?>" />
                <?
            }
        }
    ?>

    <?
        foreach ($arResult['QUESTIONS'] as $fieldSid => $question) {
            if ($question['STRUCTURE'][0]['FIELD_TYPE'] === 'textarea') {
                $fieldName = 'form_' . $question['STRUCTURE'][0]['FIELD_TYPE'] . '_' . $question['STRUCTURE'][0]['ID'];
                ?>
                    <div class="form-group form-group_full">
                        <textarea id="<?= $fieldName ?>" name="<?= $fieldName ?>" placeholder="напишите ваш вопрос"></textarea>
                        <div class="b-error">
                            <span class="js-message"></span>
                        </div>
                    </div>
                <?
            }
        }
    ?>

    <?
    foreach ($arResult['QUESTIONS'] as $fieldSid => $question) {
        if (in_array($question['STRUCTURE'][0]['FIELD_TYPE'], ['text', 'email'])) {
            $fieldName = 'form_' . $question['STRUCTURE'][0]['FIELD_TYPE'] . '_' . $question['STRUCTURE'][0]['ID'];
            $type = 'text';
            if ($fieldSid === 'email') {
                $type = 'email';
            } elseif ($fieldSid === 'phone') {
                $type = 'tel';
            } ?>
            ?>
            <div class="form-group">
                <input type="<?= $type ?>" id="FIO_REG_FEEDBACK_GRANDIN" name="<?= $fieldName ?>" value="<?= $arResult['CUR_USER'][$fieldSid] ?>" placeholder="<?=$question['CAPTION']?>">
                <div class="b-error">
                    <span class="js-message"></span>
                </div>
            </div>
            <?
        }
    }
    ?>

    <? if ($arResult['isUseCaptcha']) { ?>
        <div class="form-group form-group_full">
            <?php try {
                echo App::getInstance()->getContainer()->get(ReCaptchaService::class)->getCaptcha();
            } catch (ApplicationCreateException $e) {
                /** ошибка - капчу не вывести */
            } ?>
        </div>
    <?}?>

    <div class="feedback-landing__form-info">
        Обратите внимание, что все поля данной формы должны быть заполнены
    </div>

    <div class="feedback-landing__btn-form">
        <button type="submit" class="landing-btn">Отправить</button>
    </div>
</form>