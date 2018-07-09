<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\LocationBundle;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Location\ExternalServiceTable;
use Bitrix\Sale\Location\ExternalTable;
use Bitrix\Sale\Location\GroupLocationTable;
use Bitrix\Sale\Location\LocationTable;
use Bitrix\Sale\Location\Name\LocationTable as NameLocationTable;
use Bitrix\Sale\Location\TypeTable;
use CBitrixComponent;
use CBitrixLocationSelectorSearchComponent;
use CIBlockElement;
use Exception;
use FourPaws\Adapter\DaDataLocationAdapter;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\External\DaDataService;
use FourPaws\External\Exception\DaDataExecuteException;
use FourPaws\LocationBundle\Entity\Address;
use FourPaws\LocationBundle\Enum\CitiesSectionCode;
use FourPaws\LocationBundle\Exception\AddressSplitException;
use FourPaws\LocationBundle\Exception\CityNotFoundException;
use FourPaws\LocationBundle\Model\City;
use FourPaws\LocationBundle\Query\CityQuery;
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

    public const TYPE_SUBREGION = 'SUBREGION';

    public const TYPE_REGION = 'REGION';

    public const LOCATION_CODE_MOSCOW = '0000073738';

    public const LOCATION_CODE_MOSCOW_REGION = '0000028025';

    public const DEFAULT_REGION_CODE = 'IR77';

    public const REGION_SERVICE_CODE = 'REGION';

    public const KLADR_SERVICE_CODE = 'KLADR';

    /**
     * @var DaDataService
     */
    protected $daDataService;

    /** @var array */
    private $locationsByCode = [];
    private $locationsById = [];

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
     * @param string $cityCode
     *
     * @return int
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getRegion(string $cityCode): int
    {
        $locList = LocationTable::query()->setFilter(['=CODE' => $cityCode])->setSelect([
            'ID',
            'REGION_ID',
            'PARENT_ID',
            'TYPE_CODE'                => 'TYPE.CODE',
            'PARENTS_PARENT_ID'        => 'PARENTS.ID',
            'PARENTS_PARENT_TYPE_CODE' => 'PARENTS.TYPE.CODE',
        ])->setCacheTtl(360000)->exec()->fetchAll();
        foreach ($locList as $locItem) {
            if ($locItem['TYPE_CODE'] === 'REGION') {
                return $locItem['ID'];
            }
            if ($locItem['PARENTS_PARENT_TYPE_CODE'] === 'REGION') {
                return $locItem['PARENTS_PARENT_ID'];
            }
        }
        return 0;
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
     * @return string
     */

    /**
     * @param string $locationCode
     *
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
                ->setSelect(['XML_ID'])
                ->setFilter($filter)
                ->setLimit(1)
                ->registerRuntimeField(
                    new ReferenceField(
                        'SERVICE',
                        ExternalServiceTable::getEntity(),
                        ['=this.SERVICE_ID' => 'ref.ID']
                    )
                )
                ->registerRuntimeField(
                    new ReferenceField(
                        'LOCATION',
                        LocationTable::getEntity(),
                        ['=this.LOCATION_ID' => 'ref.ID']
                    )
                )
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
     * @param string $location
     *
     * @return string
     */
    public function getRegionNumberCode(string $location): string
    {
        return substr($this->getRegionCode($location), 2);
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
            $filter = [
                'IBLOCK_ID' => $iblockId,
                'SECTION_CODE' => CitiesSectionCode::POPULAR,
                'ACTIVE' => BitrixUtils::BX_BOOL_TRUE
            ];
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
     * @deprecated
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
     * Поиск местоположения по названию
     *
     * @param Query|array $queryParams
     * @param int         $limit
     * @param bool        $needPath
     *
     * @return array
     */
    public function findLocationNew(
        $queryParams,
        int $limit = 0,
        bool $needPath = true
    ): array {
        $cacheFinder = function () use ($queryParams, $limit, $needPath) {
            if (!($queryParams instanceof Query)) {
                /** сразу в селект не добалять позиции с join - получать их позже - для скорости
                 * поиск по коду и только по названию без родителя будет быстрее */
                $query = LocationTable::query()->setFilter($queryParams)->setSelect([
                    'ID',
                    'CODE',
                    'DEPTH_LEVEL',
                    'LEFT_MARGIN',
                    'RIGHT_MARGIN',
                    'TYPE_ID',
                ]);
            } else {
                $query = $queryParams;
            }
            if ($limit > 0) {
                $query->setLimit($limit);
            }
            $res = $query->exec();
            $locations = [];
            $typeList = [];
            while ($item = $res->fetch()) {
                $typeList[$item['TYPE_ID']][] = $item['ID'];
                /** для получения родетелей от запроса в цикле не уйти -
                 * если делать в основ запросе- то запрос буде слишком тяжелый,
                 * так как стандартно идет подключение через left_join
                 * в подзапросе уже используем поля с join так как выборка маленькая
                 */
                $parentList = [];
                /** очень долгий запрос на получение родителей */
                if ($needPath) {
                    $parentRes = LocationTable::query()
                        ->where('DEPTH_LEVEL', '<', $item['DEPTH_LEVEL'])
                        ->where('LEFT_MARGIN', '<', $item['LEFT_MARGIN'])
                        ->where('RIGHT_MARGIN', '>', $item['RIGHT_MARGIN'])
                        ->setSelect([
                            'ID',
                            'CODE',
                            'DISPLAY'    => 'NAME.NAME',
                            '_TYPE_ID'   => 'TYPE.ID',
                            '_TYPE_CODE' => 'TYPE.CODE',
                            '_TYPE_NAME' => 'TYPE.NAME.NAME',
                        ])->exec();
                    while ($parentItem = $parentRes->fetch()) {
                        $parentItem['NAME'] = $parentItem['DISPLAY'];
                        unset($parentItem['DISPLAY']);
                        $parentItem['TYPE'] = $this->stringArrayToArray($parentItem, 'TYPE');
                        $parentList[] = $parentItem;
                    }
                    $item['PATH'] = $parentList;
                }
                $locations[$item['ID']] = $item;
            }
            if (!empty($locations)) {
                $locationIds = array_keys($locations);
                $res = NameLocationTable::query()->setSelect([
                    'NAME',
                    'LOCATION_ID',
                ])->setFilter(['=LOCATION_ID' => $locationIds])->exec();
                while ($item = $res->fetch()) {
                    $locations[$item['LOCATION_ID']]['NAME'] = $item['NAME'];
                }
                $res = TypeTable::query()->setSelect([
                    'ID',
                    'CODE',
                    'DISPLAY' => 'NAME.NAME',
                ])->setFilter(['=ID' => array_keys($typeList)])->exec();
                while ($item = $res->fetch()) {
                    if (\is_array($typeList[$item['ID']])) {
                        foreach ($typeList[$item['ID']] as $itemId) {
                            $locations[$itemId]['TYPE'] = [
                                'ID'   => $item['ID'],
                                'CODE' => $item['CODE'],
                                'NAME' => $item['DISPLAY'],
                            ];
                        }
                    }
                }
            } else {
                return [];
            }
            return $locations;
        };
        try {
            return (new BitrixCache())
                ->withTag('location_finder')
                ->withTime(360000)
                ->withId(__METHOD__ . serialize($queryParams))
                ->resultOf($cacheFinder);
        } catch (\Exception $e) {
            $this->log()->error(sprintf('failed to get location: %s', $e->getMessage()), [
                'queryParams' => var_export($queryParams, true),
            ]);
            return [];
        }
    }

    /**
     * @param string $code
     * @param        $value
     *
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function findLocationByExtService(string $code, $value): array
    {
        $res = [];
        $value = substr($value, 0 ,11);
        $locations = ExternalTable::query()
            ->setOrder(['LOCATION.DEPTH_LEVEL'])
            ->where('SERVICE_ID', $this->getExternalServiceIdByCode($code))
            ->where('XML_ID', $value)
            ->setSelect(['LOCATION_ID'])
            ->exec()
            ->fetchAll();

        if (!empty($locations)) {
            $locationsIds = [];

            foreach ($locations as $location) {
                $locationsIds[] = $location['LOCATION_ID'];
            }

            if (!empty($locationsIds)) {
                $res = $this->findLocationNew(['=ID' => $locationsIds]);
            }
        }
        return $res;
    }

    /**
     * @param string $code
     *
     * @return int
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getExternalServiceIdByCode(string $code): int
    {
        $services = ExternalServiceTable::query()
            ->where('CODE', $code)
            ->setSelect(['ID'])
            ->setCacheTtl(360000)
            ->exec()
            ->fetchAll();
        $curService = reset($services);
        return (int)$curService['ID'];
    }

    /**
     * Поиск местоположения по коду
     *
     * @param string $code
     *
     * @return array
     */
    public function findLocationByCode(string $code): array
    {
        if (!isset($this->locationsByCode[$code])) {
            $this->locationsByCode[$code] = reset($this->findLocationNew(['=CODE' => $code]));
            if (\is_bool($this->locationsByCode[$code])) {
                $this->locationsByCode[$code] = [];
            }
        }
        return $this->locationsByCode[$code] ?? [];
    }

    /**
     * Поиск местоположения по коду
     *
     * @param int $id
     *
     * @return array
     */
    public function findLocationById(int $id): array
    {
        if (!isset($this->locationsById[$id])) {
            $this->locationsById[$id] = reset($this->findLocationNew(['=ID' => $id]));
            if (\is_bool($this->locationsById[$id])) {
                $this->locationsById[$id] = [];
            }
        }
        return $this->locationsById[$id] ?? [];
    }


    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Поиск местоположений с типом "город" и "деревня" по названию
     *
     * @param string            $query
     * @param string|array|null $parentName
     * @param null|int          $limit
     * @param bool              $exact
     * @param bool              $exactRegion
     *
     * @return array
     */
    public function findLocationCity(
        string $query,
        $parentName = null,
        int $limit = null,
        bool $exact = false,
        bool $exactRegion = false
    ): array {
        $prefix = $exact ? '=' : '?';
        $prefixRegion = $exactRegion ? '=' : '?';
        /** NAME_UPPER в индексе */
        $filter = [
            $prefix . 'NAME.NAME_UPPER' => ToUpper($query),
            'TYPE.CODE'                 => [
                static::TYPE_CITY,
                static::TYPE_VILLAGE,
            ],
        ];
        if ($parentName !== null && !empty($parentName)) {
            if (\is_array($parentName)) {
                if (\count($parentName) > 1) {
                    /** @todo доработать при необходимости по нескольким родителям поиск */
                    $parentFilter = ['LOGIC' => 'AND'];
                    foreach ($parentName as $typeCode => $name) {
                        $filterItem = [
                            $prefixRegion . 'PARENTS.NAME.NAME_UPPER' => ToUpper($name),
                            '=PARENTS.TYPE.CODE'                      => ToUpper($typeCode),
                        ];
                        $filter[] = $filterItem;
                    }
                    $filter[] = $parentFilter;
                } else {
                    $filter[$prefixRegion . 'PARENTS.NAME.NAME_UPPER'] = ToUpper(current($parentName));
                    $filter['=PARENTS.TYPE.CODE'] = ToUpper(key($parentName));
                }

            } else {
                $filter[$prefixRegion . 'PARENTS.NAME.NAME_UPPER'] = ToUpper($parentName);
                $filter['=PARENTS.TYPE.CODE'] = 'REGION';
            }
        }
        return $this->findLocationNew($filter, $limit);
    }

    /**
     * Поиск местоположений по коду
     *
     * @param string $code
     *
     * @return array
     * @throws CityNotFoundException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function findLocationCityByCode(string $code): array
    {
        if ($code) {
            if (!isset($this->locationsByCode[$code])) {
                $this->locationsByCode[$code] = reset($this->findLocationNew([
                    '=CODE'     => $code,
                    'TYPE.CODE' => [static::TYPE_CITY, static::TYPE_VILLAGE],
                ]));
            }
            if (!empty($this->locationsByCode[$code]) && !\is_bool($this->locationsByCode[$code])) {
                return $this->locationsByCode[$code];
            }
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
            if ($cityCode === static::LOCATION_CODE_MOSCOW) {
                $result = $data;
            } else {
                $path = $data['PATH'];

                foreach ($path as $pathItem) {
                    if (($pathItem['CODE'] === static::LOCATION_CODE_MOSCOW) ||
                        ($pathItem['TYPE']['CODE'] === static::TYPE_REGION)
                    ) {
                        $result = $pathItem;
                        break;
                    }
                }
            }
        } catch (CityNotFoundException $e) {
        }

        return $result;
    }

    /**
     * @param string $cityCode
     *
     * @return array
     */
    public function findLocationSubRegion(string $cityCode): array
    {
        $result = [];
        try {
            $data = $this->findLocationCityByCode($cityCode);
            $path = $data['PATH'];

            foreach ($path as $pathItem) {
                if ($pathItem['TYPE']['CODE'] === static::TYPE_SUBREGION) {
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
     * @return string
     */
    public function getCurrentLocation(): string
    {
        try {
            /** @var UserService $userService */
            $userService = Application::getInstance()
                ->getContainer()
                ->get(UserCitySelectInterface::class);

            if ($location = $userService->getSelectedCity()) {
                $result = $location['CODE'];
            } else {
                $result = (string)$this->getDefaultLocation()['CODE'];
            }
        } catch (\Exception $e) {
            $this->log()->error(
                sprintf('Failed to get product list: %s: %s', \get_class($e), $e->getMessage())
            );
            $result = static::LOCATION_CODE_MOSCOW;
        }

        return $result;
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
                ->withTag('location:groups')
                ->resultOf($getGroups);
        } catch (\Exception $e) {
            $this->log()->error(sprintf('failed to get location groups: %s', $e->getMessage()), [
                'withLocations' => (int)$withLocations,
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
        try {
            $address->setValid($this->daDataService->validateAddress((string)$address));
        } catch (DaDataExecuteException $e) {
            $this->log()->error(sprintf('failed to validate address: %s', $e->getMessage()), [
                'address' => (string)$address,
            ]);
        }

        return $address->isValid();
    }

    /**
     * @param string $address
     * @param string $locationCode
     *
     * @throws AddressSplitException
     * @return Address
     */
    public function splitAddress(string $address, string $locationCode = ''): Address
    {
        $splitAddress = function () use ($address, $locationCode) {
            $dadataLocation = $this->daDataService->splitAddress($address);

            if (!$locationCode) {
                $locationCode = (new DaDataLocationAdapter())->convert($dadataLocation)->getCode();
            }

            $result = new Address();
            $result->setLocation($locationCode)
                ->setCity($dadataLocation->getCity() ?: $dadataLocation->getRegion())
                ->setValid($this->daDataService->isValidAddress($dadataLocation))
                ->setStreetPrefix($dadataLocation->getStreetType())
                ->setStreet($dadataLocation->getStreet())
                ->setHouse($dadataLocation->getHouse())
                ->setFlat($dadataLocation->getFlat())
                ->setZipCode($dadataLocation->getPostalCode());

            return ['result' => $result];
        };

        try {
            $result = (new BitrixCache())
                ->withId($address . '_' . $locationCode)
                ->withTime(360000)
                ->resultOf($splitAddress)['result'];
        } catch (\Exception $e) {
            $this->log()->error(
                sprintf('failed to split address: %s: %s', \get_class($e), $e->getMessage()),
                ['address' => $address,]
            );

            throw new AddressSplitException($e->getMessage(), $e->getCode());
        }

        return $result;
    }

    /**
     * @param string $locationCode
     *
     * @return string
     */
    public function getLocationKladrCode(string $locationCode): string
    {
        $getCode = function () use ($locationCode) {
            $result = ExternalTable::query()
                ->setSelect(['XML_ID'])
                ->setFilter([
                    '=SERVICE.CODE'  => static::KLADR_SERVICE_CODE,
                    '=LOCATION.CODE' => $locationCode,
                ])
                ->registerRuntimeField(
                    new ReferenceField(
                        'SERVICE',
                        ExternalServiceTable::getEntity(),
                        ['=this.SERVICE_ID' => 'ref.ID']
                    )
                )
                ->registerRuntimeField(
                    new ReferenceField(
                        'LOCATION',
                        LocationTable::getEntity(),
                        ['=this.LOCATION_ID' => 'ref.ID']
                    )
                )
                ->exec()
                ->fetch();
            return ['result' => $result['XML_ID'] ?: ''];
        };

        $result = '';
        try {
            $result = (new BitrixCache())->withId(__METHOD__ . $locationCode)
                ->resultOf($getCode)['result'];
        } catch (\Exception $e) {
            $this->log()->error(
                sprintf('failed to get location kladr code: %s: %s', \get_class($e), $e->getMessage()),
                ['location' => $locationCode]
            );
        }

        return $result;
    }

    /**
     * @param array $location
     *
     * @return string
     */
    public function getDadataJsonFromLocationArray(array $location): string
    {
        return \json_encode((new DaDataLocationAdapter())->convertLocationArrayToDadataArray($location),
            JSON_OBJECT_AS_ARRAY);
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
     * @param array  $fields
     * @param string $code
     *
     * @param array  $excludeWords
     *
     * @return array
     */
    private function stringArrayToArray(array &$fields, string $code, array $excludeWords = []): array
    {
        $list = [];
        foreach ($fields as $key => $value) {
            if (strpos($key, $code . '_') !== false) {
                if (!empty($excludeWords)) {
                    foreach ($excludeWords as $excludeWord) {
                        if (strpos($key, $excludeWord) !== false) {
                            continue(2);
                        }
                    }
                }
                $explode = explode('_', $key);
                $add = false;
                $implode = [];
                foreach ($explode as $explodeVal) {
                    if ($add) {
                        $implode[] = $explodeVal;
                    }
                    if ($explodeVal === $code) {
                        $add = true;
                    }
                }
                $list[implode('_', $implode)] = $value;
                unset($fields[$key]);
            }
        }
        return $list;
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
     * @deprecated
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
                    'TYPE' => $types[$pathItem['TYPE_ID']],
                ];
            }
            $result[] = [
                'ID'   => $item['VALUE'],
                'CODE' => $item['CODE'],
                'NAME' => $item['DISPLAY'],
                'TYPE' => $types[$item['TYPE_ID']],
                'PATH' => $path,
            ];
        }

        return $result;
    }
}
