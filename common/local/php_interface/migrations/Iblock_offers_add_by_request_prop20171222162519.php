<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockType;
use FourPaws\Enum\IblockCode;

class Iblock_offers_add_by_request_prop20171222162519 extends SprintMigrationBase
{
    protected $description = 'Добавление свойства "под заказ" в ИБ офферов';

    const PROPERTY_CODE = 'BY_REQUEST';

    public function up()
    {
        $iblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS);
        if (!$iblockId) {
            $this->log()->error('Не найден ИБ офферов');

            return false;
        }
        $iblockHelper = $this->getHelper()->Iblock();

        if ($iblockHelper->addPropertyIfNotExists(
            $iblockId,
            [

                'NAME'          => 'Под заказ',
                'ACTIVE'        => 'Y',
                'SORT'          => '500',
                'CODE'          => self::PROPERTY_CODE,
                'DEFAULT_VALUE' => '0',
                'PROPERTY_TYPE' => 'N',
                'USER_TYPE'     => 'YesNoPropertyType',
            ]
        )) {
            $this->log()->info('Добавлено свойство ' . self::PROPERTY_CODE . ' в ИБ офферов');
        } else {
            $this->log()->error('Ошибка при добавлении свойства ' . self::PROPERTY_CODE . ' в ИБ офферов');
        }

        return true;
    }

    public function down()
    {
        $iblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS);
        if (!$iblockId) {
            $this->log()->error('Не найден ИБ офферов');

            return false;
        }
        $iblockHelper = $this->getHelper()->Iblock();

        if ($iblockHelper->deletePropertyIfExists($iblockId, self::PROPERTY_CODE)) {
            $this->log()->info('Удалено свойство ' . self::PROPERTY_CODE . ' в ИБ офферов');
        } else {
            $this->log()->error('Ошибка при удалении свойства ' . self::PROPERTY_CODE . ' в ИБ офферов');
        }

        return true;
    }
}
