<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CatalogSectionComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 */

?><form class="form-page mb-l" action="" method="post">
    <div>
        <input type="hidden" name="formName" value="cardRegistration">
        <input type="hidden" name="action" value="postForm">
        <input type="hidden" name="sessid" value="<?=bitrix_sessid()?>">


        <input type="hidden" name="step" value="<?=$arResult['STEP']?>">


        <p class="text-h3 mb-l">Введите штрих-код Вашей карты:</p>
        <div class="form-page__field-wrap">
            <label for="cardNumber" class="form-page__label">Номер вашей карты</label><?php
            $attr = '';
            $attr .= ' id="cardNumber"';
            $attr .= ' maxlength="13"';
            $attr .= ' name="cardNumber"';
            $attr .= $arResult['STEP'] > 1 ? ' readonly="readonly"' : '';
            ?><input<?=$attr?> class="form-page__field mb-l" type="text" value="<?=$arResult['PRINT_VALUES']['cardNumber']?>"><?
            if($arResult['WAS_POSTED'] && !empty($arResult['ERROR']['cardNumber'])) {
                /** @var Bitrix\Main\Error $error */
                $error = $arResult['ERROR']['cardNumber'];
                $mess = 'Неизвестная ошибка';
                switch ($error->getCode()) {
                    case 'exception':
                        $mess = $error->getMessage();
                        break;
                    case 'empty':
                        $mess = 'Пожалуйста, укажите номер карты';
                        break;
                    case 'not_found':
                        $mess = 'Увы, мы не нашли этой карты :-(';
                        break;
                    case 'activated':
                        $mess = 'Увы, но карта уже активирована :-(';
                        break;
                }
                ?><div class="form-page__message b-icon">
                    <i class="icon icon-warning"></i>
                    <span class="text-h4 text-icon"><?=$mess?></span>
                </div><?php
            }
        ?></div>


        <div class="form-page__submit-wrap">
            <input class="form-page__btn inline-block" type="submit" value="ДАЛЕЕ">
        </div>
    </div>
</form><?php
