<?php

use Bitrix\Main\Application;
use FourPaws\CatalogBundle\Service\CatalogLandingService;
use FourPaws\Helpers\ProtectorHelper;
use FourPaws\App\Application as App;
use FourPaws\KioskBundle\Service\KioskService;
use FourPaws\ReCaptchaBundle\Service\ReCaptchaInterface;


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$request = Application::getInstance()
    ->getContext()
    ->getRequest();
$backUrl = $arResult['BACK_URL'] ?? $request->get('backurl');

$isKioskMode = $arResult['KIOSK'] || KioskService::isKioskMode();

$userData = $_SESSION['socServiceParams'];

/** @var string $phone */ ?>
<div class="b-registration__content b-registration__content--moiety b-registration__content--step">
    <div class="b-step-form">Шаг <span>1</span> из <span>2</span>
    </div>
    <form class="b-registration__form js-form-validation js-registration-form"
          id="reg-step1-form"
          data-url="/ajax/user/auth/register-r/"
          method="post">
        <input type="hidden" name="action" value="get">
        <input type="hidden" name="step" value="sendSmsCode">
        <input type="hidden" name="userData[name]" value="<?= $userData['name'] ?>">
        <input type="hidden" name="userData[last_name]" value="<?= $userData['last_name'] ?>">
        <input type="hidden" name="userData[gender]" value="<?= $userData['gender'] ?>">
        <input type="hidden" name="userData[birthday]" value="<?= $userData['birthday'] ?>">
        <input type="hidden" name="userData[ex_id]" value="<?= $userData['ex_id'] ?>">
        <input type="hidden" name="userData[token]" value="<?= $userData['token'] ?>">
        <?php if (!CatalogLandingService::isLandingPage()) { ?>
            <input type="hidden" name="backurl" value="<?= $backUrl ?>">
        <?php } ?>
        <div class="b-input-line">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="mobile-number-1">Мобильный телефон</label>
            </div>
            <div class="b-input b-input--registration-form">
                <input class="b-input__input-field b-input__input-field--registration-form"
                       type="tel"
                       name="phone"
                       value="<?= $phone ?>"
                       id="mobile-number-1"
                       placeholder=""/>
                <div class="b-error"><span class="js-message"></span></div>
            </div>
        </div>

        <?
        if (!$isKioskMode) {
            /** @var \FourPaws\ReCaptchaBundle\Service\ReCaptchaService $recaptchaService */
            $recaptchaService = App::getInstance()->getContainer()->get(ReCaptchaInterface::class);
            echo $recaptchaService->getCaptcha(' b-input-line', true);
        }
        ?>

        <button class="b-button b-button--social b-button--full-width">Отправить код</button>

        <? $token = ProtectorHelper::generateToken(ProtectorHelper::TYPE_REGISTER_SMS_SEND); ?>
        <input type="hidden" name="<?=$token['field']?>" value="<?=$token['token']?>">
    </form>
</div>
<section class="b-registration__additional-info b-registration__additional-info--step">
    <h3 class="b-registration__title-advantage">Зачем это нужно?</h3>
    <ul class="b-social-advantage">
        <li class="b-social-advantage__item">Для оперативной связи по поводу доставки</li>
        <li class="b-social-advantage__item">Для привязки бонусной карты</li>
        <li class="b-social-advantage__item">Телефон можно использовать как логин при входе</li>
    </ul>
</section>
