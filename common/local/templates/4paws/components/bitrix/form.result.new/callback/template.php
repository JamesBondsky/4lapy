<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
if (!\is_array($arResult['QUESTIONS']) || empty($arResult['QUESTIONS'])) {
    return;
}

use FourPaws\Decorators\SvgDecorator;

?>
<div class="b-contact b-contact--call-me js-second-phone-content">
    <div class="b-contact__header">
        <a class="b-contact__back-link js-back-to-first-cont" href="javascript:void(0)" title="">
            <span class="b-icon b-icon--arrow-back">
                <?= new SvgDecorator('icon-arrow-back', 15, 13) ?>
            </span>Другие варианты связи
        </a>
    </div>
    <hr class="b-contact__hr" />
    <?= $arResult['FORM_DESCRIPTION'] ?>
    <form class="b-contact__form js-form-validation js-phone-query" data-url="/ajax/form/callback/add/" method="post">
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
                        $fieldName = 'form_'.$question['STRUCTURE'][0]['FIELD_TYPE'].'_'.$question['STRUCTURE'][0]['ID'];
                        $type = 'text';
                        if ($fieldSid === 'phone') {
                            $type = 'tel';
                        } ?>
                        <div class="b-input b-input--recall<?= $type === 'tel' ? ' js-phone-mask' : '' ?>">
                            <input class="b-input__input-field b-input__input-field--recall<?= $type === 'tel' ? ' js-phone-mask' : '' ?>"
                                   type="<?= $type ?>"
                                   id="id-recall-<?= $fieldSid ?>"
                                   placeholder="<?= $question['CAPTION'] ?>"
                                   name="<?= $fieldName ?>"
                                   value="<?= $arResult['CUR_USER'][$fieldSid] ?>" />
                            <div class="b-error"><span class="js-message"></span></div>
                        </div>
                        <?php
                        break;
                    case 'dropdown':
                        $fieldName = 'form_'.$question['STRUCTURE'][0]['FIELD_TYPE'].'_'.$fieldSid;
                        ?>
                        <div class="b-select b-select--recall">
                            <select class="b-select__block b-select__block--recall" name="<?= $fieldName ?>">
                                <option value="" disabled="disabled">выберите</option>
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
                            <div class="b-error"><span class="js-message"></span></div>
                        </div>
                        <?php
                        break;
                } ?>
                <?php
            }
        } ?>
        <button class="b-button b-button--full-width b-button--recall"
                type="submit"
                name="web_form_submit"
                value="Перезвоните мне">Перезвоните мне
        </button>
    </form>
</div>
<div class="b-contact b-contact--call-done js-third-phone-content">
    <dl class="b-phone-pair b-phone-pair--bottom">
        <dt class="b-phone-pair__phone js-call-me-title">Спасибо!</dt>
        <dd class="b-phone-pair__description js-call-me-text">Наш оператор свяжется с вами в указанное время</dd>
    </dl>
</div>