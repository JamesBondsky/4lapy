<?php

namespace Sprint\Migration;


use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class AddPersonalOffersCouponTitleProperty20190916140628 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = 'Добавляет свойство "Заголовок в описании купонов" в инфоблок "Персональные предложения';
    private $propCode = 'COUPON_TITLE';

    public function up()
    {
        $helper = new HelperManager();

        $helper->Iblock()->addIblockTypeIfNotExists([
            'ID'               => 'publications',
            'SECTIONS'         => 'Y',
            'EDIT_FILE_BEFORE' => '',
            'EDIT_FILE_AFTER'  => '',
            'IN_RSS'           => 'N',
            'SORT'             => '200',
            'LANG'             =>
                [
                    'ru' =>
                        [
                            'NAME'         => 'Публикации',
                            'SECTION_NAME' => '',
                            'ELEMENT_NAME' => 'Публикация',
                        ],
                ],
        ]);

        $iblockId = $helper->Iblock()->addIblockIfNotExists([
            'IBLOCK_TYPE_ID'     => 'publications',
            'LID'                => 's1',
            'CODE'               => 'personal_offers',
            'NAME'               => 'Персональные предложения',
            'ACTIVE'             => 'Y',
            'SORT'               => '40',
            'LIST_PAGE_URL'      => '',
            'DETAIL_PAGE_URL'    => '',
            'SECTION_PAGE_URL'   => '',
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
            'XML_ID'             => 'personal_offers',
            'TMP_ID'             => NULL,
            'INDEX_ELEMENT'      => 'N',
            'INDEX_SECTION'      => 'N',
            'WORKFLOW'           => 'N',
            'BIZPROC'            => 'N',
            'SECTION_CHOOSER'    => 'L',
            'LIST_MODE'          => '',
            'RIGHTS_MODE'        => 'S',
            'SECTION_PROPERTY'   => 'N',
            'PROPERTY_INDEX'     => 'Y',
            'VERSION'            => '2',
            'LAST_CONV_ELEMENT'  => '0',
            'SOCNET_GROUP_ID'    => NULL,
            'EDIT_FILE_BEFORE'   => '',
            'EDIT_FILE_AFTER'    => '',
            'SECTIONS_NAME'      => 'Разделы',
            'SECTION_NAME'       => 'Раздел',
            'ELEMENTS_NAME'      => 'Элементы',
            'ELEMENT_NAME'       => 'Элемент',
            'EXTERNAL_ID'        => 'personal_offers',
            'LANG_DIR'           => '/',
            'SERVER_NAME'        => '4lapy.ru',
        ]);

        $helper->Iblock()->addPropertyIfNotExists($iblockId, [
            'NAME'               => 'Заголовок в описании купонов',
            'ACTIVE'             => 'Y',
            'SORT'               => '80',
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
            'FILTRABLE'          => 'Y',
            'IS_REQUIRED'        => 'N',
            'VERSION'            => '2',
            'USER_TYPE'          => NULL,
            'USER_TYPE_SETTINGS' => NULL,
            'HINT'               => '',
        ]);
    }

    public function down()
    {
        $helper = new HelperManager();

        $iblockId = $helper->Iblock()->getIblockId(IblockCode::PERSONAL_OFFERS, IblockType::PUBLICATION);

        $helper->Iblock()->deletePropertyIfExists($iblockId, $this->propCode);
    }

}
