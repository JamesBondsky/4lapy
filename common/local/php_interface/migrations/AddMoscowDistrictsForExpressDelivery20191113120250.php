<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Sale\Location\Admin\ExternalServiceHelper;
use Bitrix\Sale\Location\Admin\LocationHelper;
use Bitrix\Sale\Location\Admin\TypeHelper;
use Bitrix\Sale\Location\GroupLocationTable;
use Bitrix\Sale\Location\GroupTable;
use Exception;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AddMoscowDistrictsForExpressDelivery20191113120250 extends SprintMigrationBase
{
    protected $description = 'Создание новых местоположений районов Москвы и Зон для них';

    protected $locationType = 'DISTRICT_MOSCOW';

    protected $locationService = 'OKATO';

    protected $locationServiceId;

    protected $locationTypeId;

    protected $parentLocationsID;

    protected $locationGroups = [
        [
            'NAME' => 'Зона "Экспресс доставка 4 лапы" 45 минут',
            'SORT' => '91',
            'CODE' => 'ZONE_EXPRESS_DELIVERY_45',
            'ITEMS' => [
                [
                    'CODE' => '45268584000',
                    'NAME_RU' => 'Район Раменки',
                    'SHORT_NAME_RU' => 'р-н Раменки'
                ],
                [
                    'CODE' => '45293574000',
                    'NAME_RU' => 'Район Ломоносовский',
                    'SHORT_NAME_RU' => 'р-н Ломоносовский',
                ],
            ],
        ],
        [
            'NAME' => 'Зона "Экспресс доставка 4 лапы" 90 минут',
            'SORT' => '92',
            'CODE' => 'ZONE_EXPRESS_DELIVERY_90',
            'ITEMS' => [
                [
                    'CODE' => '45268581000',
                    'NAME_RU' => 'Район Проспект Вернадского',
                    'SHORT_NAME_RU' => 'р-н Проспект Вернадского'
                ],
                [
                    'CODE' => '45293578000',
                    'NAME_RU' => 'Район Обручевский',
                    'SHORT_NAME_RU' => 'р-н Обручевский',
                ],
                [
                    'CODE' => '45293558000',
                    'NAME_RU' => 'Район Гагаринский',
                    'SHORT_NAME_RU' => 'р-н Гагаринский'
                ],
                [
                    'CODE' => '45293554000',
                    'NAME_RU' => 'Район Академический',
                    'SHORT_NAME_RU' => 'р-н Академический',
                ],
            ],
        ],
    ];

    /**
     * @return bool
     * @throws Exception
     */
    public function up(): bool
    {
        $this->fillParentLocationId();
        $this->fillLocationTypeId();
        $this->fillLocationServiceId();

        foreach ($this->locationGroups as $locationGroup) {
            $addResult = GroupTable::add(
                [
                    'NAME' => ['ru' => ['NAME' => $locationGroup['NAME']]],
                    'SORT' => $locationGroup['SORT'],
                    'CODE' => $locationGroup['CODE'],
                ]
            );
            if (!$addResult->isSuccess()) {
                throw new RuntimeException(implode(', ', $addResult->getErrorMessages()));
            }

            $groupId = $addResult->getPrimary()['ID'];

            foreach ($locationGroup['ITEMS'] as $location) {
                $okato = $location['CODE'];
                $location['CODE'] = $this->locationType . '_' . $location['CODE'];
                $location['EXTERNAL']['n0'] = [
                    'SERVICE_ID' => (string)$this->locationServiceId,
                    'XML_ID' => $okato
                ];

                $res = LocationHelper::add(
                    array_merge(
                        $location,
                        [
                            'SORT' => '0',
                            'PARENT_ID' => (string)$this->parentLocationsID,
                            'TYPE_ID' => (string)$this->locationTypeId,
                            'LATITUDE' => '0',
                            'LONGITUDE' => '0'
                        ]
                    )
                );

                if (!$res) {
                    throw new RuntimeException(implode(' ,', $res['errors']));
                }

                GroupLocationTable::add(
                    [
                        'LOCATION_ID' => $res['id'],
                        'LOCATION_GROUP_ID' => $groupId
                    ]
                );
            }
        }

        return true;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function down(): void
    {
        $locations = [];
        $groupCodes = [];

        foreach ($this->locationGroups as $locationGroup) {
            $groupCodes[] = $locationGroup['CODE'];
            foreach ($locationGroup['ITEMS'] as $location) {
                $locations[] = $location;
            }
        }

        $locationCodes = array_map(function ($location) {
            return $this->locationType . '_' . $location['CODE'];
        }, $locations);

        $dbLocations = LocationHelper::getList([
            'filter' => [
                'CODE' => $locationCodes
            ],
            'select' => [
                'ID',
                'CODE'
            ]
        ]);

        while ($location = $dbLocations->Fetch()) {
            LocationHelper::delete($location['ID']);
        }

        if (!empty($groupCodes)) {
            $res = GroupTable::query()
                ->setFilter(['CODE' => $groupCodes])
                ->setSelect(['ID'])
                ->exec();

            while ($group = $res->fetch()) {
                GroupTable::delete($group['ID']);
            }

        }
    }

    protected function fillParentLocationId(): void
    {
        $res = LocationHelper::getList([
            'filter' => [
                'CODE' => DeliveryService::MOSCOW_LOCATION_CODE,
            ],
            'select' => [
                'ID',
            ],
        ])->Fetch();

        if (!$res) {
            throw  new NotFoundHttpException('Moscow location not found');
        }

        $this->parentLocationsID = (string)$res['ID'];
    }

    protected function fillLocationServiceId(): void
    {
        $locationServiceID = null;
        $dbServices = ExternalServiceHelper::getList([
            'filter' => [
                'CODE' => $this->locationService
            ],
            'select' => [
                'ID',
                'CODE'
            ]
        ]);
        while ($service = $dbServices->Fetch()) {
            if ($service['CODE'] === $this->locationService) {
                $locationServiceID = $service['ID'];
                break;
            }
        }

        if (!$locationServiceID) {
            throw new RuntimeException('Location service with ' . $this->locationService . ' code not found');
        }

        $this->locationServiceId = $locationServiceID;
    }

    protected function fillLocationTypeId(): void
    {
        $locationTypeID = null;
        $types = TypeHelper::getTypes();
        foreach ($types as $type) {
            if ($type['CODE'] === $this->locationType) {
                $locationTypeID = $type['ID'];
                break;
            }
        }

        if (!$locationTypeID) {
            throw new RuntimeException('Location type with ' . $this->locationType . ' code not found');
        }

        $this->locationTypeId = $locationTypeID;
    }
}
