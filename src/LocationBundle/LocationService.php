<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\LocationBundle;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Sale\Location\ExternalTable;
use Bitrix\Sale\Location\GroupLocationTable;
use Bitrix\Sale\Location\TypeTable;
use CBitrixComponent;
use CBitrixLocationSelectorSearchComponent;
use CIBlockElement;
use Exception;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\External\DaDataService;
use FourPaws\External\Exception\DaDataExecuteException;
use FourPaws\LocationBundle\Enum\CitiesSectionCode;
use FourPaws\LocationBundle\Exception\CityNotFoundException;
use FourPaws\LocationBundle\Model\City;
use FourPaws\LocationBundle\Query\CityQuery;
use FourPaws\PersonalBundle\Entity\Address;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\UserCitySelectInterface;
use FourPaws\UserBundle\Service\UserService;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use WebArch\BitrixCache\BitrixCache;

/**
 * Class LocationService
 *
 * @package FourPaws\LocationBundle
 */
class LocationService
{
    use LazyLoggerAwareTrait;

    public const TYPE_CITY = 'CITY';

    public const TYPE_VILLAGE = 'VILLAGE';

    public const TYPE_REGION = 'REGION';

    public const LOCATION_CODE_MOSCOW = '0000073738';

    public const DEFAULT_REGION_CODE = 'IR77';

    public const REGION_SERVICE_CODE = 'REGION';

    /**
     * @var DaDataService
     */
    protected $daDataService;

    /**
     * LocationService constructor.
     *
     * @param DaDataService $daDataService
     */
    public function __construct(DaDataService $daDataService)
    {
        $this->daDataService = $daDataService;
    }

    /**
     * Возвращает код текущего региона.
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws NotAuthorizedException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ApplicationCreateException
     * @throws Exception
     * @return string
     */
    public function getCurrentRegionCode(): string
    {
        return $this->getRegionCode($this->getCurrentLocation());
    }

    /**
     * Возвращает код региона по коду местоположения
     *
     * @param string $locationCode
     *
     * @throws Exception
     * @return string
     */
    public function getRegionCode(string $locationCode): string
    {
        if (!$locationCode || !($location = $this->findLocationByCode($locationCode))) {
            return self::DEFAULT_REGION_CODE;
        }

        $getRegionCode = function () use ($location) {
            /** @noinspection OffsetOperationsInspection */
            $filter = [
                'LOCATION.CODE' => $location['CODE'],
                'SERVICE.CODE'  => self::REGION_SERVICE_CODE,
            ];

            /** @noinspection OffsetOperationsInspection */
            if (!empty($location['PATH'])) {
                /** @noinspection OffsetOperationsInspection */
                $filter['LOCATION.CODE'] = \array_merge(
                    [$filter['LOCATION.CODE']],
                    \array_column($location['PATH'], 'CODE')
                );
            }
            $region = ExternalTable::query()
                ->setFilter($filter)
                ->setLimit(1)
                ->exec()
                ->fetch();

            /** @noinspection OffsetOperationsInspection */
            return $region['XML_ID'] ?: self::DEFAULT_REGION_CODE;
        };

        try {
            $data = (new BitrixCache())
                ->withId($locationCode)
                ->resultOf($getRegionCode);
            return $data['result'];
        } catch (Exception $e) {
            return $getRegionCode();
        }
    }

    /**
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws IblockNotFoundException
     * @throws Exception
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
                $stores = $storeService->getStoresByLocation(
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
                $stores = $storeService->getStoresByLocation(
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
            ->withIblockTag(IblockUtils::getIblockId(IblockType::REFERENCE_BOOKS, IblockCode::CITIES))
            ->resultOf($getAvailableCities);
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Поиск местоположения по названию
     *
     * @param string $query
     * @param int    $limit
     * @param bool   $exact
     * @param array  $additionalFilter
     *
     * @throws CityNotFoundException
     * @return array
     */
    public function findLocation(
        string $query,
        int $limit = null,
        bool $exact = false,
        array $additionalFilter = []
    ): array {
        $findLocation = function () use ($query, $limit, $exact, $additionalFilter) {
            $filter = [];
            if ($query) {
                $filter = [
                    'NAME.LANGUAGE_ID' => LANGUAGE_ID,
                    'PHRASE'           => $query,
                ];

                if ($exact) {
                    $filter['NAME.NAME'] = $query;
                }
            }


            if (!empty($additionalFilter)) {
                $filter = \array_merge($filter, $additionalFilter);
            }

            // Bitrix не ищет по массиву TYPE_ID
            $typeIdFilter = [];
            if (\is_array($filter['TYPE_ID'])) {
                $typeIdFilter = $filter['TYPE_ID'];
                $filter['TYPE_ID'] = \reset($typeIdFilter);
            }

            $result = [];
            do {
                $result = \array_merge($result, $this->findWithLocationSearchComponent($filter, $limit));

                if ($limit && \count($result) >= $limit) {
                    break;
                }
            } while ($filter['TYPE_ID'] = \next($typeIdFilter));

            if ($limit) {
                $result = \array_slice($result, 0, $limit);
            }

            return $result;
        };

        try {
            $result = (new BitrixCache())
                ->withId($query . \json_encode($additionalFilter) . $limit . (int)$exact)
                ->resultOf($findLocation);
        } catch (\Exception $e) {
            $result = $findLocation();
        }

        if (empty($result)) {
            throw new CityNotFoundException('Город не найден');
        }

        return $result;
    }

    /**
     * Поиск местоположения по коду
     *
     * @param string $code
     * @param array  $additionalFilter
     *
     * @return array
     */
    public function findLocationByCode(string $code, array $additionalFilter = []): array
    {
        $findLocation = function () use ($code, $additionalFilter) {
            $filter = ['CODE' => $code];
            if (!empty($additionalFilter) && \is_array($additionalFilter)) {
                $filter = array_merge($filter, $additionalFilter);
            }
            $locations = $this->findWithLocationSearchComponent($filter, 1);
            return reset($locations);
        };

        try {
            return (new BitrixCache())
                ->withId(
                    __METHOD__ . \json_encode(
                        [
                            'code'   => $code,
                            'filter' => $additionalFilter,
                        ]
                    )
                )
                ->resultOf($findLocation);
        } catch (Exception $e) {
            $this->log()->error(sprintf('failed to get location: %s', $e->getMessage()), [
                'code' => $code,
                'filter' => $additionalFilter
            ]);
            return [];
        }
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Поиск местоположений с типом "город" и "деревня" по названию
     *
     * @param string   $query
     * @param string   $parentName
     * @param null|int $limit
     * @param bool     $exact
     *
     * @throws CityNotFoundException
     * @return array
     */
    public function findLocationCity(
        string $query,
        string $parentName = '',
        int $limit = null,
        bool $exact = false
    ): array {
        $filter = [
            'TYPE_ID' => \array_values(
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
     * @throws CityNotFoundException
     * @return array
     */
    public function findLocationCityByCode(string $code): array
    {
        if ($code) {
            $typeIds = \array_values(
                $this->getTypeIdsByCodes(
                    [
                        static::TYPE_CITY,
                        static::TYPE_VILLAGE,
                    ]
                )
            );
            return $this->findLocationByCode(
                $code,
                [
                    'TYPE_ID' => $typeIds,
                ]
            );
        }

        throw new CityNotFoundException('Город не найден');
    }

    /**
     * @param string $cityCode
     *
     * @return array
     */
    public function findLocationRegion(string $cityCode): array
    {
        $result = [];
        try {
            $data = $this->findLocationCityByCode($cityCode);
            $path = $data['PATH'];

            foreach ($path as $pathItem) {
                if (($pathItem['CODE'] === static::LOCATION_CODE_MOSCOW) ||
                    ($pathItem['TYPE'] === static::TYPE_REGION)
                ) {
                    $result = $pathItem;
                    break;
                }
            }
        } catch (CityNotFoundException $e) {
        }

        return $result;
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
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws NotAuthorizedException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ApplicationCreateException
     * @return string
     */
    public function getCurrentLocation(): string
    {
        /** @var UserService $userService */
        $userService = Application::getInstance()
            ->getContainer()
            ->get(UserCitySelectInterface::class);

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
     *
     * @return array
     */
    public function getLocationGroups($withLocations = true): array
    {
        $getGroups = function () use ($withLocations) {
            $result = [];
            $select = ['GROUP.ID', 'GROUP.CODE', 'GROUP.NAME', 'GROUP.SORT'];

            if ($withLocations) {
                $select[] = 'LOCATION.CODE';
            }

            $groups = GroupLocationTable::query()
                ->setSelect($select)
                ->setOrder(['GROUP.SORT' => 'ASC'])
                ->exec();

            while ($group = $groups->fetch()) {
                /** @noinspection OffsetOperationsInspection */
                $item = [
                    'ID'   => $group['SALE_LOCATION_GROUP_LOCATION_GROUP_ID'],
                    'CODE' => $group['SALE_LOCATION_GROUP_LOCATION_GROUP_CODE'],
                    'NAME' => $group['SALE_LOCATION_GROUP_LOCATION_GROUP_NAME_NAME'],
                ];

                if ($withLocations) {
                    /** @noinspection OffsetOperationsInspection */
                    if (isset($result[$group['SALE_LOCATION_GROUP_LOCATION_GROUP_CODE']])) {
                        /** @noinspection OffsetOperationsInspection */
                        $item = $result[$group['SALE_LOCATION_GROUP_LOCATION_GROUP_CODE']];
                    }
                    /** @noinspection OffsetOperationsInspection */
                    $item['LOCATIONS'][] = $group['SALE_LOCATION_GROUP_LOCATION_LOCATION_CODE'];
                }

                /** @noinspection OffsetOperationsInspection */
                $result[$group['SALE_LOCATION_GROUP_LOCATION_GROUP_CODE']] = $item;
            }

            return $result;
        };

        try {
            return (new BitrixCache())
                ->withId(__METHOD__ . (int)$withLocations)
                ->resultOf($getGroups);
        } catch (\Exception $e) {
            $this->log()->error(sprintf('failed to get location groups: %s', $e->getMessage()), [
                'withLocations' => (int)$withLocations
            ]);
            return [];
        }
    }

    /**
     * Получение эл-та из HL-блока Cities по коду местоположения
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @return null|City
     */
    public function getDefaultCity(): ?City
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
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @return null|City
     */
    public function getCity($locationCode): ?City
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
     * @throws NotAuthorizedException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @return null|City
     */
    public function getCurrentCity(): ?City
    {
        if (($locationCode = $this->getCurrentLocation()) && ($city = $this->getCity($locationCode))) {
            return $city;
        }

        return $this->getDefaultCity();
    }

    /**
     * Валидация адреса
     *
     * @param Address $address
     *
     * @return bool
     */
    public function validateAddress(Address $address): bool
    {
        $result = false;
        try {
            $result = $this->daDataService->isValidAddress($address);
        } catch (DaDataExecuteException $e) {
            $this->log()->error(sprintf('failed to validate address: %s', $e->getMessage()), [
                'address' => (string)$address
            ]);
        }

        return $result;
    }

    /**
     * @param array $typeCodes
     *
     * @return array
     */
    protected function getTypeIdsByCodes(array $typeCodes): array
    {
        $typeIds = $this->getTypeIds();
        return array_intersect_key($typeIds, array_flip($typeCodes));
    }

    /**
     * @return array
     */
    protected function getTypeIds(): array
    {
        $getTypeIds = function () {
            $result = [];
            $types = TypeTable::query()
                ->addSelect('ID')
                ->addSelect('CODE')
                ->exec();


            while (($type = $types->fetch()) && \is_array($type)) {
                /**
                 * @var array $type
                 */
                $result[$type['CODE'] ?? ''] = $type['ID'];
            }

            return $result;
        };

        try {
            return (new BitrixCache())
                ->withId(__METHOD__)
                ->resultOf($getTypeIds);
        } catch (\Exception $e) {
            return $getTypeIds();
        }
    }

    /**
     * Ищет местоположения по заданному фильтру
     * с помощью CBitrixLocationSelectorSearchComponent
     *
     * @param $filter
     * @param $limit
     *
     * @throws Exception
     * @return array
     */
    private function findWithLocationSearchComponent($filter, $limit): array
    {
        $result = [];

        CBitrixComponent::includeComponentClass('bitrix:sale.location.selector.search');

        $data = CBitrixLocationSelectorSearchComponent::processSearchRequestV2(
            [
                'select'      => [
                    'CODE',
                    'VALUE'   => 'ID',
                    'DISPLAY' => 'NAME.NAME',
                    'TYPE_ID',
                ],
                'filter'      => $filter,
                'additionals' => ['PATH'],
                'PAGE_SIZE'   => $limit,
                'PAGE'        => 0,
            ]
        );

        $types = array_flip($this->getTypeIds());
        /** @var array $item */
        foreach ($data['ITEMS'] as $item) {
            $path = [];
            /** @var string $pathId */
            foreach ($item['PATH'] as $pathId) {
                if (!isset($data['ETC']['PATH_ITEMS'][$pathId])) {
                    continue;
                }
                $pathItem = $data['ETC']['PATH_ITEMS'][$pathId];
                $path[] = [
                    'NAME' => $pathItem['DISPLAY'],
                    'CODE' => $pathItem['CODE'],
                    'TYPE' => $types[$pathItem['TYPE_ID']]
                ];
            }
            $result[] = [
                'ID' => $item['VALUE'],
                'CODE' => $item['CODE'],
                'NAME' => $item['DISPLAY'],
                'TYPE' => $types[$item['TYPE_ID']],
                'PATH' => $path,
            ];
        }

        return $result;
    }
}
