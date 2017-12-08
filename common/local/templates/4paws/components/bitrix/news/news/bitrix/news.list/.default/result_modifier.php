<?php

use FourPaws\BitrixOrm\Model\CropImageDecorator;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

foreach ($arResult['ITEMS'] as $key => &$item) {
    $image = new CropImageDecorator($item['PREVIEW_PICTURE']);
    if($key === 0) {
        $image->setCropWidth(630)->setCropHeight(210);
    }
    else{
        $image->setCropWidth(305)->setCropHeight(120);
    }
    $item['PREVIEW_PICTURE']['SRC'] = $image;
}
unset($item);