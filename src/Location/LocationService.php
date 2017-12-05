<?php

namespace FourPaws\Location;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Sale\Location\LocationTable;
use Bitrix\Sale\Location\Search\Finder;
use Bitrix\Sale\Location\TypeTable;
use FourPaws\Location\Exception\CityNotFoundException;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Enum\CitiesSectionCode;
use WebArch\BitrixCache\BitrixCache;
use CIBlockSection;
use CIBlockElement;
use CBitrixComponent;
use CBitrixLocationSelectorSearchComponent;

class LocationService
{
    const TYPE_CITY = 'CITY';

    const TYPE_VILLAGE = 'VILLAGE';

    const LOCATION_CODE_MOSCOW = '0000073738';

    /**
     * @param string $code
     * @param string $name
     *
     * @return bool
     * @throws \Exception
     */
    public function selectCity(string $code = '', string $name = ''): bool
    {
        if (!empty($code)) {
            $cities = [];
        } elseif (!empty($name)) {
            $cities = $this->findCity($name, 1, true);
        }

        if (!empty($cities)) {
            $_COOKIE['user_city_id'] = reset($cities)['CODE'];

            return true;
        }

        throw new CityNotFoundException('Город указан неверно.');
    }

    /**
     * @return array
     */
    public function getAvailableCities(): array
    {
        $getAvailableCities = function () {
            $iblockId = IblockUtils::getIblockId(IblockType::REFERENCE_BOOKS, IblockCode::CITIES);

            $result = [];
            $sort = ['SORT' => 'ASC', 'ID' => 'ASC'];
            $filter = ['IBLOCK_ID' => $iblockId, 'SECTION_CODE' => CitiesSectionCode::POPULAR];
            $select = ['ID', 'NAME', 'PROPERTY_LOCATION'];

            // При выборе популярных городов учитываем сортировку
            $elements = CIBlockElement::GetList($sort, $filter, false, false, $select);
            while ($element = $elements->Fetch()) {
                $result[CitiesSectionCode::POPULAR][] = [
                    'NAME'     => $element['NAME'],
                    'LOCATION' => $element['PROPERTY_LOCATION_VALUE'],
                ];
            }

            // При выборе городов Московской обл. не учитываем сортировку
            $filter['SECTION_CODE'] = CitiesSectionCode::MOSCOW_REGION;
            unset($sort['SORT']);
            $elements = CIBlockElement::GetList($sort, $filter, false, false, $select);
            while ($element = $elements->Fetch()) {
                $result[CitiesSectionCode::MOSCOW_REGION][] = [
                    'NAME' => $element['NAME'],
                    'CODE' => $element['PROPERTY_LOCATION_VALUE'],
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
     * @param $query
     * @param null $limit
     * @param bool $exact
     * @param array $additionalFilter
     *
     * @return array
     * @throws CityNotFoundException
     */
    public function find(string $query, int $limit = null, bool $exact = false, array $additionalFilter = []): array
    {
        if (empty($query)) {
            throw new CityNotFoundException('Город не найден');
        }
        CBitrixComponent::includeComponentClass("bitrix:sale.location.selector.search");

        $filter = [
            'NAME.LANGUAGE_ID' => LANGUAGE_ID,
        ];

        if (!$exact) {
            $filter['NAME.NAME'] = $query; // закомментить, чтобы поиск был не только по имени города
            $filter['=PHRASE'] = $query;
        } else {
            $filter['=NAME.NAME'] = $query;
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
     * @param string $code
     * @param array $additionalFilter
     *
     * @return array|false
     */
    public function findByCode(string $code, array $additionalFilter = [])
    {
        $filter = ['CODE' => $code];
        if (!empty($additionalFilter) && is_array($additionalFilter)) {
            $filter = array_merge($filter, $additionalFilter);
        }

        return LocationTable::getList(
            [
                'filter' => $filter,
                'select' => ['ID', 'NAME.NAME', 'CODE', 'TYPE_ID'],
                'limit'  => 1,
            ]
        )->fetch();
    }

    /**
     * @param string $query
     * @param int|null $limit
     * @param bool $exact
     *
     * @return array
     */
    public function findCity(string $query, int $limit = null, bool $exact = false): array
    {
        return $this->find(
            $query,
            $limit,
            $exact,
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

    /**
     * @param string $code
     *
     * @return array
     * @throws CityNotFoundException
     */
    public function findCityByCode(string $code = ''): array
    {
        $city = false;
        if ($code) {
            $city = $this->findByCode(
                $code,
                [
                    '=TYPE.CODE' => [static::TYPE_CITY, static::TYPE_VILLAGE],
                ]
            );
        }

        if (!$city) {
            throw new CityNotFoundException('Город не найден');
        }

        return [
            'NAME' => $city['SALE_LOCATION_LOCATION_NAME_NAME'],
            'CODE' => $city['CODE'],
        ];
    }

    /**
     * @return array
     */
    public function getDefaultCity(): array
    {
        return $this->findCityByCode(static::LOCATION_CODE_MOSCOW);
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
}
