<?php
/**
 * @var CMain $APPLICATION
 */

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

/** @noinspection PhpIncludeInspection */
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

try {
    $APPLICATION->IncludeComponent(
        'fourpaws:catalog',
        '',
        [
            'IBLOCK_ID'         => IblockUtils::getIblockId(
                IblockType::CATALOG,
                IblockCode::PRODUCTS
            ),
            'SEF_FOLDER'        => '/catalog/',
            'SEF_URL_TEMPLATES' => [
                'sections' => '',
                'section'  => '#SECTION_CODE_PATH#/',
                'element'  => '#SECTION_CODE_PATH#/#ELEMENT_CODE#/',
            ],
        ]
    );
} catch (IblockNotFoundException $e) {
    LoggerFactory::create('catalog', 'catalog')
        ->error(sprintf('Error while get catalog iblock id: %s', $e->getMessage()));
    LocalRedirect('/');
}


/** @noinspection PhpIncludeInspection */
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
