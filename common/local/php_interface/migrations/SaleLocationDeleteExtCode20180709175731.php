<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Sale\Location\ExternalServiceTable;
use Bitrix\Sale\Location\ExternalTable;
use Bitrix\Sale\Location\LocationTable;
use FourPaws\App\Application;

class SaleLocationDeleteExtCode20180709175731 extends SprintMigrationBase
{
    protected $description = 'Удаление кода кладр для мкрн Юбилейный г. Королёв';

    protected const EXTERNAL_CODE = 'KLADR';

    protected const LOCATION_CODE = '0000032219';

    /**
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function up()
    {
        if (!$externalService = ExternalServiceTable::getList(
            [
                'filter' => ['CODE' => static::EXTERNAL_CODE],
            ]
        )->fetch()) {
            $this->log()->error('Внешний сервис местоположений ' . static::EXTERNAL_CODE . ' не найден');
            return false;
        }

        if (!$location = LocationTable::getList(['filter' => ['CODE' => static::LOCATION_CODE]])->fetch()) {
            $this->log()->error('Местоположение с кодом ' . static::LOCATION_CODE . ' не найдено');
            return false;
        }

        if (!$item = ExternalTable::getList(['filter' => [
            'LOCATION_ID' => $location['ID'],
            'SERVICE_ID' => $externalService['ID']
        ]])->fetch()) {
            $this->log()->warning('Не найден код КЛАДР для ' . static::LOCATION_CODE);
        } else {
            $deleteResult = ExternalTable::delete($item['ID']);
            if (!$deleteResult->isSuccess()) {
                $this->log()->error(
                    sprintf(
                        'Ошибка при удалении кода КЛАДР для %s: %s',
                        static::LOCATION_CODE,
                        implode(',', $deleteResult->getErrorMessages())
                    )
                );
                return false;
            }
        }

        return true;
    }

    public function down()
    {

    }
}
