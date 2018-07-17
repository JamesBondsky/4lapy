<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var CBitrixComponentTemplate $this
 * @var array $arResult
 * @var FourPawsAuthFormComponent $component
 *
 * @global CMain $APPLICATION
 */

use FourPaws\App\Application;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;

$component = $this->getComponent(); ?>
<div class="b-header-info__item b-header-info__item--person">
    <a class="<?= ($component->getMode()
        === FourPawsAuthFormComponent::MODE_FORM) ? 'b-link js-open-popup' : 'b-header-info__link js-open-popover' ?>"
       href="javascript:void(0);"
       title="<?= $component->getMode()
       === FourPawsAuthFormComponent::MODE_FORM ? 'Войти' : $arResult['NAME'] ?>"<?= ($component->getMode() === FourPawsAuthFormComponent::MODE_FORM) ? ' data-popup-id="authorization"' : '' ?>>
                <span class="b-icon">
                <?= new SvgDecorator('icon-person', 16, 16) ?>
            </span>
        <span class="b-header-info__inner"><?= $component->getMode()
            === FourPawsAuthFormComponent::MODE_FORM ? 'Войти' : $arResult['NAME'] ?></span>
        <span class="b-icon b-icon--header b-icon--left-3">
                <?= new SvgDecorator('icon-arrow-down', 10, 12) ?>
            </span>
    </a>
    <?php if ($component->getMode() !== FourPawsAuthFormComponent::MODE_FORM) {
        $user = $component->getCurrentUserProvider()->getCurrentUser();

        $_GET['isAvatarAuthorized'] =
            Application::getInstance()
                ->getContainer()
                ->get(CurrentUserProviderInterface::class)
                ->isAvatarAuthorized();

        $APPLICATION->IncludeComponent(
            'bitrix:menu',
            'header.personal_menu',
            [
                'CACHE_SELECTED_ITEMS' => 'N',
                'MENU_CACHE_USE_USERS' => 'N',
                'COMPONENT_TEMPLATE' => 'header.personal_menu',
                'ROOT_MENU_TYPE' => 'personal',
                'MENU_CACHE_TYPE' => 'A',
                'MENU_CACHE_TIME' => '360000',
                'MENU_CACHE_USE_GROUPS' => 'Y',
                'MENU_CACHE_GET_VARS' => [
                    'isAvatarAuthorized',
                ],
                'MAX_LEVEL' => '1',
                'CHILD_MENU_TYPE' => '',
                'USE_EXT' => 'N',
                'DELAY' => 'N',
                'ALLOW_MULTI_SELECT' => 'N',
            ],
            false,
            ['HIDE_ICONS' => 'Y']
        );

        unset($_GET['isAvatarAuthorized']);
    }
    ?>
</div>
