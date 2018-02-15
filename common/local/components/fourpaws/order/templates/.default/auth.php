<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\ReCaptcha\ReCaptchaService;
use FourPaws\SaleBundle\Entity\OrderPropertyVariant;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Service\OrderPropertyService;

/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 */

/** @var OrderStorage $storage */
$storage = $arResult['STORAGE'];

try {
    $serviceContainer = Application::getInstance()->getContainer();
} catch (ApplicationCreateException $e) {
    return;
}

/** @var OrderPropertyService $orderPropertyService */
$orderPropertyService = $serviceContainer->get(OrderPropertyService::class);
/** @var ReCaptchaService $recaptchaService */
$recaptchaService = $serviceContainer->get('recaptcha.service');

$communicationWays = $orderPropertyService->getPropertyVariants($orderPropertyService->getPropertyByCode('COM_WAY'))
                                          ->filter(
                                              function (OrderPropertyVariant $variant) {
                                                  return in_array(
                                                      $variant->getValue(),
                                                      [
                                                          OrderPropertyService::COMMUNICATION_PHONE,
                                                          OrderPropertyService::COMMUNICATION_SMS,
                                                      ],
                                                      true
                                                  );
                                              }
                                          );

/** @var OrderPropertyVariant $currentCommWay */
$currentCommWay = $communicationWays[$storage->getCommunicationWay()];

?>
<div class="b-container">
    <h1 class="b-title b-title--h1 b-title--order">
        <?php $APPLICATION->ShowTitle() ?>
    </h1>
    <div class="b-order js-order-whole-block">
        <div class="b-tab-list">
            <ul class="b-tab-list__list">
                <li class="b-tab-list__item active"><span class="b-tab-list__step">Шаг </span>1. Контактные данные
                </li>
                <li class="b-tab-list__item"><span class="b-tab-list__step">Шаг </span>2. Выбор доставки
                </li>
                <li class="b-tab-list__item"><span class="b-tab-list__step">Шаг </span>3. Выбор оплаты
                </li>
                <li class="b-tab-list__item">Завершение
                </li>
            </ul>
        </div>
        <div class="b-order__block">
            <div class="b-order__content js-order-content-block">
                <article class="b-order-contacts">
                    <header class="b-order-contacts__header">
                        <h2 class="b-title b-title--order-tab">
                            Контактные данные для оформления
                        </h2>
                    </header>
                    <form class="b-order-contacts__form js-form-validation"
                          id="order-step"
                          method="post"
                          data-url="<?= $arResult['URL']['AUTH_VALIDATION'] ?>">
                        <div class="b-input-line js-small-input">
                            <div class="b-input-line__label-wrapper">
                                <label class="b-input-line__label" for="order-name">
                                    Имя
                                </label>
                                <span class="b-input-line__require">(обязательно)</span>
                            </div>
                            <div class="b-input b-input--registration-form">
                                <input class="b-input__input-field b-input__input-field--registration-form"
                                       type="text"
                                       id="order-name"
                                       placeholder=""
                                       name="name"
                                       value="<?= $storage->getName() ?>"
                                       data-url="">
                                <div class="b-error">
                                    <span class="js-message"></span>
                                </div>
                            </div>
                        </div>
                        <div class="b-input-line">
                            <div class="b-input-line__label-wrapper js-information-comment">
                                <label class="b-input-line__label" for="order-phone">Мобильный телефон
                                </label><span class="b-input-line__require">(обязательно)</span><a class="b-information-link b-information-link--input js-popover-information-open"
                                                                                                   href="javascript:void(0);"
                                                                                                   title="">
                                    <span class="b-information-link__icon">i</span>
                                    <div class="b-popover-information b-popover-information--input js-popover-information">
                                    </div>
                                </a>
                            </div>
                            <div class="b-input-line__comment-block js-comment-wrapper">
                                <div class="b-input b-input--registration-form js-this-comment-desktop">
                                    <input class="b-input__input-field b-input__input-field--registration-form js-this-comment-desktop"
                                           type="tel"
                                           id="order-phone"
                                           placeholder=""
                                           name="phone"
                                           value="<?= $storage->getPhone() ?>"
                                           data-url="">
                                    <div class="b-error">
                                        <span class="js-message"></span>
                                    </div>
                                </div>
                                <span class="b-input-line__comment js-comment">Для проверки статуса заказов на сайте</span>
                            </div>
                        </div>
                        <div class="b-input-line">
                            <div class="b-input-line__label-wrapper js-information-comment">
                                <label class="b-input-line__label" for="order-email">
                                    Эл. почта
                                </label>
                                <a class="b-information-link b-information-link--input js-popover-information-open"
                                   href="javascript:void(0);"
                                   title=""> <span class="b-information-link__icon">i</span>
                                    <div class="b-popover-information b-popover-information--input js-popover-information">
                                    </div>
                                </a>
                            </div>
                            <div class="b-input-line__comment-block js-comment-wrapper">
                                <div class="b-input b-input--registration-form js-this-comment-desktop">
                                    <input class="b-input__input-field b-input__input-field--registration-form js-this-comment-desktop"
                                           type="email"
                                           id="order-email"
                                           placeholder=""
                                           name="email"
                                           value="<?= $storage->getEmail() ?>"
                                           data-url="">
                                    <div class="b-error">
                                        <span class="js-message"></span>
                                    </div>
                                </div>
                                <span class="b-input-line__comment js-comment">Для проверки статуса заказов и для рассылки новостей и акций</span>
                            </div>
                        </div>
                        <div class="b-order-contacts__add-layout">
                            <div class="b-order-contacts__link-block">
                                <a class="b-link b-link--add-phone js-order-add-phone-link"
                                   href="javascript:void(0);"
                                   title=""
                                    <?= $storage->getAltPhone() ? 'style="display:none"' : '' ?>>
                                    Дополнительный телефон
                                </a>
                                <a class="b-information-link b-information-link--additional-telephone-order js-additional-telephone js-popover-information-open"
                                   href="javascript:void(0);"
                                   title=""> <span class="b-information-link__icon">i</span>
                                    <div class="b-popover-information b-popover-information--additional-telephone-order js-popover-information">
                                    </div>
                                </a>
                                <span class="b-order-contacts__text js-additional-telephone-info">Если мы не дозвонимся по основному телефону</span>
                            </div>
                            <div class="b-order-contacts__layout js-order-add-phone"
                                <?= $storage->getAltPhone() ? 'style="display:block"' : '' ?>>
                                <div class="b-input-line js-no-valid">
                                    <div class="b-input-line__label-wrapper js-information-comment">
                                        <label class="b-input-line__label" for="order-phone-dop">Дополнительный телефон
                                        </label>
                                        <a class="b-information-link b-information-link--input js-popover-information-open"
                                           href="javascript:void(0);"
                                           title=""> <span class="b-information-link__icon">i</span>
                                            <div class="b-popover-information b-popover-information--input js-popover-information">
                                            </div>
                                        </a>
                                    </div>
                                    <div class="b-input-line__comment-block js-comment-wrapper">
                                        <div class="b-input b-input--registration-form js-this-comment-desktop">
                                            <input class="b-input__input-field b-input__input-field--registration-form js-this-comment-desktop"
                                                   type="tel"
                                                   id="order-phone-dop"
                                                   placeholder=""
                                                   name="altPhone"
                                                   data-url=""
                                                   value="<?= $storage->getAltPhone() ?>">
                                            <div class="b-error">
                                                <span class="js-message"></span>
                                            </div>
                                        </div>
                                        <span class="b-input-line__comment js-comment">Если мы не дозвонимся по основному телефону</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="b-input-line">
                            <div class="b-input-line__label-wrapper"><span class="b-input-line__label">Как с вами связаться для подтверждения заказа</span>
                            </div>
                            <?php /** @var OrderPropertyVariant $commWay */ ?>
                            <?php foreach ($communicationWays as $i => $commWay) { ?>
                                <?php
                                $isSelected = $currentCommWay && ($commWay->getValue() === $currentCommWay->getValue());
                                ?>
                                <div class="b-radio b-radio--tablet-big">
                                    <input class="b-radio__input"
                                           type="radio"
                                           name="order-confirm"
                                           id="order-<?= $commWay->getValue() ?>"
                                        <?= $isSelected ? 'checked="checked"' : '' ?>
                                           data-radio="<?= $i ?>">
                                    <label class="b-radio__label b-radio__label--tablet-big"
                                           for="order-<?= $commWay->getValue() ?>">
                                        <span class="b-radio__text-label"><?= $commWay->getName() ?></span>
                                    </label>
                                </div>
                            <?php } ?>
                        </div>
                        <?php if (!$storage->isCaptchaFilled()) { ?>
                            <div class="b-input-line">
                                <?= $recaptchaService->getCaptcha() ?>
                            </div>
                        <?php } ?>
                    </form>
                </article>
            </div>
            <?php include 'include/basket.php' ?>
        </div>

        <button class="b-button b-button--social b-button--next b-button--fixed-bottom js-order-next js-valid-out-sub">
            Далее
        </button>
    </div>
</div>
<div class="b-preloader b-preloader--fixed">
  <div class="b-preloader__spinner">
      <img class="b-preloader__image" src="/static/build/images/inhtml/spinner.svg" alt="spinner" title="">
  </div>
</div>
