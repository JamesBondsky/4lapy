<?php

use Symfony\Component\Templating\PhpEngine;

global $APPLICATION;

/**
 * @var string    $sectionId
 * @var PhpEngine $view
 */

$APPLICATION->IncludeComponent(
    'fourpaws:catalog.articles',
    '',
    [
        'COUNT'      => 4,
        'SECTION_ID' => $sectionId,
    ]
);
