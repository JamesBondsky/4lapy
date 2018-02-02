<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var \CBitrixComponentTemplate $this
 *
 * @var array $arParams
 * @var array $arResult
 * @var array $templateData
 *
 * @var string $componentPath
 * @var string $templateName
 * @var string $templateFile
 * @var string $templateFolder
 *
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 */

$this->setFrameMode(true);
?>
<?php $frame = $this->createFrame()->begin() ?>
<span class="b-header__phone-short-header"><?= $arResult['PHONE'] ?></span>
<?php $frame->beginStub() ?>
<span class="b-header__phone-short-header"><?= $arResult['DEFAULT_PHONE'] ?></span>
<? $frame->end() ?>
