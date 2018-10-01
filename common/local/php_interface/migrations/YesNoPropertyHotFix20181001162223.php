<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Application;

/** @noinspection PhpUndefinedClassInspection
 *
 * Class YesNoPropertyHotFix20181001162223
 *
 * @package Sprint\Migration
 */
class YesNoPropertyHotFix20181001162223 extends SprintMigrationBase
{
    private const CURRENT_YES_NO_PROPERTY_CODES =
        [
            'STM',
            'LICENSE',
            'LOW_TEMPERATURE',
            'FOOD',
            'POPULAR',
            'TARGET_BLANK',
            'PRODUCED_BY_HOLDER',
            'ONLY_MP',
            'TRANSPORT_ONLY_REFRIGERATOR',
            'DC_SPECIAL_AREA_STORAGE',
            'IS_HIT',
            'IS_NEW',
            'IS_POPULAR',
            'IN_LANDING',
            'IS_SALE',
            'BONUS_EXCLUDE',
            'PREMISE_BONUS',
        ];


    protected /** @noinspection ClassOverridesFieldOfSuperClassInspection */
        $description = 'Yes/No property hot fix';

    public function up()
    {
        Application::getConnection()->query(
            \sprintf(
                'UPDATE b_iblock_property SET USER_TYPE = \'WebArch\\\\BitrixIblockPropertyType\\\\YesNoType\' WHERE CODE IN (\'%s\')',
                \implode('\', \'', self::CURRENT_YES_NO_PROPERTY_CODES)
            )
        );
    }
}
