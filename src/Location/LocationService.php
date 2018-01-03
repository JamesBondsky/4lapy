<?php

namespace FourPaws\Location;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Highloadblock\DataManager;
use Bitrix\Sale\Location\ExternalTable;
use Bitrix\Sale\Location\GroupLocationTable;
use Bitrix\Sale\Location\TypeTable;
use CBitrixComponent;
use CBitrixLocationSelectorSearchComponent;
use CIBlockElement;
use FourPaws\App\Application;
use FourPaws\BitrixOrm\Model\ModelInterface;
use FourPaws\Enum\CitiesSectionCode;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Location\Exception\CityNotFoundException;
use FourPaws\Location\Model\City;
use FourPaws\Location\Query\CityQuery;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\UserBundle\Service\UserService;
use WebArch\BitrixCache\BitrixCache;

class LocationService
{
    const TYPE_CITY = 'CITY';

    const TYPE_VILLAGE = 'VILLAGE';

    const LOCATION_CODE_MOSCOW = '0000073738';

    const DEFAULT_REGION_CODE = 'IR77';

    const REGION_SERVICE_CODE = 'REGION';

    public function __construct()
    {
    }

    /**
     * Возвращает код текущего региона.
     *
     * @return string
     */
    public function getCurrentRegionCode(): string
    {
        $locationCode = $this->getCurrentLocation();

        return $this->getRegionCode($locationCode);
    }

    /**
     * Возвращает код региона по коду местоположения
     *
     * @param string $locationCode
     *
     * @return string
     */
    public function getRegionCode(string $locationCode): string
    {
        if (!$locationCode || !$location = $this->findLocationByCode($locationCode)) {
            return self::DEFAULT_REGION_CODE;
        }

        $getRegionCode = function () use ($location) {
            $filter = [
                'LOCATION.CODE' => $location['CODE'],
                'SERVICE.CODE'  => self::REGION_SERVICE_CODE,
            ];

            if (!empty ($location['PATH'])) {
                $filter['LOCATION.CODE'] = array_merge(
                    [$filter['LOCATION.CODE']],
                    array_column($location['PATH'], 'CODE')
                );
            }

            if ($region = ExternalTable::getList(
                [
                    'filter' => $filter,
                    // коды привязаны к регионам, так что в принципе может вернуться только одно значение
                    'limit'  => 1,
                ]
            )->fetch()) {
                return $region['XML_ID'];
            }

            return self::DEFAULT_REGION_CODE;
        };

        $data = (new BitrixCache())
            ->withId($locationCode)
            ->resultOf($getRegionCode);

        return $data['result'];
    }

    /**
     * @return array
     */
    public function getAvailableCities(): array
    {
        $getAvailableCities = function () {
            $iblockId = IblockUtils::getIblockId(IblockType::REFERENCE_BOOKS, IblockCode::CITIES);

            /** @var StoreService $storeService */
            $storeService = Application::getInstance()->getContainer()->get('store.service');

            $result = [];
            $filter = ['IBLOCK_ID' => $iblockId, 'SECTION_CODE' => CitiesSectionCode::POPULAR];
            $select = ['ID', 'NAME', 'PROPERTY_LOCATION'];

            // При выборе популярных городов учитываем сортировку
            $sort = ['SORT' => 'ASC', 'ID' => 'ASC'];
            $elements = CIBlockElement::GetList($sort, $filter, false, false, $select);
            while ($element = $elements->Fetch()) {
                if (empty($element['PROPERTY_LOCATION_VALUE'])) {
                    continue;
                }

                $storeCodes = [];
                $stores = $storeService->getByLocation(
                    $element['PROPERTY_LOCATION_VALUE'],
                    StoreService::TYPE_SHOP
                );
                /** @var Store $store */
                foreach ($stores as $store) {
                    $storeCodes[] = $store->getXmlId();
                }

                $result[CitiesSectionCode::POPULAR][] = [
                    'NAME'  => $element['NAME'],
                    'CODE'  => $element['PROPERTY_LOCATION_VALUE'],
                    'SHOPS' => $storeCodes,
                ];
            }

            // При выборе городов Московской обл. сортируем по алфавиту
            $sort = ['NAME' => 'ASC', 'ID' => 'ASC'];
            $filter['SECTION_CODE'] = CitiesSectionCode::MOSCOW_REGION;
            $elements = CIBlockElement::GetList($sort, $filter, false, false, $select);
            while ($element = $elements->Fetch()) {
                if (empty($element['PROPERTY_LOCATION_VALUE'])) {
                    continue;
                }

                $storeCodes = [];
                $stores = $storeService->getByLocation(
                    $element['PROPERTY_LOCATION_VALUE'],
                    StoreService::TYPE_SHOP
                );
                /** @var Store $store */
                foreach ($stores as $store) {
                    $storeCodes[] = $store->getXmlId();
                }

                $result[CitiesSectionCode::MOSCOW_REGION][] = [
                    'NAME'  => $element['NAME'],
                    'CODE'  => $element['PROPERTY_LOCATION_VALUE'],
                    'SHOPS' => $storeCodes,
                ];
            }

            return $result;
        };

        return (new BitrixCache())
            ->withId(__METHOD__)
            ->withIblockTag(
                IblockUtils::getIblockId(IblockType::REFERENCE_BOOKS, IblockCode::CITIES)
            )
            ->resultOf($getAvailableCities);
    }

    /**
     * Поиск местоположения по названию
     *
     * @param $query
     * @param null $limit
     * @param bool $exact
     * @param array $additionalFilter
     *
     * @return array
     * @throws CityNotFoundException
     */
    public function findLocation(
        string $query,
        int $limit = null,
        bool $exact = false,
        array $additionalFilter = []
    ): array {
        $findLocation = function () use ($query, $limit, $exact, $additionalFilter) {
            if (empty($query)) {
                throw new CityNotFoundException('Город не найден');
            }
            CBitrixComponent::includeComponentClass("bitrix:sale.location.selector.search");

            $filter = [
                'NAME.LANGUAGE_ID' => LANGUAGE_ID,
                'PHRASE'           => $query,
            ];

            if ($exact) {
                $filter['NAME.NAME'] = $query;
            }

            if (!empty($additionalFilter)) {
                $filter = array_merge($filter, $additionalFilter);
            }

            // Bitrix не ищет по массиву TYPE_ID
            $typeIdFilter = [];
            if (\is_array($filter['TYPE_ID'])) {
                $typeIdFilter = $filter['TYPE_ID'];
                $filter['TYPE_ID'] = reset($typeIdFilter);
            }

            $result = [];
            do {
                $result = array_merge($result, $this->findWithLocationSearchComponent($filter, $limit));

                if ($limit && \count($result) >= $limit) {
                    break;
                }
            } while ($filter['TYPE_ID'] = next($typeIdFilter));

            if ($limit) {
                $result = array_slice($result, 0, $limit);
            }

            return $result;
        };

        $result = (new BitrixCache())
            ->withId($query . json_encode($additionalFilter) . (int)$limit . (int)$exact)
            ->resultOf($findLocation);

        if (empty($result)) {
            throw new CityNotFoundException('Город не найден');
        }

        return $result;
    }

    /**
     * Поиск местоположения по коду
     *
     * @param string $code
     * @param array $additionalFilter
     *
     * @return array|false
     */
    public function findLocationByCode(string $code, array $additionalFilter = []): array
    {
        $findLocation = function () use ($code, $additionalFilter) {
            $filter = ['CODE' => $code];
            if (!empty($additionalFilter) && is_array($additionalFilter)) {
                $filter = array_merge($filter, $additionalFilter);
            }

            $location = reset($this->findWithLocationSearchComponent($filter, 1));

            return $location;
        };

        return (new BitrixCache())
            ->withId(
                __METHOD__ . json_encode(
                    [
                        'code'   => $code,
                        'filter' => $additionalFilter,
                    ]
                )
            )
            ->resultOf($findLocation);
    }

    /**
     * Поиск местоположений с типом "город" и "деревня" по названию
     *
     * @param string $query
     * @param int|null $limit
     * @param bool $exact
     *
     * @return array
     */
    public function findLocationCity(
        string $query,
        string $parentName = '',
        int $limit = null,
        bool $exact = false
    ): array {
        $filter = [
            'TYPE_ID' => array_values(
                $this->getTypeIdsByCodes(
                    [
                        static::TYPE_CITY,
                        static::TYPE_VILLAGE,
                    ]
                )
            ),
        ];

        // можно было бы сначала найти PARENT_ID по $parentName,
        // но так мы получим результат одним запросом
        if ($parentName) {
            $exact = false;
            $query = $parentName . ' ' . $query;
        }

        return $this->findLocation(
            $query,
            $limit,
            $exact,
            $filter
        );
    }

    /**
     * Поиск местоположений с типом "город" или "деревня" по коду
     *
     * @param string $code
     *
     * @return array
     * @throws CityNotFoundException
     */
    public function findLocationCityByCode(string $code = ''): array
    {
        $city = false;
        if ($code) {
            $city = $this->findLocationByCode(
                $code,
                [
                    'TYPE_ID' => array_values(
                        $this->getTypeIdsByCodes(
                            [
                                static::TYPE_CITY,
                                static::TYPE_VILLAGE,
                            ]
                        )
                    ),
                ]
            );
        }

        if (!$city) {
            throw new CityNotFoundException('Город не найден');
        }

        return [
            'NAME' => $city['NAME'],
            'CODE' => $city['CODE'],
            'PATH' => $city['PATH'],
        ];
    }

    /**
     * Возвращает дефолтное местоположение
     *
     * @return array
     */
    public function getDefaultLocation(): array
    {
        try {
            return $this->findLocationCityByCode(static::LOCATION_CODE_MOSCOW);
        } catch (CityNotFoundException $e) {
        }

        return [];
    }

    /**
     * Получение кода текущего местоположения
     *
     * @return string
     */
    public function getCurrentLocation(): string
    {
        /** @var UserService $userService */
        $userService = Application::getInstance()
                                  ->getContainer()
                                  ->get('FourPaws\UserBundle\Service\UserCitySelectInterface');

        if ($location = $userService->getSelectedCity()) {
            return $location['CODE'];
        }

        return (string)$this->getDefaultLocation()['CODE'];
    }

    /**
     * Получение групп местоположений
     *
     * @param bool $withLocations если true, то в каждой группе содержать ключ LOCATIONS,
     *                            содержащий массив кодов местоположений этой группы
     */
    public function getLocationGroups($withLocations = true): array
    {
        $getGroups = function () use ($withLocations) {
            $result = [];
            $select = ['GROUP.ID', 'GROUP.CODE', 'GROUP.NAME', 'GROUP.SORT'];

            if ($withLocations) {
                $select[] = 'LOCATION.CODE';
            }

            $groups = GroupLocationTable::getList(
                [
                    'select' => $select,
                    'order'  => ['GROUP.SORT' => 'ASC'],
                ]
            );

            while ($group = $groups->fetch()) {
                $item = [
                    'ID'   => $group['SALE_LOCATION_GROUP_LOCATION_GROUP_ID'],
                    'CODE' => $group['SALE_LOCATION_GROUP_LOCATION_GROUP_CODE'],
                    'NAME' => $group['SALE_LOCATION_GROUP_LOCATION_GROUP_NAME_NAME'],
                ];

                if ($withLocations) {
                    if (isset($result[$group['SALE_LOCATION_GROUP_LOCATION_GROUP_CODE']])) {
                        $item = $result[$group['SALE_LOCATION_GROUP_LOCATION_GROUP_CODE']];
                    }
                    $item['LOCATIONS'][] = $group['SALE_LOCATION_GROUP_LOCATION_LOCATION_CODE'];
                }

                $result[$group['SALE_LOCATION_GROUP_LOCATION_GROUP_CODE']] = $item;
            }

            return $result;
        };

        return (new BitrixCache())
            ->withId(__METHOD__ . intVal($withLocations))
            ->resultOf($getGroups);
    }

    /**
     * Получение эл-та из HL-блока Cities по коду местоположения
     *
     * @return City|null
     */
    public function getDefaultCity()
    {
        $citiesTable = Application::getInstance()->getContainer()->get('bx.hlblock.cities');

        return (new CityQuery($citiesTable::query()))->withFilterParameter('UF_DEFAULT', true)
                                                     ->exec()
                                                     ->first();
    }

    /**
     * Получение эл-та из HL-блока Cities по коду местоположения
     *
     * @param $locationCode
     *
     * @return ModelInterface|null
     */
    public function getCity($locationCode)
    {
        try {
            return City::createFromLocation($locationCode);
        } catch (CityNotFoundException $e) {
            return null;
        }
    }

    /**
     * Получение эл-та из HL-блока,
     * привязанного к выбранному городу пользователя
     *
     * @return ModelInterface|null
     */
    public function getCurrentCity()
    {

        if ($locationCode = $this->getCurrentLocation()) {
            if ($city = $this->getCity($locationCode)) {
                return $city;
            }
        }

        return $this->getDefaultCity();
    }

    /**
     * @param array $typeCodes
     *
     * @return array
     */
    protected function getTypeIdsByCodes(array $typeCodes): array
    {
        $getTypeIds = function () use ($typeCodes) {
            $result = [];
            $types = TypeTable::getList(
                [
                    'filter' => ['CODE' => $typeCodes],
                    'select' => ['ID', 'CODE'],
                ]
            );

            while ($type = $types->fetch()) {
                $result[$type['CODE']] = $type['ID'];
            }

            return $result;
        };

        return (new BitrixCache())
            ->withId(__METHOD__ . json_encode($typeCodes))
            ->resultOf($getTypeIds);
    }

    /**
     * Ищет местоположения по заданному фильтру
     * с помощью CBitrixLocationSelectorSearchComponent
     *
     * @param $filter
     * @param $limit
     *
     * @return array
     */
    private function findWithLocationSearchComponent($filter, $limit)
    {
        $result = [];

        CBitrixComponent::includeComponentClass('bitrix:sale.location.selector.search');

        $data = CBitrixLocationSelectorSearchComponent::processSearchRequestV2(
            [
                'select'      => [
                    'CODE',
                    'VALUE'   => 'ID',
                    'DISPLAY' => 'NAME.NAME',
                ],
                'filter'      => $filter,
                'additionals' => ['PATH'],
                'PAGE_SIZE'   => $limit,
                'PAGE'        => 0,
            ]
        );
        foreach ($data['ITEMS'] as $item) {
            $path = [];
            foreach ($item['PATH'] as $pathId) {
                if (!isset($data['ETC']['PATH_ITEMS'][$pathId])) {
                    continue;
                }
                $pathItem = $data['ETC']['PATH_ITEMS'][$pathId];
                $path[] = [
                    'NAME' => $pathItem['DISPLAY'],
                    'CODE' => $pathItem['CODE'],
                ];
            }
            $result[] = [
                'CODE' => $item['CODE'],
                'NAME' => $item['DISPLAY'],
                'PATH' => $path,
            ];
        }

        return $result;
    }
}
