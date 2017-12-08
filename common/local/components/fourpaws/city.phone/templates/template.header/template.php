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

$this->setFrameMode(true);
?>

<?php $frame = $this->createFrame()->begin() ?>
    <dl class="b-phone-pair">
        <dt class="b-phone-pair__phone">
            <a class="b-phone-pair__link" href="tel:<?= $arResult['PHONE_FOR_URL'] ?>" title="<?= $arResult['PHONE'] ?>">
                <?= $arResult['PHONE'] ?>
            </a>
        </dt>
        <?php /* @todo склонять название города? */ ?>
        <dd class="b-phone-pair__description">Для г.<?= $arResult['CITY_NAME'] ?>. <?= $arResult['WORKING_HOURS'] ?></dd>
    </dl>
<?php $frame->beginStub() ?>
    <dl class="b-phone-pair">
        <dt class="b-phone-pair__phone">
            <a class="b-phone-pair__link" href="tel:<?= $arResult['DEFAULT_PHONE_FOR_URL'] ?>" title="<?= $arResult['DEFAULT_PHONE'] ?>">
                <?= $arResult['DEFAULT_PHONE'] ?>
            </a>
        </dt>
        <?php /* @todo склонять название города? */ ?>
        <dd class="b-phone-pair__description">Для г.<?= $arResult['DEFAULT_CITY_NAME'] ?>. <?= $arResult['DEFAULT_WORKING_HOURS'] ?></dd>
    </dl>
<? $frame->end() ?>
