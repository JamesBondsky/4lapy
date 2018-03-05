<?php
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

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Catalog\Model\OftenSeek;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var ArrayCollection $items */
$items = $arResult['ITEMS'];

if($items->isEmpty())
{
    return;
} ?>
<dl class="b-catalog-filter__row">
    <dt class="b-catalog-filter__label">Часто ищут:</dt>
    <dd class="b-catalog-filter__block">
        <?php /** @var OftenSeek $item */
        foreach ($items as $item) { ?>
            <a class="b-link b-link--filter" href="<?=$item->getLink()?>" title="<?= $item->getName() ?>">
                <?= $item->getName() ?>
            </a>
        <?php } ?>
    </dd>
</dl>
<div class="b-line b-line--sort-desktop"></div>
