<?php

namespace FourPaws\Location;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Highloadblock\DataManager;
use Bitrix\Sale\Location\ExternalTable;
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
use FourPaws\UserBundle\Service\UserService;
use WebArch\BitrixCache\BitrixCache;

class LocationService
{
    const TYPE_CITY = 'CITY';

    const TYPE_VILLAGE = 'VILLAGE';

    const LOCATION_CODE_MOSCOW = '0000073738';

    const DEFAULT_REGION_CODE = 'IR77';

    const REGION_SERVICE_CODE = 'REGION';

    protected $dataManager;

    /**
     * Возвращает код выбранного региона.
     *
     * @param $locationCode
     *
     * @return string
     */
    public function getCurrentRegionCode(string $locationCode): string
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

    public function __construct(DataManager $dataManager)
    {
        $this->dataManager = $dataManager;
    }

    /**
     * @return array
     */
    public function getAvailableCities(): array
    {
        $getAvailableCities = function () {
            $iblockId = IblockUtils::getIblockId(IblockType::REFERENCE_BOOKS, IblockCode::CITIES);

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

                $result[CitiesSectionCode::POPULAR][] = [
                    'NAME'  => $element['NAME'],
                    'CODE'  => $element['PROPERTY_LOCATION_VALUE'],
                    'SHOPS' => array_column(
                        $this->getShopsByCity($element['PROPERTY_LOCATION_VALUE']),
                        'CODE'
                    ),
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

                $result[CitiesSectionCode::MOSCOW_REGION][] = [
                    'NAME'  => $element['NAME'],
                    'CODE'  => $element['PROPERTY_LOCATION_VALUE'],
                    'SHOPS' => array_column(
                        $this->getShopsByCity($element['PROPERTY_LOCATION_VALUE']),
                        'CODE'
                    ),
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
        if (is_array($filter['TYPE_ID'])) {
            $typeIdFilter = $filter['TYPE_ID'];
            $filter['TYPE_ID'] = reset($typeIdFilter);
        }

        $result = [];
        do {
            $result = array_merge($result, $this->findWithLocationSearchComponent($filter, $limit));

            if ($limit && count($result) >= $limit) {
                break;
            }
        } while ($filter['TYPE_ID'] = next($typeIdFilter));

        if (empty($result)) {
            throw new CityNotFoundException('Город не найден');
        }

        if ($limit) {
            $result = array_slice($result, 0, $limit);
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
     * Получение эл-та из HL-блока Cities по коду местоположения
     *
     * @return City|null
     */
    public function getDefaultCity()
    {
        return (new CityQuery($this->dataManager::query()))->withFilterParameter('UF_DEFAULT', true)
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
        /** @var UserService $userService */
        $userService = Application::getInstance()
                                  ->getContainer()
                                  ->get('FourPaws\UserBundle\Service\UserCitySelectInterface');

        if ($locationCode = $userService->getSelectedCity()['CODE']) {
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

    /**
     * @param $locationCode
     *
     * @return array
     */
    public function getShopsByCity($locationCode): array
    {
        /* @todo implement this */
        return [];
    }
}
