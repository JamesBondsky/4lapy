<?php

use Bitrix\Main\Application;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain                                     $APPLICATION
 * @var array                                        $arParams
 * @var array                                        $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate                     $this
 * @var string                                       $templateName
 * @var string                                       $componentPath
 */

/*if ($arResult['CAN_ACCESS'] !== 'Y') {
    ShowError('При обработке запроса произошла ошибка: отказано в доступе');
    return;
}*/
/*if ($arResult['IS_AVATAR_AUTHORIZED'] === 'Y') {
    echo '<br><p>Вы уже находитесь в режиме "аватар". <a href="'.$arParams['LOGOUT_URL'].'">Выйти из режима</a>.</p>';
    return;
}*/

if ((int)Application::getInstance()->getContext()->getRequest()->getQuery('promoId')) {
    echo '<div id="refreshingBlockContainer">';
}

// форма
include __DIR__ . '/inc.form.php';

// Пользователь
echo '<div class="lk-container">';
if (!empty($arResult['USER_INFO'])) {
    $curPrintFields = [
	    'id' => [
            'VALUE'	=> $arResult['USER_INFO']['ID'],
	    ],
	    'promoId' => [
	    	'VALUE'	=> $arResult['USER_INFO']['UF_FESTIVAL_USER_ID'],
		    'READONLY' => true,
	    ],
        'firstName' => [
            'VALUE'	=> $arResult['USER_INFO']['UF_NAME'],
            'REQUIRED' => true,
        ],
        'lastName' => [
            'VALUE'	=> $arResult['USER_INFO']['UF_SURNAME'],
        ],
        'phone' => [
            'VALUE'	=> $arResult['USER_INFO']['UF_PHONE'],
            'REQUIRED' => true,
        ],
        'email' => [
            'VALUE'	=> $arResult['USER_INFO']['UF_EMAIL'],
        ],
        'passport' => [
            'VALUE'	=> $arResult['USER_INFO']['UF_PASSPORT'],
        ],
        /*'cardNumber' => [
            'VALUE'	=> $arResult['USER_INFO']['CARD_NUMBER'],
            'READONLY' => true,
        ],*/
    ];
    $requiredMark = '<span style="color: red;">*</span>';
    $formName = 'festUserUpdate';
	?>
	<form class="form-page mb-l" action="" method="post" data-name="<?= $formName ?>">
		<div>
	        <input type="hidden" name="formName" value="<?= $formName ?>">
	        <input type="hidden" name="action" value="userUpdate">
		    <input type="hidden" name="sessid" value="<?= bitrix_sessid() ?>">

			<input type="hidden" name="id" value="<?= $curPrintFields['id']['VALUE'] ?>">
			<?


			// Поле: ID
            $fieldName = 'promoId';
            $fieldMeta = $curPrintFields[$fieldName];
            $value     = $fieldMeta['VALUE'];
            $attr      = '';
            $attr      .= $fieldMeta['READONLY'] ? ' readonly="readonly"' : '';
            $attr      .= ' maxlength="13"';
            $errMess   = '';
            /** @var Bitrix\Main\Error $error */
            $error = $fieldMeta['ERROR'];
            if ($error) {
                switch ($error->getCode()) {
                    case 'empty':
                        $errMess = 'Пожалуйста, укажите ID';
                        break;
                    case 'not_valid':
                    case 'incorrect_value':
                        $errMess = 'ID задан в неверном формате';
                        break;
                    case 'runtime':
                        $errMess = $error->getMessage();
                        break;
                    default:
                        $errMess = '[' . $error->getCode() . '] ' . $error->getMessage();
                        break;
                }
            }
            ?>
            <div class="form-page__field-wrap">
                <label for="<?= $fieldName ?>" class="form-page__label">ID</label>
                <input id="<?= $fieldName ?>"
                       name="<?= $fieldName ?>"
                       value="<?= $value ?>"<?= $attr ?>
                       class="form-page__field mb-l"
                       type="text">
                <?= ($errMess ? sprintf($errBlock, $errMess) : '') ?>
            </div>
            <?


			// Поле: Имя
            $fieldName = 'firstName';
            $fieldId = $fieldName;
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
	        <?


	        // Поле: Фамилия
            $fieldName = 'lastName';
            $fieldId = $fieldName;
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
	        <?


	        // Поле: Мобильный телефон (10 знаков без 7 или 8 в формате 9ХХХХХХХХХ)
            $fieldName = 'phone';
            $fieldId = $fieldName;
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
                       type="number"
                       pattern="\d{0,10}"
                >
                <?php
                if ($errMess) {
                    echo sprintf($errBlock, $errMess);
                }
                ?>
            </div>
            <?php


            // Поле: Ваш email(поле необязательно для заполнения)
            $fieldName = 'email';
            $fieldId = $fieldName;
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
            </div>
	        <?


	        // Поле: Номер паспорта
            $fieldName = 'passport';
            $fieldMeta = $curPrintFields[$fieldName];
            $value     = $fieldMeta['VALUE'];
            $attr      = '';
            $attr      .= $fieldMeta['READONLY'] ? ' readonly="readonly"' : '';
            $attr      .= ' maxlength="5"';
            $errMess   = '';
            /** @var Bitrix\Main\Error $error */
            $error = $fieldMeta['ERROR'];
            if ($error) {
                switch ($error->getCode()) {
                    case 'empty':
                        $errMess = 'Пожалуйста, укажите номер паспорта';
                        break;
                    case 'not_valid':
                    case 'incorrect_value':
                        $errMess = 'Номер паспорта задан в неверном формате';
                        break;
                    case 'runtime':
                        $errMess = $error->getMessage();
                        break;
                    default:
                        $errMess = '[' . $error->getCode() . '] ' . $error->getMessage();
                        break;
                }
            }
            ?>
            <div class="form-page__field-wrap">
                <label for="<?= $fieldName ?>" class="form-page__label">Номер паспорта</label>
                <input id="<?= $fieldName ?>"
                       name="<?= $fieldName ?>"
                       value="<?= $value ?>"<?= $attr ?>
                       class="form-page__field mb-l"
                       type="number"
                       pattern="\d{0,5}"
                >
                <?= ($errMess ? sprintf($errBlock, $errMess) : '') ?>
            </div>
	        <?


	        /*// Поле: Номер карты
            $fieldName = 'cardNumber';
            $fieldId = $fieldName;
            $fieldMeta = $curPrintFields[$fieldName];
            $value = $fieldMeta['VALUE'];
            $attr = '';
            $attr .= $fieldMeta['READONLY'] ? ' readonly="readonly"' : '';
            $attr .= ' maxlength="100"';
            $errMess = '';
            /** @var Bitrix\Main\Error $error *//*
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
                    Номер карты
                    <?= ($fieldMeta['REQUIRED'] ? $requiredMark : $notRequiredMark) ?>
                </label>
                <input id="<?= $fieldId ?>"
                       name="<?= $fieldName ?>"
                       value="<?= $value ?>"<?= $attr ?>
                       class="form-page__field mb-l"
                       type="text">
                <?= ($errMess ? sprintf($errBlock, $errMess) : '') ?>
            </div>
	        <?*/


            // вывод общих ошибок, если есть
            if (!empty($arResult['ERROR']['EXEC'])) {
                $errMessages = [];
                foreach ($arResult['ERROR']['EXEC'] as $errName => $errMsg) {
                    $errMessages[] =
                        $errName && !in_array($errName, ['emptySearchParams']) ? '[' . $errName . '] '
                                                                                 . $errMsg : $errMsg;
                }
                echo '<div class="form-page__field-wrap">';
                echo sprintf($errBlock, 'Ошибки запроса данных:<br>' . implode('<br>', $errMessages));
                echo '</div>';
            }


            if (!$arResult['FIELD_VALUES']['search_by_passport']) {
			    $btnText = 'Сохранить';
			    ?>
			    <div class="form-page__submit-wrap">
				    <input id="ajaxSubmitButton" class="form-page__btn inline-block" type="submit" value="<?= $btnText ?>">
				    <p><?= $requiredMark ?>&nbsp;&mdash;&nbsp;обязательное поле</p>
			    </div>
	            <?
            }
            ?>
		</div>
    </form>
    <?
    /*?>
    <table class="user-list">
        <thead>
        <tr>
            <th class="user-list__full-name">Ф.И.О.</th>
            <th class="user-list__phone">Телефон</th>
            <th class="user-list__card-number">Номер карты</th>
            <th class="user-list__bd">Род.</th>
            <th class="user-list__auth">Действие</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $rowClass = 'even';
        foreach ($arResult['USERS_LIST'] as $user) {
            $rowClass = $rowClass === 'even' ? 'odd' : 'even';
            ?>
            <tr class="user-list__item-row <?= $rowClass ?>">
                <td class="user-list__full-name">
                    <div class="cell-value"><?= ($user['_FULL_NAME_'] ? htmlspecialcharsbx($user['_FULL_NAME_']) : '-') ?></div>
                </td>
                <td class="user-list__phone">
                    <div class="cell-value"><?= ($user['_PERSONAL_PHONE_NORMALIZED_'] ? htmlspecialcharsbx($user['_PERSONAL_PHONE_NORMALIZED_']) : '-') ?></div>
                </td>
                <td class="user-list__card-number">
                    <div class="cell-value"><?= ($user['UF_DISCOUNT_CARD'] ? htmlspecialcharsbx($user['UF_DISCOUNT_CARD']) : '-') ?></div>
                </td>
                <td class="user-list__bd">
                    <div class="cell-value"><?= ($user['PERSONAL_BIRTHDAY'] ? htmlspecialcharsbx($user['PERSONAL_BIRTHDAY']) : '-') ?></div>
                </td>
                <td class="user-list__auth">
                    <div class="cell-value">
                        <span class="_action-auth" data-id="<?= $user['ID'] ?>">авторизоваться</span>
                    </div>
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <?php */
} elseif (empty($arResult['ERROR'])) {
    echo '<p>По запросу ничего не найдено</p>';
}
echo '</div>';

if ((int)Application::getInstance()->getContext()->getRequest()->getQuery('promoId')) {
    echo '</div>';

    require_once __DIR__ . '/initScript.php';
    ?>
	<script>
        $('html, body').animate(
            {
                scrollTop: $(document).height()
            },
            200
        );
        window.history.replaceState({}, null, window.location.pathname);
	</script>
    <?
}
