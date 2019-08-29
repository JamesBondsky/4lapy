<? use FourPaws\Decorators\SvgDecorator;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die; ?>

<?
/**
 * @var CBitrixComponentTemplate $this
 * @var array $arResult
 * @var CStampsBalanceComponent $component
 *
 * @global CMain $APPLICATION
 */
?>

<? if ($component->isAuthorized()): ?>
    <p>Вы авторизованы и на вашем счете - <?= $component->getActiveStampsCount() ?> марок</p>
<? else: ?>
    <a class="b-link js-open-popup js-toggle-popover-mobile-header"
       href="javascript:void(0);"
       title="Войти" data-popup-id="authorization">
        <span>Войти</span>
    </a>
<? endif; ?>
