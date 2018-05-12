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
use FourPaws\LocationBundle\LocationService;

class DeliveryDpdLocations20180512205141 extends SprintMigrationBase
{
    protected $description = 'Создание внешнего сервиса KLADR для местоположений';

    protected const EXTERNAL_CODE = 'KLADR';


    /**
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function up()
    {
        if ($externalService = ExternalServiceTable::getList(
            [
                'filter' => ['CODE' => static::EXTERNAL_CODE],
            ]
        )->fetch()) {
            $externalServiceId = $externalService['ID'];
            $this->log()->info('Внешний сервис местоположений ' . static::EXTERNAL_CODE . ' уже существует');
        } else {
            $addResult = ExternalServiceTable::add(['CODE' => static::EXTERNAL_CODE]);
            if ($addResult->isSuccess()) {
                $externalServiceId = $addResult->getId();
                $this->log()->info('Внешний сервис местоположений ' . static::EXTERNAL_CODE . ' добавлен');
            } else {
                $this->log()->error(
                    'Ошибка при добавлении внешнего сервиса местоположений ' . static::EXTERNAL_CODE
                );

                return false;
            }
        }

        while ($kladrLocations = $this->loadLocations()) {
            if (empty($kladrLocations)) {
                break;
            }

            $kladrLocations = array_filter($kladrLocations, function ($item) {
                return $item && $item !== '@';
            });

            if (empty($kladrLocations)) {
                continue;
            }

            // если делать средствами битрикса, то миграция будет выполняться около 5000 лет
            $q = "SELECT CODE, ID FROM b_sale_location WHERE CODE IN (" . implode(',', \array_keys($kladrLocations)) . ")";
            $locations = \Bitrix\Main\Application::getConnection()->query($q)->fetchAll();

            $codeToId = [];
            foreach ($locations as $location) {
                $codeToId[$location['CODE']] = $location['ID'];
            }

            foreach ($kladrLocations as $locationCode => $kladrCode) {
                if (!isset($codeToId[$locationCode])) {
                    $this->log()->warning('Не найдено местоположение с кодом ' . $locationCode);
                    continue;
                }
                ExternalTable::add(
                    [
                        'LOCATION_ID' => $codeToId[$locationCode],
                        'SERVICE_ID'  => $externalServiceId,
                        'XML_ID'      => $kladrCode,
                    ]
                );
            }
        }

        return true;
    }

    private function loadLocations(int $count = 10000)
    {
        static $fp;
        if (null === $fp) {
            $filePath = Application::getAbsolutePath('/local/php_interface/migration_sources/location2kladr.csv');
            $fp = fopen($filePath, 'rb');

            if (false === $fp) {
                throw new \RuntimeException(
                    sprintf(
                        'Can not open file %s',
                        $filePath
                    )
                );
            }
        }

        $i = 0;
        $locations = [];
        while ($row = fgetcsv($fp, 0, ';')) {
            if (++$i > $count) {
                break;
            }
            $locations[$row[0]] = $row[1];
        }

        return $locations;
    }

    /**
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function down()
    {
        if (!$externalService = ExternalServiceTable::getList(
            [
                'filter' => ['CODE' => static::EXTERNAL_CODE],
            ]
        )->fetch()) {
            $this->log()->info('Внешний сервис местоположений ' . static::EXTERNAL_CODE . ' не найден');

            return false;
        }

        $externalServiceId = $externalService['ID'];

        $items = ExternalTable::getList(['filter' => ['SERVICE_ID' => $externalServiceId]]);
        while ($item = $items->fetch()) {
            ExternalTable::delete($item['ID']);
        }

        if (ExternalServiceTable::delete($externalServiceId)) {
            $this->log()->info('Внешний сервис местоположений ' . static::EXTERNAL_CODE . ' удален');
        } else {
            $this->log()->error(
                'Ошибка при удалении внешнего сервиса местоположений ' . static::EXTERNAL_CODE
            );

            return false;
        }

        return true;
    }
}
