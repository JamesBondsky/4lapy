<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Location\Admin\TypeHelper;
use Bitrix\Sale\Location\Admin\LocationHelper;
use Bitrix\Sale\Location\Admin\ExternalServiceHelper;
use Exception;

class MoscowZonesWithTypeDistrictMoscow20190611130707 extends SprintMigrationBase
{
    protected $description = 'Создание нового типа - Район Москвы и нового сервиса ОКАТО';

    protected $locationType = [
        'CODE'         => 'DISTRICT_MOSCOW',
        'SORT'         => 610,
        'DISPLAY_SORT' => 120,
        'NAME_RU'      => 'Район Москвы'
    ];

    protected $locationService = [
        'CODE' => 'OKATO'
    ];

    /**
     * @return void
     * @throws Exception
     */
    public function up()
    {
        $districtMoscowTypeID = TypeHelper::add($this->locationType)['id'];

        $serviceID = ExternalServiceHelper::add($this->locationService)['id'];

        if (!$districtMoscowTypeID) {
            throw new Exception('Location type DISTRICT_MOSCOW not found!');
        }

        if (!$serviceID) {
            throw new Exception('Service OKATO not found!');
        }
    }

    /**
     * @return void
     */
    public function down()
    {
        $types = TypeHelper::getTypes();
        foreach ($types as $type) {
            if ($type['CODE'] == $this->locationType['CODE']) {
                TypeHelper::delete($type['ID']);
                break;
            }
        }

        $dbServices = ExternalServiceHelper::getList([
            'filter' => [
                'CODE' => $this->locationService['CODE']
            ],
            'select' => [
                'ID',
                'CODE'
            ]
        ]);

        while ($service = $dbServices->Fetch()) {
            if ($service['CODE'] == $this->locationService['CODE']) {
                ExternalServiceHelper::delete($service['ID']);
                break;
            }
        }
    }
}
