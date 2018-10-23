<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var FourPawsFrontOfficeCardRegistrationComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 */

/** @var array $curPrintFields */
/** @var string $curFormId */

$errBlock = '<div class="form-page__message b-icon"><i class="icon icon-warning"></i><span class="text-h4 text-icon">%s</span></div>';

$attr = '';
$attr .= ' data-ajax-url="' . $componentPath . '/ajax.php"';
$attr .= ' data-result-container="#refreshingBlockContainer"';

$requiredMark = '<span style="color: red;">*</span>';
$notRequiredMark = '';
$showAuthButton = false;
?>
    <form class="form-page mb-l registration-form" action=""<?= $attr ?> method="post" id="<?= $curFormId ?>">
        <div>
            <input type="hidden" name="formName" value="customerRegistration">
            <input type="hidden" name="action" value="postForm">
            <input type="hidden" name="sessid" value="<?= bitrix_sessid() ?>">
            <?php

            if ($arResult['STEP'] >= 1) {
                // Поле: Мобильный телефон (10 знаков без 7 или 8 в формате 9ХХХХХХХХХ)
                $fieldName = 'phone';
                $fieldId = $curFormId.'__'.$fieldName;
                $fieldMeta = $curPrintFields[$fieldName];
                $value = $fieldMeta['VALUE'];
                $attr = '';
                $attr .= $fieldMeta['READONLY'] ? ' readonly="readonly"' : '';
                $attr .= ' maxlength="10"';
                $errMess = '';
                /** @var Bitrix\Main\Error $error */
                $error = $fieldMeta['ERROR'];
                if ($error) {
                    switch ($error->getCode()) {
                        case 'exception':
                            $errMess = $error->getMessage();
                            break;
                        case 'empty':
                            $errMess = 'Не задан номер телефона';
                            break;
                        case 'not_valid':
                            $errMess = 'Телефон задан в неверном формате';
                            break;
                        case 'already_registered':
                            $errMess = 'Данный телефонный номер есть в базе данных сайта, авторизоваться под пользователем?';
                            $showAuthButton = true;
                            break;
                        default:
                            $errMess = '[' . $error->getCode() . '] ' . $error->getMessage();
                            break;
                    }
                }
                ?>
                <div class="form-page__field-wrap">
                    <label for="<?= $fieldId ?>" class="form-page__label">
                        Мобильный телефон (10 знаков без 7 или 8 в формате 9ХХХХХХХХХ)
                        <?= ($fieldMeta['REQUIRED'] ? $requiredMark : $notRequiredMark) ?>
                    </label>
                    <input id="<?= $fieldId ?>"
                           name="<?= $fieldName ?>"
                           value="<?= $value ?>"<?= $attr ?>
                           class="form-page__field mb-l"
                           type="text">
                    <?php
                    if ($errMess) {
                        echo sprintf($errBlock, $errMess);
                    }
                    ?>
                </div>
                <?php
            }

            if ($arResult['STEP'] >= 2) {
                ?>
                <input type="hidden" name="contactId" value="<?= $curPrintFields['contactId']['VALUE'] ?>">
                <?php
            }

            if ($arResult['STEP'] >= 3) {
                // Поле: Фамилия
                $fieldName = 'lastName';
                $fieldId = $curFormId.'__'.$fieldName;
                $fieldMeta = $curPrintFields[$fieldName];
                $value = $fieldMeta['VALUE'];
                $attr = '';
                $attr .= $fieldMeta['READONLY'] ? ' readonly="readonly"' : '';
                $attr .= ' maxlength="100"';
                $errMess = '';
                /** @var Bitrix\Main\Error $error */
                $error = $fieldMeta['ERROR'];
                if ($error) {
                    switch ($error->getCode()) {
                        case 'exception':
                            $errMess = $error->getMessage();
                            break;
                        case 'empty':
                            $errMess = 'Данные не заданы';
                            break;
                        case 'not_valid':
                            $errMess = 'Введите корректные данные';
                            break;
                        default:
                            $errMess = '[' . $error->getCode() . '] ' . $error->getMessage();
                            break;
                    }
                }
                ?>
                <div class="form-page__field-wrap">
                    <label for="<?= $fieldId ?>" class="form-page__label">
                        Фамилия
                        <?= ($fieldMeta['REQUIRED'] ? $requiredMark : $notRequiredMark) ?>
                    </label>
                    <input id="<?= $fieldId ?>"
                           name="<?= $fieldName ?>"
                           value="<?= $value ?>"<?= $attr ?>
                           class="form-page__field mb-l"
                           type="text">
                    <?= ($errMess ? sprintf($errBlock, $errMess) : '') ?>
                </div>
                <?php

                // Поле: Имя
                $fieldName = 'firstName';
                $fieldId = $curFormId.'__'.$fieldName;
                $fieldMeta = $curPrintFields[$fieldName];
                $value = $fieldMeta['VALUE'];
                $attr = '';
                $attr .= $fieldMeta['READONLY'] ? ' readonly="readonly"' : '';
                $attr .= ' maxlength="100"';
                $errMess = '';
                /** @var Bitrix\Main\Error $error */
                $error = $fieldMeta['ERROR'];
                if ($error) {
                    switch ($error->getCode()) {
                        case 'exception':
                            $errMess = $error->getMessage();
                            break;
                        case 'empty':
                            $errMess = 'Данные не заданы';
                            break;
                        case 'not_valid':
                            $errMess = 'Введите корректные данные';
                            break;
                        default:
                            $errMess = '[' . $error->getCode() . '] ' . $error->getMessage();
                            break;
                    }
                }
                ?>
                <div class="form-page__field-wrap">
                    <label for="<?= $fieldId ?>" class="form-page__label">
                        Имя
                        <?= ($fieldMeta['REQUIRED'] ? $requiredMark : $notRequiredMark) ?>
                    </label>
                    <input id="<?= $fieldId ?>"
                           name="<?= $fieldName ?>"
                           value="<?= $value ?>"<?= $attr ?>
                           class="form-page__field mb-l"
                           type="text">
                    <?= ($errMess ? sprintf($errBlock, $errMess) : '') ?>
                </div>
                <?php

                // Поле: Отчество
                $fieldName = 'secondName';
                $fieldId = $curFormId.'__'.$fieldName;
                $fieldMeta = $curPrintFields[$fieldName];
                $value = $fieldMeta['VALUE'];
                $attr = '';
                $attr .= $fieldMeta['READONLY'] ? ' readonly="readonly"' : '';
                $attr .= ' maxlength="100"';
                $errMess = '';
                /** @var Bitrix\Main\Error $error */
                $error = $fieldMeta['ERROR'];
                if ($error) {
                    switch ($error->getCode()) {
                        case 'exception':
                            $errMess = $error->getMessage();
                            break;
                        case 'empty':
                            $errMess = 'Данные не заданы';
                            break;
                        case 'not_valid':
                            $errMess = 'Введите корректные данные';
                            break;
                        default:
                            $errMess = '[' . $error->getCode() . '] ' . $error->getMessage();
                            break;
                    }
                }
                ?>
                <div class="form-page__field-wrap">
                    <label for="<?= $fieldId ?>" class="form-page__label">
                        Отчество
                        <?= ($fieldMeta['REQUIRED'] ? $requiredMark : $notRequiredMark) ?>
                    </label>
                    <input id="<?= $fieldId ?>"
                           name="<?= $fieldName ?>"
                           value="<?= $value ?>"<?= $attr ?>
                           class="form-page__field mb-l"
                           type="text">
                    <?= ($errMess ? sprintf($errBlock, $errMess) : '') ?>
                </div>
                <?php

                // Поле: Укажите свой пол
                $fieldName = 'genderCode';
                $fieldId = $curFormId.'__'.$fieldName;
                $fieldMeta = $curPrintFields[$fieldName];
                $value = $fieldMeta['VALUE'];
                $attr = '';
                $optAttr = $fieldMeta['READONLY'] ? ' disabled="disabled"' : '';
                $errMess = '';
                /** @var Bitrix\Main\Error $error */
                $error = $fieldMeta['ERROR'];
                if ($error) {
                    switch ($error->getCode()) {
                        case 'exception':
                            $errMess = $error->getMessage();
                            break;
                        case 'empty':
                            $errMess = 'Укажите пол';
                            break;
                        case 'not_valid':
                            $errMess = 'Значение задано некорректно';
                            break;
                        default:
                            $errMess = '[' . $error->getCode() . '] ' . $error->getMessage();
                            break;
                    }
                }
                $value = trim($value);
                $male = trim($component::EXTERNAL_GENDER_CODE_M);
                $female = trim($component::EXTERNAL_GENDER_CODE_F);
                ?>
                <div class="form-page__field-wrap">
                    <label for="<?= $fieldId ?>" class="form-page__label">
                        Пол
                        <?= ($fieldMeta['REQUIRED'] ? $requiredMark : $notRequiredMark) ?>
                    </label>
                    <select id="<?= $fieldId ?>" name="<?= $fieldName ?>">
                        <option<?=$optAttr?> value="">Укажите пол</option>
                        <option<?= ($value === $male ? ' selected="selected"' : $optAttr) ?> value="<?= $male ?>">Мужской</option>
                        <option<?= ($value === $female ? ' selected="selected"' : $optAttr) ?> value="<?= $female ?>">Женский</option>
                    </select>
                    <?= ($errMess ? sprintf($errBlock, $errMess) : '') ?>
                </div>
                <?php

                // Поле: Дата вашего рождения дд.мм.гггг
                $fieldName = 'birthDay';
                $fieldId = $curFormId.'__'.$fieldName;
                $fieldMeta = $curPrintFields[$fieldName];
                $value = $fieldMeta['VALUE'];
                $attr = '';
                $attr .= $fieldMeta['READONLY'] ? ' readonly="readonly"' : '';
                $attr .= ' maxlength="10"';
                $errMess = '';
                /** @var Bitrix\Main\Error $error */
                $error = $fieldMeta['ERROR'];
                if ($error) {
                    switch ($error->getCode()) {
                        case 'exception':
                            $errMess = $error->getMessage();
                            break;
                        case 'empty':
                            $errMess = 'Укажите дату рождения!';
                            break;
                        case 'not_valid':
                            $errMess = 'Дата указана в неверном формате';
                            break;
                        default:
                            $errMess = '[' . $error->getCode() . '] ' . $error->getMessage();
                            break;
                    }
                }

                ?>
                <div class="form-page__field-wrap">
                    <label for="<?= $fieldId ?>" class="form-page__label">
                        Дата рождения дд.мм.гггг
                        <?= ($fieldMeta['REQUIRED'] ? $requiredMark : $notRequiredMark) ?>
                    </label>
                    <input id="<?= $fieldId ?>"
                           name="<?= $fieldName ?>"
                           value="<?= $value ?>"<?= $attr ?>
                           class="form-page__field mb-l"
                           type="text">
                    <?= ($errMess ? sprintf($errBlock, $errMess) : '') ?>
                </div>
                <?php

                // Поле: Ваш email(поле необязательно для заполнения)
                $fieldName = 'email';
                $fieldId = $curFormId.'__'.$fieldName;
                $fieldMeta = $curPrintFields[$fieldName];
                $value = $fieldMeta['VALUE'];
                $attr = '';
                $attr .= $fieldMeta['READONLY'] ? ' readonly="readonly"' : '';
                $attr .= ' maxlength="100"';
                $errMess = '';
                /** @var Bitrix\Main\Error $error */
                $error = $fieldMeta['ERROR'];
                if ($error) {
                    $errMess = 'Неизвестная ошибка';
                    switch ($error->getCode()) {
                        case 'exception':
                            $errMess = $error->getMessage();
                            break;
                        case 'not_valid':
                            $errMess = 'E-mail задан некорректно';
                            break;
                        case 'already_registered':
                            $errMess = 'Пользователь с таким e-mail уже есть в системе.
                            Для продолжения введите другой e-mail или очистите поле';
                            break;
                    }
                }
                ?>
                <div class="form-page__field-wrap">
                    <label for="<?= $fieldId ?>" class="form-page__label">
                        Ваш email
                        <?= ($fieldMeta['REQUIRED'] ? $requiredMark : $notRequiredMark) ?>
                    </label>
                    <input id="<?= $fieldId ?>"
                           name="<?= $fieldName ?>"
                           value="<?= $value ?>"<?= $attr ?>
                           class="form-page__field mb-l _email"
                           type="text">
                    <?= ($errMess ? sprintf($errBlock, $errMess) : '') ?>
                </div><?php

                // сообщаем компоненту, что пользователя можно регистрировать в случае успешных проверок
                ?><input type="hidden" name="doCustomerRegistration" value="Y"><?php
            }

            // вывод ошибок регистрации карты
            if (isset($arResult['REGISTRATION_STATUS']) && $arResult['REGISTRATION_STATUS'] === 'ERROR') {
                $errMessages = [];
                foreach ($arResult['ERROR']['REGISTRATION'] as $errCode => $errMsg) {
                    $errMessages[] = $errCode !== '' ? '[' . $errCode . '] ' . $errMsg : $errMsg;
                }
                echo '<div class="form-page__field-wrap">';
                echo sprintf($errBlock, 'Ошибка регистрации пользователя:<br>' . implode('<br>', $errMessages));
                echo '</div>';
            }

            ?>
            <div class="form-page__submit-wrap">
                <?php
                if ($showAuthButton) {
                    ?>
                    <a href="javascript:void(0)"
                       data-user-id="<?= $arResult['REGISTERED_USER_ID'] ?>"
                       class="btn inline-block avatarAuth">
                        Авторизоваться
                    </a>
                    <a href="<?= $arParams['CURRENT_PAGE'] ?>" class="btn inline-block">Отказаться</a>
                    <?php
                } else {
                    $btnText = $arResult['STEP'] >= 2 ? 'Зарегистрировать' : 'ДАЛЕЕ';
                    ?>
                    <input class="form-page__btn inline-block ajaxSubmitButton" type="submit" value="<?= $btnText ?>">
                    <?php
                }
                ?>
                <p><?= $requiredMark ?>&nbsp;&mdash;&nbsp;обязательное поле</p>
            </div>
        </div>
    </form>
<?php
