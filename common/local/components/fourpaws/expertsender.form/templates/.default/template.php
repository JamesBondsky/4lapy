<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\Decorators\SvgDecorator;

/**
 * @var \CBitrixComponentTemplate $this
 *
 * @var array $arParams
 * @var array $arResult
 * @var array $templateData
 * @var FourPawsExpertsenderFormComponent $component
 *
 * @var string $componentPath
 * @var string $templateName
 * @var string $templateFile
 * @var string $templateFolder
 *
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 */

$component = $this->getComponent();
$email = '';
if ($component->getAuthorizationProvider()->isAuthorized()) {
    $email = $component->getCurrentUserProvider()->getCurrentUser()->getEmail();
}
?>
    <div class="b-feedback js-feedback-permutation" data-url="">
        <h4 class="b-feedback__header">Получайте рекомендации и выгодные предложения на почту</h4>
        <div class="b-form-inline b-form-inline--feedback">
            <form class="b-form-inline__form">
                <input class="b-input"
                       type="email"
                       name="email"
                       id="feedback"
                       placeholder="Адрес эл. почты"
                       value="<?= $email ?>"/>
                <button class="b-button b-button--form-inline b-button--feedback">
                <span class="b-icon">
                    <?= new SvgDecorator('icon-check', 16, 16) ?>
                </span>
                </button>
            </form>
        </div>
    </div>
<?php
