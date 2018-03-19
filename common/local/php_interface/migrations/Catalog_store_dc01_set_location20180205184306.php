<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Catalog\StoreTable;
use FourPaws\LocationBundle\LocationService;

class Catalog_store_dc01_set_location20180205184306 extends SprintMigrationBase
{
    const STORE_XML_ID = 'DC01';

    protected $description = 'Задание местоположения для склада ' . self::STORE_XML_ID;

    public function up()
    {
        $store = StoreTable::getList(['filter' => ['XML_ID' => self::STORE_XML_ID]])->fetch();
        if (!$store) {
            $this->log()->error('Не найден склад ' . self::STORE_XML_ID);

            return false;
        }

        $result = StoreTable::update($store['ID'], ['UF_LOCATION' => LocationService::LOCATION_CODE_MOSCOW]);
        if ($result->isSuccess()) {
            $this->log()->info('Задано местоположение для склада ' . self::STORE_XML_ID);
        } else {
            $this->log()->error(
                'Ошибка при изменении склада ' . self::STORE_XML_ID . ': ' . implode(', ', $result->getErrorMessages())
            );

            return false;
        }

        return true;
    }

    public function down()
    {
        $store = StoreTable::getList(['filter' => ['XML_ID' => self::STORE_XML_ID]])->fetch();
        if (!$store) {
            $this->log()->error('Не найден склад ' . self::STORE_XML_ID);

            return false;
        }

        $result = StoreTable::update($store['ID'], ['UF_LOCATION' => null]);
        if ($result->isSuccess()) {
            $this->log()->info('Удалено местоположение для склада ' . self::STORE_XML_ID);
        } else {
            $this->log()->error(
                'Ошибка при изменении склада ' . self::STORE_XML_ID . ': ' . implode(', ', $result->getErrorMessages())
            );

            return false;
        }

        return true;
    }
}
