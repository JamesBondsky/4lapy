<?php

use FourPaws\Decorators\SvgDecorator;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<div class="b-header-info__item b-header-info__item--person">
    <div class="b-header-info__item b-header-info__item--person">
        <a class="<?= ($component->getMode()
                       === FourPawsAuthFormComponent::MODE_FORM) ? 'b-link js-open-popup js-open-popup' : 'b-header-info__link js-open-popover' ?>"
           href="javascript:void(0);"
           title="Войти"<?= ($component->getMode()
                             === FourPawsAuthFormComponent::MODE_FORM) ? ' data-popup-id="authorization"' : '' ?>>
                <span class="b-icon">
                <?= new SvgDecorator('icon-person', 16, 16) ?>
            </span>
            <span class="b-header-info__inner">Войти</span>
            <span class="b-icon b-icon--header b-icon--left-3">
                <?= new SvgDecorator('icon-arrow-down', 10, 12) ?>
            </span>
        </a>
        <?php
        if ($component->getMode() === FourPawsAuthFormComponent::MODE_FORM) { ?>
            <section class="b-popup-pick-city b-popup-pick-city--authorization js-popup-section"
                     data-popup="authorization">
                <a class="b-popup-pick-city__close b-popup-pick-city__close--authorization js-close-popup"
                   href="javascript:void(0);"
                   title="Закрыть"></a>
                <div class="b-registration b-registration--popup-authorization">
                    <header class="b-registration__header">
                        <h1 class="b-title b-title--h1 b-title--registration">Авторизация</h1>
                    </header>
                    <form class="b-registration__form js-form-validation"
                          data-utl="/ajax/user/auth/login/"
                          method="post">
                        <input type="hidden" name="action" value="login">
                        <div class="b-input-line b-input-line--popup-authorization">
                            <div class="b-input-line__label-wrapper">
                                <label class="b-input-line__label" for="tel-email-authorization">Телефон или
                                                                                                 эл.почта</label>
                            </div>
                            <input class="b-input b-input--registration-form"
                                   type="text"
                                   id="tel-email-authorization"
                                   name="LOGIN"
                                   placeholder="" />
                        </div>
                        <div class="b-input-line b-input-line--popup-authorization">
                            <div class="b-input-line__label-wrapper">
                                <label class="b-input-line__label" for="password-authorization">Пароль</label>
                                <a class="b-link-gray b-link-gray--label"
                                   href="/personal/forgot-password/"
                                   title="Забыли пароль?">Забыли пароль?</a>
                            </div>
                            <input class="b-input b-input--registration-form"
                                   type="password"
                                   id="password-authorization"
                                   name="PASSWORD"
                                   placeholder="" />
                        </div>
                        <button class="b-button b-button--social b-button--full-width b-button--popup-authorization">
                            Войти
                        </button>
                        <span class="b-registration__else b-registration__else--authorization">или</span>
                        <?php $APPLICATION->IncludeComponent(
                            'bitrix:socserv.auth.form',
                            'socserv_auth',
                            [
                                'AUTH_SERVICES' => $arResult['AUTH_SERVICES'],
                                'AUTH_URL'      => $arResult['AUTH_URL'],
                                'POST'          => $arResult['POST'],
                            ],
                            $component,
                            ['HIDE_ICONS' => 'Y']
                        );
                        ?>
                        <div class="b-registration__new-user">Я новый покупатель.
                            <a class="b-link b-link--authorization b-link--authorization"
                               href="/personal/register/"
                               title="Зарегистрироваться"><span class="b-link__text b-link__text--authorization">Зарегистрироваться</span></a>
                        </div>
                    </form>
                </div>
            </section>
            <?php
        } else {
            $user = $component->getCurrentUserProvider()->getCurrentUser(); ?>
            <?php $APPLICATION->IncludeComponent(
                'bitrix:menu',
                'header.personal_menu',
                [
                    'COMPONENT_TEMPLATE'    => 'header.personal_menu',
                    'ROOT_MENU_TYPE'        => 'personal',
                    'MENU_CACHE_TYPE'       => 'A',
                    'MENU_CACHE_TIME'       => '360000',
                    'MENU_CACHE_USE_GROUPS' => 'N',
                    'MENU_CACHE_GET_VARS'   => [],
                    'MAX_LEVEL'             => '1',
                    'CHILD_MENU_TYPE'       => '',
                    'USE_EXT'               => 'N',
                    'DELAY'                 => 'N',
                    'ALLOW_MULTI_SELECT'    => 'N',
                ],
                false,
                ['HIDE_ICONS' => 'Y']
            );
        }
        ?>
    </div>
</div>
