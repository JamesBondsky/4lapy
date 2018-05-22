<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Catalog\StoreTable;

class StoreDeleteDuplicates20180517171026 extends SprintMigrationBase
{
    protected $description = 'Удаление дубликатов складов';

    protected $baseShops = [
        'R046',
        'R059',
        'R169',
        'R181',
        'R186',
        'R225',
    ];

    public function up()
    {
        $found = [];
        $stores = StoreTable::getList([
                'select' => ['*', 'UF_*'],
                'order'  => ['ID' => 'asc'],
            ]
        );

        while ($store = $stores->fetch()) {
            $found[$store['XML_ID']][$store['ID']] = $store;
        }

        foreach ($found as $xmlId => $stores) {
            if (\count($stores) === 1) {
                continue;
            }

            $result = $this->findBestFieldValues($stores);

            \reset($stores);
            $id = \key($stores);
            $updateResult = StoreTable::update($id, $result);
            if (!$updateResult->isSuccess()) {
                $this->log()->error(
                    sprintf(
                        'Ошибка при обновлении склада %s: %s',
                        $store['ID'],
                        implode(', ', $updateResult->getErrorMessages())
                    )
                );
                return false;
            }

            \array_shift($stores);
            foreach ($stores as $id => $store) {
                $deleteResult = StoreTable::delete($store['ID']);
                if (!$deleteResult->isSuccess()) {
                    $this->log()->warning(
                        sprintf(
                            'Ошибка при удалении склада %s: %s',
                            $store['ID'],
                            implode(', ', $deleteResult->getErrorMessages())
                        )
                    );
                }
            }
        }

        return true;
    }

    protected function findBestFieldValues($stores)
    {
        $result = [];

        $skipFields = [
            'ID',
            'DATE_MODIFY',
            'DATE_CREATE',
            'ACTIVE',
            'USER_ID',
            'MODIFIED_BY',
            'UF_SERVICES_SINGLE',
            'UF_REGION_SINGLE',
            'UF_SHIPMENT_TILL_11_SINGLE',
            'UF_SHIPMENT_TILL_13_SINGLE',
            'UF_SHIPMENT_TILL_18_SINGLE',
        ];

        foreach ($stores as $id => $store) {
            foreach ($store as $code => $value) {
                if (\in_array($code, $skipFields, true)) {
                    continue;
                }

                if (!$value) {
                    continue;
                }

                switch ($code) {
                    case 'ADDRESS':
                        if (\mb_strlen($result['ADDRESS']) < \mb_strlen($value)) {
                            $result['ADDRESS'] = $value;
                        }
                        break;
                    default:
                        if (!$result[$code]) {
                            $result[$code] = $value;
                        }
                }
            }
        }

        if (\in_array($result['XML_ID'], $this->baseShops, true)) {
            $result['UF_IS_BASE_SHOP'] = 1;
        }

        return $result;
    }

    public function down()
    {

    }
}