<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
if (!\is_array($arResult['QUESTIONS']) || empty($arResult['QUESTIONS'])) {
    return;
}

use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;

?>
<h2 class="b-title b-title--feedback-form"><?= $arResult['FORM_DESCRIPTION'] ?></h2>
<form class="b-feedback-page__form js-form-validation js-feedback-form"
      name="<?= $arResult['arForm']['SID'] ?>" data-url="/ajax/form/feedback/add/"
      method="post"
      enctype="multipart/form-data">
    <?= bitrix_sessid_post() ?>
    <input name="WEB_FORM_ID" value="<?= $arResult['arForm']['ID'] ?>" type="hidden">

    <?php
    foreach ($arResult['QUESTIONS'] as $fieldSid => $question) {
        if ($question['STRUCTURE'][0]['FIELD_TYPE'] === 'hidden') {
            echo $question['HTML_CODE'];
        } else {
            switch ($question['STRUCTURE'][0]['FIELD_TYPE']) {
                case 'text':
                case 'email':
                    $fieldName =
                        'form_' . $question['STRUCTURE'][0]['FIELD_TYPE'] . '_' . $question['STRUCTURE'][0]['ID'];
                    $type = 'text';
                    if ($fieldSid === 'email') {
                        $type = 'email';
                    } elseif ($fieldSid === 'phone') {
                        $type = 'tel';
                    } ?>
                    <div class="b-input-line b-input-line--feedback-page">
                        <div class="b-input-line__label-wrapper">
                            <label class="b-input-line__label"
                                   for="feedback-<?= $fieldSid ?>"><?= $question['CAPTION'] ?></label>
                        </div>
                        <div class="b-input b-input--registration-form">
                            <input class="b-input__input-field b-input__input-field--registration-form"
                                   type="<?= $type ?>"
                                   id="feedback-<?= $fieldSid ?>"
                                   placeholder=""
                                   name="<?= $fieldName ?>" <?= $question['REQUIRED'] === 'Y' ? ' required' : '' ?>
                                   value="<?= $arResult['CUR_USER'][$fieldSid] ?>"/>
                            <div class="b-error"><span class="js-message" title=""></span></div>
                        </div>
                    </div>
                    <?php
                    break;
                case 'dropdown':
                    $fieldName = 'form_' . $question['STRUCTURE'][0]['FIELD_TYPE'] . '_' . $fieldSid;
                    ?>
                    <div class="b-input-line b-input-line--feedback-page">
                        <div class="b-input-line__label-wrapper">
                            <label class="b-input-line__label"
                                   for="feedback-<?= $fieldSid ?>"><?= $question['CAPTION'] ?></label>
                        </div>
                        <div class="b-select b-select--recall b-select--feedback-page">
                            <select id="feedback-<?= $fieldSid ?>"
                                    class="b-select__block b-select__block--recall b-select__block--feedback-page"
                                    name="<?= $fieldName ?>">
                                <option value="" disabled="disabled" selected="selected">выберите</option>
                                <?php
                                if (\is_array($arResult['arAnswers'][$fieldSid])
                                    && !empty($arResult['arAnswers'][$fieldSid])) {
                                    foreach ($arResult['arAnswers'][$fieldSid] as $selectItem) {
                                        ?>
                                        <option value="<?= $selectItem['ID'] ?>"><?= $selectItem['MESSAGE'] ?></option>
                                        <?php
                                    }
                                } ?>
                            </select>
                            <div class="b-error"><span class="js-message" title=""></span></div>
                        </div>
                    </div>
                    <?php
                    break;
                case 'textarea':
                    $fieldName =
                        'form_' . $question['STRUCTURE'][0]['FIELD_TYPE'] . '_' . $question['STRUCTURE'][0]['ID'];
                    ?>
                    <div class="b-input-line b-input-line--textarea">
                        <div class="b-input-line__label-wrapper">
                            <label class="b-input-line__label"
                                   for="feedback-<?= $fieldSid ?>"><?= $question['CAPTION'] ?></label>
                        </div>
                        <div class="b-input b-input--registration-form">
                            <textarea
                                    class="b-input__input-field b-input__input-field--textarea b-input__input-field--registration-form"
                                    id="feedback-<?= $fieldSid ?>" name="<?= $fieldName ?>"></textarea>
                            <div class="b-error"><span class="js-message" title=""></span></div>
                        </div>
                    </div>
                    <?php
                    break;
                case 'file':
                    $fieldName =
                        'form_' . $question['STRUCTURE'][0]['FIELD_TYPE'] . '_' . $question['STRUCTURE'][0]['ID'];
                    ?>
                    <div class="b-input-line b-input-line--file">
                        <div class="b-input-line__comment-block">
                            <div class="b-input b-input--feedback-page js-no-valid">
                                <input type="hidden" name="MAX_FILE_SIZE" value="<?= 2 * 1024 * 1024 ?>"/>
                                <input class="b-input__input-field b-input__input-field--feedback-page js-no-valid"
                                       type="file"
                                       id="feedback-<?= $fieldSid ?>"
                                       placeholder=""
                                       name="<?= $fieldName ?>"/>
                            </div>
                            <label class="b-input-line__label b-input-line__label--feedback-page"
                                   for="feedback-<?= $fieldSid ?>">
                                <?= $question['CAPTION'] ?>
                            </label>
                            <div class="b-error"><span class="js-message" title=""></span></div>
                            <span class="b-input-line__comment b-input-line__comment--feedback-page">
                                Объем файла не более 2 Мб.<br/> Допустимые форматы файла: jpg, png, doc, docx
                            </span>
                        </div>
                    </div>
                    <?php
                    break;
            } ?>
            <?php
        }
    }
    if ($arResult['isUseCaptcha']) {
        ?>
        <div class="b-feedback-page__capcha">
            <?php try {
                echo App::getInstance()->getContainer()->get('recaptcha.service')->getCaptcha();
            } catch (ApplicationCreateException $e) {
                /** ошибка - капчу не вывести */
            } ?>
        </div>
        <?php
    }
    ?>
    <button class="b-button b-button--feedback-page" type="submit" name="web_form_submit" value="Отправить">
        Отправить
    </button>
</form>