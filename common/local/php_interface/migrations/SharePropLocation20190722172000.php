<?php
namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class SharePropLocation20190722172000 extends SprintMigrationBase
{
    protected $description = 'Добавляет свойство акции "Местоположение"';

    const PROP_CODE = 'LOCATION';

    const SITE_ID = 's1';

    protected $properties = [
        'LOCATION' => [
            'NAME'          => 'Местоположение',
            'CODE'          => 'LOCATION',
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE'     => 'sale_location',
            'IS_REQUIRED'   => 'N',
            'MULTIPLE'   => 'Y',
        ],
    ];

    public function up()
    {
        $bannerIblockId = IblockUtils::getIblockId(IblockType::PUBLICATION,
            IblockCode::BANNERS);

        /** @var \Sprint\Migration\Helpers\IblockHelper $iblockHelper */
        $iblockHelper = $this->getHelper()->Iblock();

        foreach ($this->properties as $code => $fields) {
            if (!$iblockHelper->addPropertyIfNotExists($bannerIblockId, $fields)) {
                $this->logError('Ошибка при добавлении свойства ' . $code . ' в ИБ с ID=' . $bannerIblockId);

                return false;
            }
        }

        return true;
    }

    public function down()
    {
        /** @var \Sprint\Migration\Helpers\IblockHelper $iblockHelper */
        $iblockHelper = $this->getHelper()->Iblock();

        $bannerIblockId = IblockUtils::getIblockId(IblockType::PUBLICATION,
            IblockCode::BANNERS);

        $iblockHelper->deletePropertyIfExists($bannerIblockId, self::PROP_CODE);

        return true;
    }
}
