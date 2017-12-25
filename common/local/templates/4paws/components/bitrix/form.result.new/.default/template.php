<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
if (!\is_array($arResult['QUESTIONS']) || empty($arResult['QUESTIONS'])) {
    return;
}
use FourPaws\App\Application as App;
?>
<h2 class="b-title b-title--feedback-form"><?= $arResult['FORM_DESCRIPTION'] ?></h2>
<?php if ($arResult['isFormErrors'] === 'Y'): ?><?= $arResult['FORM_ERRORS_TEXT']; ?><? endif; ?>
<form class="b-feedback-page__form js-form-validation"
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
                                   name="<?= $fieldSid ?>" <?= $question['REQUIRED'] === 'Y' ? ' required' : '' ?>/>
                            <?php if (is_array($arResult['FORM_ERRORS'])
                                      && array_key_exists(
                                          $fieldSid,
                                          $arResult['FORM_ERRORS']
                                      )): ?>
                                <div class="b-error">
                        <span class="js-message"
                              title="<?= htmlspecialcharsbx($arResult['FORM_ERRORS'][$fieldSid]) ?>"></span></div>
                            <? endif; ?>
                        </div>
                    </div>
                    <?php
                    break;
                case 'dropdown':
                    ?>
                    <div class="b-input-line b-input-line--feedback-page">
                        <div class="b-input-line__label-wrapper">
                            <label class="b-input-line__label"
                                   for="feedback-<?= $fieldSid ?>"><?= $question['CAPTION'] ?></label>
                        </div>
                        <div class="b-select b-select--recall b-select--feedback-page">
                            <select id="feedback-<?= $fieldSid ?>"
                                    class="b-select__block b-select__block--recall b-select__block--feedback-page"
                                    name="<?= $fieldSid ?>">
                                <option value="" disabled="disabled" selected="selected">выберите</option>
                                <?php
                                if (\is_array($arResult['arAnswers'][$fieldSid])
                                    && !empty($arResult['arAnswers'][$fieldSid])) {
                                    foreach ($arResult['arAnswers'][$fieldSid] as $selectItem) { ?>
                                        <option value="<?= $selectItem['ID'] ?>"><?= $selectItem['MESSAGE'] ?></option>
                                        <?php
                                    }
                                } ?>
                            </select>
                            <?php if (is_array($arResult['FORM_ERRORS'])
                                      && array_key_exists(
                                          $fieldSid,
                                          $arResult['FORM_ERRORS']
                                      )): ?>
                                <div class="b-error">
                        <span class="js-message"
                              title="<?= htmlspecialcharsbx($arResult['FORM_ERRORS'][$fieldSid]) ?>"></span></div>
                            <? endif; ?>
                        </div>
                    </div>
                    <?php
                    break;
                case 'textarea':
                    ?>
                    <div class="b-input-line b-input-line--textarea">
                        <div class="b-input-line__label-wrapper">
                            <label class="b-input-line__label"
                                   for="feedback-<?= $fieldSid ?>"><?= $question['CAPTION'] ?></label>
                        </div>
                        <div class="b-input b-input--registration-form">
                        <textarea class="b-input__input-field b-input__input-field--textarea b-input__input-field--registration-form"
                                  id="feedback-<?= $fieldSid ?>" name="<?= $fieldSid ?>"></textarea>
                            <?php if (is_array($arResult['FORM_ERRORS'])
                                      && array_key_exists(
                                          $fieldSid,
                                          $arResult['FORM_ERRORS']
                                      )): ?>
                                <div class="b-error">
                        <span class="js-message"
                              title="<?= htmlspecialcharsbx($arResult['FORM_ERRORS'][$fieldSid]) ?>"></span></div>
                            <? endif; ?>
                        </div>
                    </div>
                    <?php
                    break;
                case 'file':
                    ?>
                    <div class="b-input-line b-input-line--file">
                        <div class="b-input-line__comment-block">
                            <div class="b-input b-input--feedback-page">
                                <input class="b-input__input-field b-input__input-field--feedback-page"
                                       type="file"
                                       id="feedback-<?= $fieldSid ?>"
                                       placeholder=""
                                       name="<?= $fieldSid ?>" />
                            </div>
                            <label class="b-input-line__label b-input-line__label--feedback-page"
                                   for="feedback-<?= $fieldSid ?>">
                                <?= $question['CAPTION'] ?>
                            </label>
                            <?php if (is_array($arResult['FORM_ERRORS'])
                                      && array_key_exists(
                                          $fieldSid,
                                          $arResult['FORM_ERRORS']
                                      )): ?>
                                <div class="b-error">
                        <span class="js-message"
                              title="<?= htmlspecialcharsbx($arResult['FORM_ERRORS'][$fieldSid]) ?>"></span></div>
                            <? endif; ?>
                            <span class="b-input-line__comment b-input-line__comment--feedback-page">
                                Объем файла не более 2 Мб.<br /> Допустимые форматы файла: jpg, png, doc, docx
                            </span>
                        </div>
                    </div>
                    <?php
                    break;
            }
            ?>
            <?php
        }
    }
    if ($arResult['isUseCaptcha'] === 'Y') {
        ?>
        <div class="b-feedback-page__capcha">
            <?= /** @noinspection PhpUnhandledExceptionInspection */
            App::getInstance()->getContainer()->get('recaptcha.service')->getCaptcha();?>
        </div>
        <?php
    }
    ?>
    <button class="b-button b-button--feedback-page" type="submit" name="web_form_submit" value="Отправить">Отправить
    </button>
</form>