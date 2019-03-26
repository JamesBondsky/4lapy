<?php

namespace Sprint\Migration;


use FourPaws\App\Application;

class ImportLatLonToLocationsTalbe20181218195815 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Импорт широты и долготы в таблицу местоположений";

    public function up(){
        $locationsNew = \Bitrix\Sale\Location\LocationTable::query()->setSelect(['*'])->setFilter(['=LATITUDE' => 0])->exec()->fetchAll();
        $locationsOld = $this->loadLocations();
        foreach ($locationsNew as $locationNew) {
            if ($locationOld = $locationsOld[$locationNew['CODE']]) {
                \Bitrix\Sale\Location\LocationTable::update($locationNew['ID'], [
                    'LATITUDE' => $locationOld['LATITUDE'],
                    'LONGITUDE' => $locationOld['LONGITUDE']
                ]);
            }
        }
    }

    public function down(){
        // no down
    }

    private function loadLocations(int $count = 10000)
    {
        static $fp;
        if (null === $fp) {
            $filePath = Application::getAbsolutePath('/local/php_interface/migration_sources/location_geo_coords.csv');
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
            [$code, $lat, $lon] = $row;
            $locations[$row[0]] = [
                'CODE' => $code,
                'LATITUDE' => $lat,
                'LONGITUDE' => $lon,
            ];
        }

        return $locations;
    }

}
