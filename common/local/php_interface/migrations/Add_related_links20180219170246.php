<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Exception\MigrationFailureException;
use Adv\Bitrixtools\Migration\Iblock\Iblock;
use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class Add_related_links20180219170246 extends SprintMigrationBase
{

    const FIELD_CODE_COUNT = 'UF_COUNT';

    const FIELD_CODE_SECTION = 'UF_SECTION';

    const SITE_ID = 's1';

    protected $description = 'Добавим ИБ "Перелинковка"';

    /**
     * @return bool|void
     */
    public function down()
    {
    }

    /**
     * @return bool|void
     * @throws IblockNotFoundException
     * @throws MigrationFailureException
     */
    public function up()
    {
        $helper = new HelperManager();

        $migrateIblock = new Iblock();

        $relatedLinksIblockId = $migrateIblock->setIblock(
            [
                'IBLOCK_TYPE_ID'   => IblockType::CATALOG,
                'CODE'             => IblockCode:: RELATED_LINKS,
                'SORT'             => 700,
                'ACTIVE'           => 'Y',
                'NAME'             => 'Перелинковка',
                'ELEMENT_NAME'     => 'Ссылка',
                'ELEMENTS_NAME'    => 'Ссылки',
                'ELEMENT_ADD'      => 'Добавить ссылку',
                'ELEMENT_EDIT'     => 'Изменить ссылку',
                'ELEMENT_DELETE'   => 'Удалить ссылку',
                'SECTION_NAME'     => 'Категория',
                'SECTIONS_NAME'    => 'Категории',
                'SECTION_ADD'      => 'Добавить категорию',
                'SECTION_EDIT'     => 'Изменить категорию',
                'SECTION_DELETE'   => 'Удалить категорию',
                'LIST_PAGE_URL'    => '',
                'SECTION_PAGE_URL' => '',
                'DETAIL_PAGE_URL'  => '',
                'SITE_ID'          => [static::SITE_ID],
                'GROUP_ID'         => ['2' => 'R', '10' => 'w'],
                'VERSION'          => 2,
                'BIZPROC'          => 'N',
                'WORKFLOW'         => 'N',
                'INDEX_ELEMENT'    => 'N',
                'INDEX_SECTION'    => 'N',
                'LIST_MODE'        => 'S',
                'FIELDS'           => [
                    'DETAIL_TEXT_TYPE' => [
                        'DEFAULT_VALUE' => 'html',
                    ],
                ],
            ]
        );

        $propList = [
            [
                'IBLOCK_ID'          => $relatedLinksIblockId,
                'IBLOCK_CODE'        => IblockCode::RELATED_LINKS,
                'NAME'               => 'Ссылка',
                'ACTIVE'             => 'Y',
                'SORT'               => '500',
                'CODE'               => 'LINK',
                'DEFAULT_VALUE'      => '',
                'PROPERTY_TYPE'      => 'S',
                'ROW_COUNT'          => '1',
                'COL_COUNT'          => '30',
                'MULTIPLE'           => 'N',
                'XML_ID'             => null,
                'FILE_TYPE'          => '',
                'MULTIPLE_CNT'       => '1',
                'TMP_ID'             => null,
                'WITH_DESCRIPTION'   => 'N',
                'SEARCHABLE'         => 'N',
                'FILTRABLE'          => 'Y',
                'IS_REQUIRED'        => 'Y',
                'VERSION'            => '2',
                'USER_TYPE'          => null,
                'USER_TYPE_SETTINGS' => null,
                'HINT'               => '',
            ],
        ];
        foreach ($propList as $prop) {
            $migrateIblock->setProperty($prop);
        }

        $entityId = 'IBLOCK_' . $relatedLinksIblockId . '_SECTION';

        $helper
            ->UserTypeEntity()
            ->addUserTypeEntityIfNotExists(
                $entityId,
                static::FIELD_CODE_COUNT,
                [
                    'USER_TYPE_ID'    => 'integer',
                    'MULTIPLE'        => 'N',
                    'IS_SEARCHABLE'   => 'N',
                    'SHOW_FILTER'     => 'S',
                    'SHOW_IN_LIST'    => '',
                    'EDIT_IN_LIST'    => '',
                    'EDIT_FORM_LABEL' => [
                        'ru' => 'Количество ссылок',
                    ],
                    'HELP_MESSAGE'    => [
                        'ru' => 'Количество ссылок в разделе',
                    ],
                ]
            );

        $helper
            ->UserTypeEntity()
            ->addUserTypeEntityIfNotExists(
                $entityId,
                static::FIELD_CODE_SECTION,
                [
                    'USER_TYPE_ID'    => 'iblock_section',
                    'MULTIPLE'        => 'N',
                    'IS_SEARCHABLE'   => 'N',
                    'SHOW_FILTER'     => 'S',
                    'SHOW_IN_LIST'    => '',
                    'EDIT_IN_LIST'    => '',
                    'EDIT_FORM_LABEL' => [
                        'ru' => 'Раздел каталога',
                    ],
                    'HELP_MESSAGE'    => [
                        'ru' => 'Раздел каталога',
                    ],
                    'SETTINGS'        => [
                        'IBLOCK_TYPE_ID' => IblockType::CATALOG,
                        'IBLOCK_ID'      => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS),
                    ],
                ]
            );

        //форма элемента
        $helper->AdminIblock()->buildElementForm(
            $relatedLinksIblockId,
            [
                'Ссылка'         => [
                    'ACTIVE',
                    'ACTIVE_FROM',
                    'ACTIVE_TO',
                    'NAME' => 'Текст',
                    'PROPERTY_LINK',
                    'SORT',
                ],
                'Категория'      => [
                    'SECTIONS' => 'Разделы',
                ],
                'Системные поля' => [
                    'XML_ID',
                ],
            ]
        );

        //список элементов
        $helper->AdminIblock()->buildElementList(
            $relatedLinksIblockId,
            [
                'NAME',
                'PROPERTY_LINK',
                'SORT',
                'ACTIVE',
                'DATE_ACTIVE_FROM',
                'DATE_ACTIVE_TO',
                'ID',
            ],
            [
                'order'     => 'desc',
                'by'        => 'sort',
                'page_size' => 10,
            ]
        );

        //форма раздела
        $helper->AdminIblock()->buildElementForm(
            $relatedLinksIblockId,
            [
                'Основное' => [
                    'ACTIVE',
                    'NAME',
                    'SORT',
                    static::FIELD_CODE_COUNT,
                    static::FIELD_CODE_SECTION,
                ],
            ],
            ['name_prefix' => 'form_section_']
        );

        //список разделов
        $helper->AdminIblock()->buildElementList(
            $relatedLinksIblockId,
            [
                'NAME',
                'SORT',
                'ACTIVE',
                static::FIELD_CODE_COUNT,
                static::FIELD_CODE_SECTION,
                'ID',
            ],
            [
                'order'     => 'desc',
                'by'        => 'sort',
                'page_size' => 20,
            ]
        );
    }

}
