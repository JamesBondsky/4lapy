<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class StampsProductsAddYouTubeLinkProp20190906151207 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = 'Добавление свойства "Ссылка на видео (YouTube)" в инфоблок "Марки: товары"';
    protected $propCode = 'VIDEO_LINK_YT';

    public function up()
    {
        $helper = new HelperManager();

        $helper->Iblock()->addIblockTypeIfNotExists([
            'ID'               => 'grandin',
            'SECTIONS'         => 'Y',
            'EDIT_FILE_BEFORE' => NULL,
            'EDIT_FILE_AFTER'  => NULL,
            'IN_RSS'           => 'N',
            'SORT'             => '1000',
            'LANG'             =>
                [
                    'ru' =>
                        [
                            'NAME'         => 'Лендинги',
                            'SECTION_NAME' => 'Разделы',
                            'ELEMENT_NAME' => 'Элементы',
                        ],
                ],
        ]);

        $iblockId = $helper->Iblock()->addIblockIfNotExists([
            'IBLOCK_TYPE_ID'     => 'grandin',
            'LID'                => 's1',
            'CODE'               => 'stamps_products',
            'NAME'               => 'Марки: товары',
            'ACTIVE'             => 'Y',
            'SORT'               => '500',
            'LIST_PAGE_URL'      => '#SITE_DIR#/grandin/index.php?ID=#IBLOCK_ID#',
            'DETAIL_PAGE_URL'    => '#SITE_DIR#/grandin/detail.php?ID=#ELEMENT_ID#',
            'SECTION_PAGE_URL'   => '#SITE_DIR#/grandin/list.php?SECTION_ID=#SECTION_ID#',
            'CANONICAL_PAGE_URL' => '',
            'PICTURE'            => NULL,
            'DESCRIPTION'        => '',
            'DESCRIPTION_TYPE'   => 'text',
            'RSS_TTL'            => '24',
            'RSS_ACTIVE'         => 'Y',
            'RSS_FILE_ACTIVE'    => 'N',
            'RSS_FILE_LIMIT'     => NULL,
            'RSS_FILE_DAYS'      => NULL,
            'RSS_YANDEX_ACTIVE'  => 'N',
            'XML_ID'             => '31',
            'TMP_ID'             => '1fe13be5008b4c8d9d1ddc4d678c8028',
            'INDEX_ELEMENT'      => 'Y',
            'INDEX_SECTION'      => 'Y',
            'WORKFLOW'           => 'N',
            'BIZPROC'            => 'N',
            'SECTION_CHOOSER'    => 'L',
            'LIST_MODE'          => '',
            'RIGHTS_MODE'        => 'S',
            'SECTION_PROPERTY'   => 'N',
            'PROPERTY_INDEX'     => 'N',
            'VERSION'            => '1',
            'LAST_CONV_ELEMENT'  => '0',
            'SOCNET_GROUP_ID'    => NULL,
            'EDIT_FILE_BEFORE'   => '',
            'EDIT_FILE_AFTER'    => '',
            'SECTIONS_NAME'      => 'Разделы',
            'SECTION_NAME'       => 'Раздел',
            'ELEMENTS_NAME'      => 'Элементы',
            'ELEMENT_NAME'       => 'Элемент',
            'EXTERNAL_ID'        => '31',
            'LANG_DIR'           => '/',
            'SERVER_NAME'        => '4lapy.ru',
        ]);

        $helper->Iblock()->addPropertyIfNotExists($iblockId, [
            'NAME'               => 'Ссылка на видео (YouTube)',
            'ACTIVE'             => 'Y',
            'SORT'               => '500',
            'CODE'               => $this->propCode,
            'DEFAULT_VALUE'      => '',
            'PROPERTY_TYPE'      => 'S',
            'ROW_COUNT'          => '1',
            'COL_COUNT'          => '30',
            'LIST_TYPE'          => 'L',
            'MULTIPLE'           => 'N',
            'XML_ID'             => '',
            'FILE_TYPE'          => '',
            'MULTIPLE_CNT'       => '5',
            'TMP_ID'             => NULL,
            'LINK_IBLOCK_ID'     => '0',
            'WITH_DESCRIPTION'   => 'N',
            'SEARCHABLE'         => 'N',
            'FILTRABLE'          => 'N',
            'IS_REQUIRED'        => 'N',
            'VERSION'            => '1',
            'USER_TYPE'          => NULL,
            'USER_TYPE_SETTINGS' => NULL,
            'HINT'               => '',
        ]);


    }

    public function down()
    {
        $helper = new HelperManager();

        $iblockId = IblockUtils::getIblockId(IblockType::GRANDIN, IblockCode::STAMPS_PRODUCTS);
        $helper->Iblock()->deletePropertyIfExists($iblockId, $this->propCode);
    }

}
