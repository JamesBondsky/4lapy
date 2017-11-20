<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use CApplicationException;
use CCatalogGroup;
use FourPaws\App\Application;
use RuntimeException;

class Catalog_price_types20170921152127 extends SprintMigrationBase
{

    protected $description = "Типы цен в каталоге";

    public function up()
    {
        global $APPLICATION;

        $CCatalogGroup = new CCatalogGroup();

        $regionList = $this->loadRegionList();

        $statTotal = count($regionList);
        $statOk = 0;
        $statError = 0;

        foreach ($regionList as $number => $name) {
            $code = 'IR' . $number;
            $groupId = $CCatalogGroup->Add(
                [
                    'NAME'           => $code,
                    'USER_LANG'      => [
                        'ru' => $name,
                    ],
                    'SORT'           => (int)$number,
                    'USER_GROUP'     => [2],
                    'USER_GROUP_BUY' => [2],
                    'XML_ID'         => $code,
                ]
            );
            if (false == $groupId) {
                $statError++;
                $errorMessage = '<null>';
                $exception = $APPLICATION->GetException();
                if ($exception instanceof CApplicationException) {
                    $errorMessage = $exception->GetString();
                }
                $this->log()->error(
                    sprintf(
                        'Ошибка создания типа цен для региона номер %s: %s',
                        $code,
                        $errorMessage
                    )
                );

            } else {
                $statOk++;
            }
        }

        $this->log()->info(
            sprintf(
                "Создание типов цен:\tВсего %d\tУспех %d\tОшибка %d",
                $statTotal,
                $statOk,
                $statError
            )
        );
    }

    private function loadRegionList()
    {
        $filePath = Application::getAbsolutePath('/local/php_interface/migration_sources/regions.csv');

        $fp = fopen($filePath, 'rb');
        if (false === $fp) {
            throw new RuntimeException(
                sprintf(
                    'Can not open file %s',
                    $filePath
                )
            );
        }

        $regionList = [];

        while ($row = fgetcsv($fp, null, "\t", '"')) {
            $regionList[trim($row[1])] = trim($row[0]);
        }

        fclose($fp);

        return $regionList;
    }

}
