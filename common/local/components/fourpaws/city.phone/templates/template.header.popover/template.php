<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var \CBitrixComponentTemplate $this
 *
 * @var array                     $arParams
 * @var array                     $arResult
 * @var array                     $templateData
 *
 * @var string                    $componentPath
 * @var string                    $templateName
 * @var string                    $templateFile
 * @var string                    $templateFolder
 *
 * @global CUser                  $USER
 * @global CMain                  $APPLICATION
 * @global CDatabase              $DB
 */

use FourPaws\Decorators\SvgDecorator;

$this->setFrameMode(true);
?>

<?php $frame = $this->createFrame()->begin() ?>
    <a class="b-header-info__link js-open-popover" href="javascript:void(0);" title="<?= $arResult['PHONE'] ?>">
        <span class="b-icon">
            <?= new SvgDecorator('icon-phone-dark', 16, 16) ?>
        </span>
        <span class="b-header-info__inner">
            <?= $arResult['PHONE'] ?>
        </span>
        <span class="b-icon b-icon--header b-icon--left-3">
            <?= new SvgDecorator('icon-arrow-down', 10, 12) ?>
        </span>
    </a>
<?php $frame->beginStub() ?>
    <a class="b-header-info__link js-open-popover" href="javascript:void(0);" title="<?= $arResult['DEFAULT_PHONE'] ?>">
        <span class="b-icon">
            <?= new SvgDecorator('icon-phone-dark', 16, 16) ?>
        </span>
        <span class="b-header-info__inner">
            <?= $arResult['DEFAULT_PHONE'] ?>
        </span>
        <span class="b-icon b-icon--header b-icon--left-3">
            <?= new SvgDecorator('icon-arrow-down', 10, 12) ?>
        </span>
    </a>
<? $frame->end() ?>
