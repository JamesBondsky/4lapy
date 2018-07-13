<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\LocationBundle\LocationService;

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
 */ ?>
<dl class="b-phone-pair">
    <dt class="b-phone-pair__phone">
        <a class="b-phone-pair__link" href="tel:<?= $arResult['PHONE_FOR_URL'] ?>" title="<?= $arResult['PHONE'] ?>">
            <?= $arResult['PHONE'] ?>
        </a>
    </dt>
    <dd class="b-phone-pair__description">
        Для <?= $arResult['LOCATION']['TYPE']['CODE'] === LocationService::TYPE_CITY ? 'г.' : '' ?><?= $arResult['CITY_NAME'] ?>
        . <?= $arResult['WORKING_HOURS'] ?></dd>
</dl>
