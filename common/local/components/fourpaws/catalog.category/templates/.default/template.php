<?php
/**
 * @var CBitrixComponentTemplate $this
 * @var CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var Category $category
 * @var Category $childCategory
 * @var Category[] $childCategories
 * @var Category $childChildCategory
 */

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\App\Templates\ViewsEnum;
use FourPaws\Catalog\Model\Category;
use FourPaws\Enum\IblockType;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\BannerSectionCode;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
