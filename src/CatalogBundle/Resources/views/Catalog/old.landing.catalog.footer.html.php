<?php

use FourPaws\Catalog\Model\Category;
use Symfony\Component\Templating\PhpEngine;

/**
 * @var Category $category
 * @var PhpEngine $view
 */

global $faqCategoryId, $APPLICATION;
$faqCategoryId = $category->getUfFaqSection();

$APPLICATION->IncludeComponent(
    'bitrix:main.include',
    '',
    [
        'AREA_FILE_SHOW' => 'file',
        'PATH' => '/local/include/blocks/faq.php',
        'EDIT_TEMPLATE' => '',
    ],
    null,
    [
        'HIDE_ICONS' => 'Y',
    ]
);