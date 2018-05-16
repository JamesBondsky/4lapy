<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Catalog\StoreTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

class StoreAddressFix20180516134332 extends SprintMigrationBase
{
    protected $description = 'trim адресов складов';

    /**
     * @return bool
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws \Exception
     */
    public function up()
    {
        $stores = StoreTable::getList(['select' => ['ID', 'ADDRESS']]);

        while ($store = $stores->fetch()) {
            if (!$address = trim($store['ADDRESS'])) {
                $address = ' ';
            }

            $result = StoreTable::update($store['ID'], ['ADDRESS' => $address]);
            if (!$result->isSuccess()) {
                $this->log()->warning(
                    sprintf(
                        'Ошибка при обновлении склада с id=%s: %s',
                        $store['ID'],
                        implode(', ', $result->getErrorMessages())
                    )
                );
            }
        }

        return true;
    }

    public function down()
    {
        // не требуется
    }
}