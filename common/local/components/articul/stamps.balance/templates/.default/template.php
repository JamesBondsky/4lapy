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

<div class="toys-landing__marks">
    <? if ($component->isAuthorized()): ?>
        <div class="toys-landing__marks-cnt">
            У вас марок

            <div class="toys-landing__marks-cnt-number">
                <?= $component->getActiveStampsCount() ?>
                <img src="/upload/toys-landing/logo.png" alt="" width="19" height="19">
            </div>
        </div>
    <? else: ?>
        <div class="toys-landing__marks-login">
            Узнайте Ваш <br/> баланс марок

            <button class="toys-landing__marks-login-btn js-open-popup js-toggle-popover-mobile-header"
               href="javascript:void(0);"
               title="Войти" data-popup-id="authorization">
                <span>Войти</span>
            </button>
        </div>
    <? endif; ?>
</div>
