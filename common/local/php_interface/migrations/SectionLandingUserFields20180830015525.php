<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

/**
 * Class SectionLandingUserFields20180820195005
 *
 * @package Sprint\Migration
 */
class SectionLandingUserFields20180830015525 extends SprintMigrationBase
{
    protected $description = 'Коррекция полей разделов для лендинга - множественный баннер';

    /**
     * @return bool|void
     * @throws IblockNotFoundException
     */
    public function up()
    {
        $bannerIblockId = IblockUtils::getIblockId(IblockType::PUBLICATION,
            IblockCode::BANNERS);

        $this->getHelper()->Iblock()->updatePropertyIfExists($bannerIblockId, 'SECTION', [
            'NAME' => 'Привязка к разделу',
            'ACTIVE' => 'Y',
            'SORT' => '500',
            'CODE' => 'SECTION',
            'DEFAULT_VALUE' => '',
            'PROPERTY_TYPE' => 'G',
            'ROW_COUNT' => '1',
            'COL_COUNT' => '30',
            'LIST_TYPE' => 'L',
            'MULTIPLE' => 'Y',
            'XML_ID' => '',
            'FILE_TYPE' => '',
            'MULTIPLE_CNT' => '5',
            'TMP_ID' => null,
            'LINK_IBLOCK_ID' => '2',
            'WITH_DESCRIPTION' => 'N',
            'SEARCHABLE' => 'N',
            'FILTRABLE' => 'N',
            'IS_REQUIRED' => 'N',
            'VERSION' => '2',
            'USER_TYPE' => null,
            'USER_TYPE_SETTINGS' => null,
            'HINT' => '',
        ]);
    }

    /**
     * @return bool|void
     *
     * @throws IblockNotFoundException
     */
    public function down()
    {
        $bannerIblockId = IblockUtils::getIblockId(IblockType::PUBLICATION,
            IblockCode::BANNERS);

        $this->getHelper()->Iblock()->updatePropertyIfExists($bannerIblockId, 'SECTION', [
            'NAME' => 'Привязка к разделу',
            'ACTIVE' => 'Y',
            'SORT' => '500',
            'CODE' => 'SECTION',
            'DEFAULT_VALUE' => '',
            'PROPERTY_TYPE' => 'G',
            'ROW_COUNT' => '1',
            'COL_COUNT' => '30',
            'LIST_TYPE' => 'L',
            'MULTIPLE' => 'Y',
            'XML_ID' => '',
            'FILE_TYPE' => '',
            'MULTIPLE_CNT' => '5',
            'TMP_ID' => null,
            'LINK_IBLOCK_ID' => '2',
            'WITH_DESCRIPTION' => 'N',
            'SEARCHABLE' => 'N',
            'FILTRABLE' => 'N',
            'IS_REQUIRED' => 'N',
            'VERSION' => '2',
            'USER_TYPE' => null,
            'USER_TYPE_SETTINGS' => null,
            'HINT' => '',
        ]);
    }
}
