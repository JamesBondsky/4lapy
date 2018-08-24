<?php

use Bitrix\Main\SystemException;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\ReCaptchaBundle\Service\ReCaptchaService;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\NotFoundException;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\UserPasswordService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
try {
    $container = Application::getInstance()->getContainer();
} catch (ApplicationCreateException $e) {
    ShowMessage($e->getMessage());
}
$reCaptchaService = $container->get(ReCaptchaService::class);
if ($arResult['POST']['FORGOT']) {
    if (check_email($arResult['POST']['MAIL'])) {
        try {
            if ($reCaptchaService->checkCaptcha()) {
                $userPasswordService = $container->get(UserPasswordService::class);
                $userRepository = $container->get(UserRepository::class);
                $users = $userRepository->findOneByEmail($arResult['POST']['MAIL']);
                if($users) {
                    /** @var User $user */
                    $user = current($users);
                    if($user->getGroups()->count() === 1 && $user->getGroups()->first()->getCode() === 'FRONT_OFFICE_USERS') {
                        $userPasswordService->resetPassword($user->getId());
                        unset($arResult['POST']['FORGOT']);
                        //
                        // нужно сделать переключалку дивов и зависимость от POST
                        //
                        ShowMessage(['MESSAGE' => 'Проверьте почту, скоро вам прийдет новый пароль', 'TYPE' => 'OK']);
                    } else {
                        ShowMessage('Вам запрещено сбрасывать пароль');
                    }
                } else {
                    ShowMessage('Пользователь с таким E-mail не найден');
                }
            } else {
                ShowMessage('Нажмите капчу');
            }
        } catch (SystemException | NotFoundException $e) {
            ShowMessage($e->getMessage());
        }
    } else {
        ShowMessage('Введите корректный E-mail');
    }
}
ShowMessage($arParams['~AUTH_RESULT']);
ShowMessage($arResult['ERROR_MESSAGE']);

?>

<div class="bx-auth"<?= $arResult['POST']['FORGOT'] ? ' style="display: none;"' : ''; ?>>
    <?php if ($arResult['AUTH_SERVICES']): ?>
        <div class="bx-auth-title"><?php echo GetMessage('AUTH_TITLE') ?></div>
    <? endif ?>
    <div class="bx-auth-note"><?= GetMessage('AUTH_PLEASE_AUTH') ?></div>

    <form name="form_auth" method="post" target="_top" action="<?= $arResult['AUTH_URL'] ?>">

        <input type="hidden" name="AUTH_FORM" value="Y"/>
        <input type="hidden" name="TYPE" value="AUTH"/>
        <?php if (strlen($arResult['BACKURL']) > 0): ?>
            <input type="hidden" name="backurl" value="<?= $arResult['BACKURL'] ?>"/>
        <? endif ?>
        <?php
        foreach ($arResult['POST'] as $key => $value) {
            if ($key === 'g-recaptcha-response' || $key === 'FORGOT') {
                continue;
            }
            ?>
            <input type="hidden" name="<?= $key ?>" value="<?= $value ?>"/>
            <?php
        }
        ?>

        <table class="bx-auth-table">
            <tr>
                <td class="bx-auth-label"><?= GetMessage('AUTH_LOGIN') ?></td>
                <td><input class="bx-auth-input"
                           type="text"
                           name="USER_LOGIN"
                           maxlength="255"
                           value="<?= $arResult['LAST_LOGIN'] ?>"/></td>
            </tr>
            <tr>
                <td class="bx-auth-label"><?= GetMessage('AUTH_PASSWORD') ?></td>
                <td><input class="bx-auth-input"
                           type="password"
                           name="USER_PASSWORD"
                           maxlength="255"
                           autocomplete="off"/>
                    <?php if ($arResult['SECURE_AUTH']): ?>
                        <span class="bx-auth-secure"
                              id="bx_auth_secure"
                              title="<?php echo GetMessage('AUTH_SECURE_NOTE') ?>"
                              style="display:none">
					<div class="bx-auth-secure-icon"></div>
				</span>
                        <noscript>
				<span class="bx-auth-secure" title="<?php echo GetMessage('AUTH_NONSECURE_NOTE') ?>">
					<div class="bx-auth-secure-icon bx-auth-secure-unlock"></div>
				</span>
                        </noscript>
                        <script type="text/javascript">
                            document.getElementById('bx_auth_secure').style.display = 'inline-block';
                        </script>
                    <? endif ?>
                </td>
            </tr>
            <?php if ($arResult['CAPTCHA_CODE']): ?>
                <tr>
                    <td></td>
                    <td><input type="hidden" name="captcha_sid" value="<?php echo $arResult['CAPTCHA_CODE'] ?>"/>
                        <img src="/bitrix/tools/captcha.php?captcha_sid=<?php echo $arResult['CAPTCHA_CODE'] ?>"
                             width="180"
                             height="40"
                             alt="CAPTCHA"/></td>
                </tr>
                <tr>
                    <td class="bx-auth-label"><?php echo GetMessage('AUTH_CAPTCHA_PROMT') ?>:</td>
                    <td><input class="bx-auth-input"
                               type="text"
                               name="captcha_word"
                               maxlength="50"
                               value=""
                               size="15"/></td>
                </tr>
            <? endif; ?>
            <?php if ($arResult['STORE_PASSWORD'] === 'Y'): ?>
                <tr>
                    <td></td>
                    <td><input type="checkbox"
                               id="USER_REMEMBER"
                               name="USER_REMEMBER"
                               value="Y"/><label for="USER_REMEMBER">&nbsp;<?= GetMessage('AUTH_REMEMBER_ME') ?></label>
                    </td>
                </tr>
            <? endif ?>
            <tr>
                <td></td>
                <td class="authorize-submit-cell"><input type="submit"
                                                         name="Login"
                                                         value="<?= GetMessage('AUTH_AUTHORIZE') ?>"/></td>
            </tr>
            <tr>
                <td></td>
                <td><a href="#" data-show="js-forgot">Забыли пароль?</a></td>
            </tr>
        </table>

        <?php if ($arParams['NOT_SHOW_LINKS'] !== 'Y'): ?>
            <noindex>
                <p>
                    <a href="<?= $arResult['AUTH_FORGOT_PASSWORD_URL'] ?>"
                       rel="nofollow"><?= GetMessage('AUTH_FORGOT_PASSWORD_2') ?></a>
                </p>
            </noindex>
        <? endif ?>

        <?php if ($arParams['NOT_SHOW_LINKS'] !== 'Y' && $arResult['NEW_USER_REGISTRATION'] === 'Y'
            && $arParams['AUTHORIZE_REGISTRATION'] !== 'Y'): ?>
            <noindex>
                <p>
                    <a href="<?= $arResult['AUTH_REGISTER_URL'] ?>"
                       rel="nofollow"><?= GetMessage('AUTH_REGISTER') ?></a><br/>
                    <?= GetMessage('AUTH_FIRST_ONE') ?>
                </p>
            </noindex>
        <? endif ?>

    </form>
</div>
<div class="js-forgot" <?= $arResult['POST']['FORGOT'] ? '' : ' style="display: none;"'; ?>>

    <p><br>Введите E-mail и мы вышлем вам новый пароль</p>
    <form name="forgot_password" method="post" action="">
        <table class="bx-auth-table">
            <tr>
                <td class="bx-auth-label">Логин:</td>
                <td>
                    <input class="bx-auth-input"
                           type="text"
                           name="MAIL"
                           maxlength="255"
                           value="<?= $arResult['LAST_LOGIN'] ?>" title="E-mail"/>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <?= $reCaptchaService->getCaptcha(); ?>
                </td>
            </tr>
            <tr>
                <td></td>
                <td><input type="submit"
                           name="FORGOT"
                           value="Выслать новый пароль"/></td>
            </tr>
            <tr>
                <td></td>
                <td><a href="#" data-show="bx-auth">Авторизоваться</a></td>
            </tr>
        </table>

    </form>
</div>

<script type="text/javascript">
    <?if (strlen($arResult['LAST_LOGIN']) > 0):?>
    try {
        document.form_auth.USER_PASSWORD.focus();
    } catch (e) {
    }
    <?else:?>
    try {
        document.form_auth.USER_LOGIN.focus();
    } catch (e) {
    }
    <?endif?>
</script>

<?php if ($arResult['AUTH_SERVICES']): ?>
    <?php
    $APPLICATION->IncludeComponent('bitrix:socserv.auth.form',
        '',
        [
            'AUTH_SERVICES' => $arResult['AUTH_SERVICES'],
            'CURRENT_SERVICE' => $arResult['CURRENT_SERVICE'],
            'AUTH_URL' => $arResult['AUTH_URL'],
            'POST' => $arResult['POST'],
            'SHOW_TITLES' => $arResult['FOR_INTRANET'] ? 'N' : 'Y',
            'FOR_SPLIT' => $arResult['FOR_INTRANET'] ? 'Y' : 'N',
            'AUTH_LINE' => $arResult['FOR_INTRANET'] ? 'N' : 'Y',
        ],
        $component,
        ['HIDE_ICONS' => 'Y']);
    ?>
<? endif ?>
