<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use Sprint\Migration\Helpers\IblockHelper;

class IblockAddSubscribePrices20190326172741 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Добавляет инфоблок \"Скидка по подписке на доставку\"";

    public function up()
    {
        $helper = new HelperManager();

        $iblockId = $helper->Iblock()->addIblockIfNotExists(
            [
                'IBLOCK_TYPE_ID'     => 'catalog',
                'LID'                => 's1',
                'CODE'               => 'subscribe_prices',
                'NAME'               => 'Скидка по подписке на доставку',
                'ACTIVE'             => 'Y',
                'SORT'               => '500',
                'PICTURE'            => null,
                'DESCRIPTION'        => '',
                'DESCRIPTION_TYPE'   => 'text',
                'RSS_TTL'            => '24',
                'RSS_ACTIVE'         => 'Y',
                'RSS_FILE_ACTIVE'    => 'N',
                'RSS_FILE_LIMIT'     => null,
                'RSS_FILE_DAYS'      => null,
                'RSS_YANDEX_ACTIVE'  => 'N',
                'XML_ID'             => '',
                'TMP_ID'             => '507e7bffa0ceb3151f0ca2421689420e',
                'INDEX_ELEMENT'      => 'Y',
                'INDEX_SECTION'      => 'Y',
                'WORKFLOW'           => 'N',
                'BIZPROC'            => 'N',
                'SECTION_CHOOSER'    => 'L',
                'LIST_MODE'          => '',
                'RIGHTS_MODE'        => 'S',
                'SECTION_PROPERTY'   => null,
                'PROPERTY_INDEX'     => null,
                'VERSION'            => '2',
                'LAST_CONV_ELEMENT'  => '0',
                'SOCNET_GROUP_ID'    => null,
                'EDIT_FILE_BEFORE'   => '',
                'EDIT_FILE_AFTER'    => '',
                'SECTIONS_NAME'      => 'Разделы',
                'SECTION_NAME'       => 'Раздел',
                'ELEMENTS_NAME'      => 'Элементы',
                'ELEMENT_NAME'       => 'Элемент',
                'EXTERNAL_ID'        => '',
                'LANG_DIR'           => '/',
                'SERVER_NAME'        => '4lapy.ru',
            ]
        );

        $helper->Iblock()->addPropertyIfNotExists(
            $iblockId,
            [
                'NAME'               => 'Код региона',
                'ACTIVE'             => 'Y',
                'SORT'               => '500',
                'CODE'               => 'REGION_CODE',
                'DEFAULT_VALUE'      => '',
                'PROPERTY_TYPE'      => 'S',
                'ROW_COUNT'          => '1',
                'COL_COUNT'          => '30',
                'LIST_TYPE'          => 'L',
                'MULTIPLE'           => 'N',
                'XML_ID'             => '',
                'FILE_TYPE'          => '',
                'MULTIPLE_CNT'       => '5',
                'TMP_ID'             => null,
                'LINK_IBLOCK_ID'     => '0',
                'WITH_DESCRIPTION'   => 'N',
                'SEARCHABLE'         => 'N',
                'FILTRABLE'          => 'N',
                'IS_REQUIRED'        => 'Y',
                'VERSION'            => '1',
                'HINT'               => '',
            ]
        );

        $helper->Iblock()->addPropertyIfNotExists(
            $iblockId,
            [
                'NAME'               => 'Привязка к бренду',
                'ACTIVE'             => 'Y',
                'SORT'               => '500',
                'CODE'               => 'BRAND',
                'DEFAULT_VALUE'      => '',
                'PROPERTY_TYPE'      => 'E',
                'ROW_COUNT'          => '1',
                'COL_COUNT'          => '30',
                'LIST_TYPE'          => 'L',
                'MULTIPLE'           => 'Y',
                'XML_ID'             => '',
                'FILE_TYPE'          => '',
                'MULTIPLE_CNT'       => '5',
                'TMP_ID'             => null,
                'LINK_IBLOCK_ID'     => '0',
                'WITH_DESCRIPTION'   => 'N',
                'SEARCHABLE'         => 'N',
                'FILTRABLE'          => 'N',
                'IS_REQUIRED'        => 'N',
                'VERSION'            => '1',
                'USER_TYPE'          => 'EAutocomplete',
                'USER_TYPE_SETTINGS' => [
                    'IBLOCK_TYPE' => 'catalog',
                    'IBLOCK_ID'   => IblockUtils::getIblockId('catalog', IblockCode::BRANDS),
                ],
            ]
        );

        $helper->Iblock()->addPropertyIfNotExists(
            $iblockId,
            [
                'NAME'               => 'Коэффицент скидки (%)',
                'ACTIVE'             => 'Y',
                'SORT'               => '500',
                'CODE'               => 'PERCENT',
                'DEFAULT_VALUE'      => '',
                'PROPERTY_TYPE'      => 'S',
                'ROW_COUNT'          => '1',
                'COL_COUNT'          => '30',
                'LIST_TYPE'          => 'L',
                'MULTIPLE'           => 'N',
                'XML_ID'             => '',
                'FILE_TYPE'          => '',
                'MULTIPLE_CNT'       => '5',
                'TMP_ID'             => null,
                'LINK_IBLOCK_ID'     => '0',
                'WITH_DESCRIPTION'   => 'N',
                'SEARCHABLE'         => 'N',
                'FILTRABLE'          => 'N',
                'IS_REQUIRED'        => 'Y',
                'VERSION'            => '1',
                'HINT'               => '',
            ]
        );

        \CIBlock::SetPermission($iblockId, ["2" => "R"]);
    }

    public function down(){
        $helper = new HelperManager();

        //your code ...

    }

}
