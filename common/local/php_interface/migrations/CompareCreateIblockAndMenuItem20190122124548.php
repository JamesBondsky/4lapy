<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;


class CompareCreateIblockAndMenuItem20190122124548 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Создаёт инфоблок \"Сравнение\" и пункт меню в шапке";

    public function up(){
        $this->log()->info('Создание инфоблока comparing');
        $iblockId = $this->getHelper()->Iblock()->addIblockIfNotExists([
            'IBLOCK_TYPE_ID'     => 'publications',
            'LID'                => 's1',
            'CODE'               => IblockCode::COMPARING,
            'NAME'               => 'Сравнение',
            'ACTIVE'             => 'Y',
            'SORT'               => '500',
            'LIST_PAGE_URL'      => '',
            'DETAIL_PAGE_URL'    => '',
            'SECTION_PAGE_URL'   => '',
            'CANONICAL_PAGE_URL' => '',
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
            'TMP_ID'             => '6E54F3BA80659FC74ED33DED41BF668A',
            'INDEX_ELEMENT'      => 'N',
            'INDEX_SECTION'      => 'N',
            'WORKFLOW'           => 'N',
            'BIZPROC'            => 'N',
            'SECTION_CHOOSER'    => 'L',
            'LIST_MODE'          => '',
            'RIGHTS_MODE'        => 'S',
            'SECTION_PROPERTY'   => 'N',
            'PROPERTY_INDEX'     => 'N',
            'VERSION'            => '2',
            'LAST_CONV_ELEMENT'  => '0',
            'SOCNET_GROUP_ID'    => null,
            'EDIT_FILE_BEFORE'   => '',
            'EDIT_FILE_AFTER'    => '',
            'SECTIONS_NAME'      => 'Группы сравнений',
            'SECTION_NAME'       => 'Группа сравнения',
            'ELEMENTS_NAME'      => 'Товары',
            'ELEMENT_NAME'       => 'Товар',
            'EXTERNAL_ID'        => '',
            'LANG_DIR'           => '/',
            'SERVER_NAME'        => 'stage.4lapy.adv.ru',
        ]);

        $this->log()->info('Установка свойств товаров');
        $fields = [
            [
                'NAME'               => 'Привязка к товару',
                'ACTIVE'             => 'Y',
                'SORT'               => '100',
                'CODE'               => 'PRODUCT',
                'DEFAULT_VALUE'      => '',
                'PROPERTY_TYPE'      => 'E',
                'ROW_COUNT'          => '1',
                'COL_COUNT'          => '30',
                'LIST_TYPE'          => 'L',
                'MULTIPLE'           => 'N',
                'XML_ID'             => '',
                'FILE_TYPE'          => '',
                'MULTIPLE_CNT'       => '5',
                'TMP_ID'             => null,
                'LINK_IBLOCK_ID'     => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS),
                'WITH_DESCRIPTION'   => 'N',
                'SEARCHABLE'         => 'N',
                'FILTRABLE'          => 'N',
                'IS_REQUIRED'        => 'Y',
                'VERSION'            => '2',
                'USER_TYPE'          => null,
                'USER_TYPE_SETTINGS' => null,
                'HINT'               => '',
            ],
            [
                'NAME'               => 'Артикул',
                'ACTIVE'             => 'Y',
                'SORT'               => '150',
                'CODE'               => 'ARTICLE',
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
                'LINK_IBLOCK_ID'     => '',
                'WITH_DESCRIPTION'   => 'N',
                'SEARCHABLE'         => 'N',
                'FILTRABLE'          => 'N',
                'IS_REQUIRED'        => 'Y',
                'VERSION'            => '2',
                'USER_TYPE'          => null,
                'USER_TYPE_SETTINGS' => null,
                'HINT'               => '',
            ],
            [
                'NAME'               => 'Наличие свежего мяса в корме (%)',
                'ACTIVE'             => 'Y',
                'SORT'               => '200',
                'CODE'               => 'FRESH_MEAT',
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
                'LINK_IBLOCK_ID'     => '',
                'WITH_DESCRIPTION'   => 'N',
                'SEARCHABLE'         => 'N',
                'FILTRABLE'          => 'N',
                'IS_REQUIRED'        => 'N',
                'VERSION'            => '2',
                'USER_TYPE'          => null,
                'USER_TYPE_SETTINGS' => null,
                'HINT'               => '',
            ],
            [
                'NAME'               => 'Количество белков животного происхождения (%)',
                'ACTIVE'             => 'Y',
                'SORT'               => '300',
                'CODE'               => 'PROTEIN',
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
                'LINK_IBLOCK_ID'     => '',
                'WITH_DESCRIPTION'   => 'N',
                'SEARCHABLE'         => 'N',
                'FILTRABLE'          => 'N',
                'IS_REQUIRED'        => 'N',
                'VERSION'            => '2',
                'USER_TYPE'          => null,
                'USER_TYPE_SETTINGS' => null,
                'HINT'               => '',
            ],
            [
                'NAME'               => 'Наличие минералов в хелатной форме',
                'ACTIVE'             => 'Y',
                'SORT'               => '400',
                'CODE'               => 'MINERALS',
                'DEFAULT_VALUE'      => '',
                'PROPERTY_TYPE'      => 'L',
                'ROW_COUNT'          => '1',
                'COL_COUNT'          => '30',
                'LIST_TYPE'          => 'C',
                'MULTIPLE'           => 'N',
                'XML_ID'             => '',
                'FILE_TYPE'          => '',
                'MULTIPLE_CNT'       => '5',
                'TMP_ID'             => null,
                'LINK_IBLOCK_ID'     => '',
                'WITH_DESCRIPTION'   => 'N',
                'SEARCHABLE'         => 'N',
                'FILTRABLE'          => 'N',
                'IS_REQUIRED'        => 'N',
                'VERSION'            => '2',
                'USER_TYPE'          => null,
                'USER_TYPE_SETTINGS' => null,
                'HINT'               => '',
                'VALUES'             => [
                    [
                        "VALUE" => "Да",
                        "DEF" => "N",
                        "SORT" => "200"
                    ]
                ]
            ],
            [
                'NAME'               => 'Наличие злаков',
                'ACTIVE'             => 'Y',
                'SORT'               => '500',
                'CODE'               => 'CEREALS',
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
                'LINK_IBLOCK_ID'     => '',
                'WITH_DESCRIPTION'   => 'N',
                'SEARCHABLE'         => 'N',
                'FILTRABLE'          => 'N',
                'IS_REQUIRED'        => 'N',
                'VERSION'            => '2',
                'USER_TYPE'          => null,
                'USER_TYPE_SETTINGS' => null,
                'HINT'               => '',
            ],
            [
                'NAME'               => 'Полный состав',
                'ACTIVE'             => 'Y',
                'SORT'               => '550',
                'CODE'               => 'COMPOSITION',
                'DEFAULT_VALUE'      => '',
                'PROPERTY_TYPE'      => 'S',
                'ROW_COUNT'          => '5',
                'COL_COUNT'          => '30',
                'LIST_TYPE'          => 'L',
                'MULTIPLE'           => 'N',
                'XML_ID'             => '',
                'FILE_TYPE'          => '',
                'MULTIPLE_CNT'       => '5',
                'TMP_ID'             => null,
                'LINK_IBLOCK_ID'     => '',
                'WITH_DESCRIPTION'   => 'N',
                'SEARCHABLE'         => 'N',
                'FILTRABLE'          => 'N',
                'IS_REQUIRED'        => 'N',
                'VERSION'            => '2',
                'USER_TYPE'          => null,
                'USER_TYPE_SETTINGS' => null,
                'HINT'               => '',
            ],
            [
                'NAME'               => 'Вес одной порции',
                'ACTIVE'             => 'Y',
                'SORT'               => '600',
                'CODE'               => 'PORTION_WEIGHT',
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
                'LINK_IBLOCK_ID'     => '',
                'WITH_DESCRIPTION'   => 'N',
                'SEARCHABLE'         => 'N',
                'FILTRABLE'          => 'N',
                'IS_REQUIRED'        => 'N',
                'VERSION'            => '2',
                'USER_TYPE'          => null,
                'USER_TYPE_SETTINGS' => null,
                'HINT'               => '',
            ],
        ];

        foreach($fields as $field){
            $this->getHelper()->Iblock()->addPropertyIfNotExists($iblockId, $field);
        }
        $this->log()->info('Инфоблок comparing успешно создан');

        $menuItem = [
            'NAME'              => 'Сравнение',
            'CODE'              => 'comparing',
            'SORT'              => 500,
            'XML_ID'            => '',
            // Не отрабатывает
            // 'IBLOCK_SECTION_ID' => $this->getHelper()->Iblock()->getSectionId(IblockCode::MAIN_MENU, 'services'),
            'IBLOCK_SECTION_ID' => 1089
        ];

        // Не отрабатывает
        //$propHrefId = $this->getHelper()->Iblock()->getPropertyId(IblockCode::MAIN_MENU, 'HREF');
        $menuItemProps = [
            50 => '/comparing/',
        ];

        $iblockIdMenu = $this->getHelper()->Iblock()->getIblockId(IblockCode::MAIN_MENU);
        $this->getHelper()->Iblock()->addElementIfNotExists($iblockIdMenu, $menuItem, $menuItemProps);
        $this->log()->info('Пункт меню успешно создан');

        return true;
    }

    public function down(){
        $this->getHelper()->Iblock()->deleteIblockIfExists(IblockCode::COMPARING);

        $iblockIdMenu = $this->getHelper()->Iblock()->getIblockId(IblockCode::MAIN_MENU);
        $this->getHelper()->Iblock()->deleteElementIfExists($iblockIdMenu, 'comparing');

        return true;
    }

}
