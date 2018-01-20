<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use Sprint\Migration\Helpers\IblockHelper;

class Product_refrigerator_property20180112181110 extends SprintMigrationBase
{
    protected $description = 'Добавление свойства "Перевозить в холодильнике" в ИБ каталога';

    const PROP_CODE = 'REFRIGERATED';

    public function up()
    {
        /** @var IblockHelper $iblockHelper */
        $iblockHelper = $this->getHelper()->Iblock();
        $iblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS);

        if ($iblockHelper->addPropertyIfNotExists(
            $iblockId,
            [
                'NAME'          => 'Перевозить в холодильнике',
                'CODE'          => self::PROP_CODE,
                'ACTIVE'        => 'Y',
                'USER_TYPE'     => 'YesNoPropertyType',
                'PROPERTY_TYPE' => 'N',
                'DEFAULT_VALUE' => 0,
            ]
        )) {
            $this->log()->info('Добавлено свойство ' . self::PROP_CODE);
        } else {
            $this->log()->error('Ошибка при добавлении свойства ' . self::PROP_CODE);

            return false;
        }

        return true;
    }

    public function down()
    {
        /** @var IblockHelper $iblockHelper */
        $iblockHelper = $this->getHelper()->Iblock();
        $iblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS);

        if ($iblockHelper->deletePropertyIfExists($iblockId, self::PROP_CODE)) {
            $this->log()->info('Свойство ' . self::PROP_CODE . ' удалено');
        } else {
            $this->log()->error('Ошибка при удалении свойства ' . self::PROP_CODE);

            return false;
        }

        return true;
    }
}
