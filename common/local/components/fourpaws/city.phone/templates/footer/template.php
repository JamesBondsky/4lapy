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
    <span class="b-footer-communication__item">
        <a class="b-footer-communication__link" href="tel:<?= $arResult['PHONE_FOR_URL'] ?>" title="<?= $arResult['PHONE'] ?>">
            <?= $arResult['PHONE'] ?>
        </a>
        <span class="b-footer-communication__description">(доступен до 21:00)</span>
    </span>
<?php $frame->beginStub() ?>
    <span class="b-footer-communication__item">
        <a class="b-footer-communication__link" href="tel:<?= $arResult['DEFAULT_PHONE_FOR_URL'] ?>" title="<?= $arResult['DEFAULT_PHONE'] ?>">
            <?= $arResult['DEFAULT_PHONE'] ?>
        </a>
        <span class="b-footer-communication__description">(доступен до 21:00)</span>
    </span>
<? $frame->end() ?>
