<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

/**
 * Class AddGoogleCategory20181010121215
 *
 * @package Sprint\Migration
 */
class AddGoogleCategory20181010121215 extends SprintMigrationBase
{
    protected const FIELD_CODE = 'GOOGLE_CATEGORY';

    /**
     * @return bool
     * @throws Exceptions\HelperException
     * @throws IblockNotFoundException
     */
    public function up()
    {
        $this->getHelper()->Iblock()->addPropertyIfNotExists($this->getEntityId(), [
            'NAME' => 'Категория для google merchant',
            'ACTIVE' => 'Y',
            'SORT' => '600',
            'CODE' => 'BONUS_EXCLUDE',
            'DEFAULT_VALUE' => 0,
            'PROPERTY_TYPE' => 'S',
            'ROW_COUNT' => '1',
            'COL_COUNT' => '30',
            'LIST_TYPE' => 'L',
            'MULTIPLE' => 'N',
            'XML_ID' => '',
            'FILE_TYPE' => '',
            'MULTIPLE_CNT' => '5',
            'TMP_ID' => null,
            'LINK_IBLOCK_ID' => '0',
            'WITH_DESCRIPTION' => 'N',
            'SEARCHABLE' => 'N',
            'FILTRABLE' => 'N',
            'IS_REQUIRED' => 'N',
            'VERSION' => '2',
            'USER_TYPE' => '',
            'USER_TYPE_SETTINGS' => null,
            'HINT' => '',
        ]);

        return true;
    }

    /**
     * @return string
     * @throws IblockNotFoundException
     */
    protected function getEntityId()
    {
        return IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS);
    }

    /**
     * @return bool
     * @throws Exceptions\HelperException
     * @throws IblockNotFoundException
     */
    public function down()
    {
        return true;
    }
}
