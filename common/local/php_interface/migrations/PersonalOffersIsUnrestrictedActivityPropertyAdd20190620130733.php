<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class PersonalOffersIsUnrestrictedActivityPropertyAdd20190620130733 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = 'Добавление в инфоблок "Персональные предложения" свойства "Применять выданные купоны независимо от активности предложения"';

    protected $propCode = 'IS_UNRESTRICTED_ACTIVITY';

    public function up()
    {
        $helper = new HelperManager();

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
            'XML_ID'             => '',
            'TMP_ID'             => NULL,
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
            'SOCNET_GROUP_ID'    => NULL,
            'EDIT_FILE_BEFORE'   => '',
            'EDIT_FILE_AFTER'    => '',
            'SECTIONS_NAME'      => 'Разделы',
            'SECTION_NAME'       => 'Раздел',
            'ELEMENTS_NAME'      => 'Элементы',
            'ELEMENT_NAME'       => 'Элемент',
            'EXTERNAL_ID'        => '',
            'LANG_DIR'           => '/',
            'SERVER_NAME'        => '4lapy.ru',
        ]);

        $helper->Iblock()->addPropertyIfNotExists($iblockId, [
            'NAME'               => 'Применять выданные купоны независимо от активности предложения',
            'ACTIVE'             => 'Y',
            'SORT'               => '500',
            'CODE'               => $this->propCode,
            'DEFAULT_VALUE'      => false,
            'PROPERTY_TYPE'      => 'N',
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
            'USER_TYPE'          => 'WebArch\\BitrixIblockPropertyType\\YesNoType',
            'USER_TYPE_SETTINGS' => NULL,
            'HINT'               => '',
        ]);
    }

    public function down()
    {
        $helper = new HelperManager();
        $iblockId = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::PERSONAL_OFFERS);

        $helper->Iblock()->deletePropertyIfExists($iblockId, $this->propCode);
    }

}
