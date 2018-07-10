<?php

namespace FourPaws\Adapter;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\Adapter\Model\Input\DadataLocation;
use FourPaws\Adapter\Model\Output\BitrixLocation;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\LocationBundle\Exception\CityNotFoundException;
use FourPaws\LocationBundle\LocationService;

/**
 * Class DaDataLocationAdapter
 *
 * @package FourPaws\Helpers
 */
class DaDataLocationAdapter extends BaseAdapter
{
    public const TYPE_MAP = [
        'COUNTRY'          => false,
        'COUNTRY_DISTRICT' => false,
        'REGION'           => 'region',
        'SUBREGION'        => false,
        'CITY'             => 'city',
        'VILLAGE'          => 'settlement',
        'STREET'           => false,
    ];

    public const EXCLUDE_REGION_TYPE = [
        'REGION'  => [
            'край',
            'область',
            'автономная область',
            'автономный округ',
            'республика',
        ],
        'VILLAGE' => [
            'село',
            'посёлок',
            'посёлок городского типа',
            'хутор',
            'аул',
            'деревня',
        ],
    ];

    /**
     * @var LocationService
     */
    private $locationService;

    /**
     * DaDataLocationAdapter constructor.
     */
    public function __construct()
    {
        $this->locationService = Application::getInstance()->getContainer()->get('location.service');

        parent::__construct();
    }

    /**
     * @param DadataLocation $entity
     *
     * @return BitrixLocation
     */
    public function convert($entity): BitrixLocation
    {
        /** @var DadataLocation $entity */
        $bitrixLocation = new BitrixLocation();

        try {
            $cities = [];
            $fullCities = $this->getFullCities($entity);
            $region = $this->getRegion($entity);
            if (!empty($entity->getKladrId())) {
                $city = $this->getCityName($entity);

                try {
                    $cities = $this->locationService->findLocationByExtService(LocationService::KLADR_SERVICE_CODE,
                        $entity->getKladrId());
                } catch (\Exception $e) {
                    $cities = [];
                    $logger = LoggerFactory::create('dadataAdapter');
                    $logger->error($e->getMessage(), $e);
                }
                /** двухфакторный фикс - первый отбирает частичное соответствие населенного пунка, второй полное соответствие если результатов больше 1 */
                if (\count($cities) > 1) {
                    foreach ($cities as $key => $cityItem) {
                        if (strpos($cityItem['NAME'], $city) === false) {
                            unset($cities[$key]);
                        }
                    }
                    /** если после частичного отбора все равно много местоположений - отбираем по полному совпадению */
                    if (\count($cities) > 1) {
                        $fullCities = array_unique($fullCities);
                        foreach ($cities as $key => $cityItem) {
                            if (!\in_array($cityItem['NAME'], $fullCities, true)) {
                                unset($cities[$key]);
                            }
                        }
                    }
                }
            }

            if (empty($cities)) {
                /** пока доставка в одной стране - убираем поиск по стране */
                $city = $this->getCityName($entity);

                $fullRegion = $this->getFullRegion($entity);
                /** гребаный фикс - циклим поиск по нескольким местоположениям */
                foreach ($fullCities as $fullCity) {
                    $cities = $this->locationService->findLocationCity(trim($fullCity), trim($fullRegion), 1, true,
                        true);
                    if (!empty($cities)) {
                        break;
                    }
                }
            }
            if (!empty($cities)) {
                $selectedCity = reset($cities);
            } else {
                $selectedCity['NAME'] = $city;
            }

            /** установка ид региона дополнительно из запроса, при необходимости именно здесь устанавливать доп. данные */
            if (!empty($selectedCity['PATH'])) {
                foreach ($selectedCity['PATH'] as $pathItem) {
                    if (ToUpper($pathItem['TYPE']['CODE']) === 'REGION') {
                        $selectedCity['REGION_ID'] = $pathItem['ID'];
                        $selectedCity['REGION_CODE'] = $pathItem['CODE'];
                        break;
                    }
                }
            }

            $selectedCity['REGION'] = $region;
            $bitrixLocation = $this->convertDataToEntity($selectedCity, BitrixLocation::class);

        } catch (CityNotFoundException $e) {
            /** не нашли - возвращаем пустой объект - должно быть сведено к 0*/
            $logger = LoggerFactory::create('dadataAdapter');
            $logger->error('не найдено');
        } catch (ApplicationCreateException $e) {}

        return $bitrixLocation;
    }

    /**
     * @param array $data
     *
     * @return BitrixLocation
     */
    public function convertFromArray(array $data): BitrixLocation
    {
        /** @var DadataLocation $entity */
        $entity = $this->convertDataToEntity($data, DadataLocation::class);

        return $this->convert($entity);
    }

    /**
     * @param array $location
     *
     * @return array
     */
    public function convertLocationArrayToDadataArray(array $location): array
    {
        $result = \array_filter($this->expandLocation($location), function ($item) {
            return $this::TYPE_MAP[$item['TYPE']['CODE']];
        });

        return \array_reduce($result, function ($array, $item) {
            if (self::EXCLUDE_REGION_TYPE[$item['TYPE']['CODE']]) {
                $item['NAME'] = \trim(\str_replace(self::EXCLUDE_REGION_TYPE[$item['TYPE']['CODE']], '',
                    \strtolower($item['NAME'])));
            }

            return \array_merge($array, [
                $this::TYPE_MAP[$item['TYPE']['CODE']] => $item['NAME'],
            ]);
        }, []);
    }

    /**
     * @param array $location
     *
     * @return array
     */
    protected function expandLocation(array $location): array
    {
        $path = $location['PATH'];
        unset($location['PATH']);

        return \array_merge([$location], $path);
    }

    /**
     * @param DadataLocation $entity
     *
     * @return string
     */
    private function getFullCity(DadataLocation $entity): string
    {
        $fullCity = $city = !empty($entity->getCity()) ? $entity->getCity() : '';
        $cityType = $entity->getCityTypeFull();
        $isCity = ToLower($cityType) === 'город';
        if (!empty($entity->getSettlement()) && $entity->getSettlementType() !== 'мкр') {
            $city = $entity->getSettlement();
            $type = $entity->getSettlementTypeFull();
            if ($entity->getSettlementType() === 'рп') {
                $type = 'рабочий посёлок';
            }
            if ($entity->getSettlementType() === 'дп') {
                $type = 'дачный посёлок';
            }
            $fullCity = $city . ' ' . $type;
        } else {
            if (!$isCity) {
                if ($entity->getCityType() === 'рп') {
                    $cityType = 'рабочий посёлок';
                }
                if ($entity->getCityType() === 'дп') {
                    $cityType = 'дачный посёлок';
                }
                $fullCity = $city . ' ' . $cityType;
            }
        }
        return $fullCity;
    }

    /**
     * @param DadataLocation $entity
     *
     * @return string
     */
    private function getFullRegion(DadataLocation $entity): string
    {
        $fullRegion = '';
        $region = $this->getRegion($entity);
        if (!empty($region)) {
            $continue = true;
            /** если регион это город федерального значения то не юзаем префикс */
            if (\in_array(ToLower($region), ['москва', 'санкт-петербург', 'севастополь'])) {
                $continue = false;
            }
            if ($continue) {
                $regionType = trim($entity->getRegionTypeFull());
                $regionExcluded = ['Кабардино-Балкарская', 'Удмуртская', 'Чеченская', 'Чувашская'];
                if ($entity->getRegionType() === 'Респ' && !\in_array($region, $regionExcluded, true)) {
                    $fullRegion = $regionType . ' ' . $region;
                } else {
                    $fullRegion = $region . ' ' . $regionType;
                }
            }
        }

        return $fullRegion;
    }

    /**
     * @param $entity
     *
     * @return string
     */
    private function getRegion(DadataLocation $entity): string
    {
        return trim(!empty($entity->getRegion()) && $entity->getCity() !== $entity->getRegion() ? sprintf(str_replace('/',
            '%s',
            $entity->getRegion()), '(', ')') : '');
    }

    /**
     * @param DadataLocation $entity
     *
     * @return array
     */
    private function getFullCities(DadataLocation $entity): array
    {
        $fullCities = [];
        $fullCities[] = $fullCity = $this->getFullCity($entity);
        /** ищем поселок с буквой ё так же и наоборот */
        if (strpos($fullCity, 'поселок')) {
            $fullCities[] = trim(str_replace('поселок', 'посёлок', $fullCity));
        }
        if (strpos($fullCity, 'посёлок')) {
            $fullCities[] = trim(str_replace('посёлок', 'поселок', $fullCity));
        }
        if (strpos($fullCity, 'рабочий')) {
            $val = trim(str_replace(' рабочий', '', $fullCity));
            $fullCities[] = $val;
            /** ищем поселок с буквой ё так же */
            if (strpos($val, 'поселок')) {
                $fullCities[] = trim(str_replace('поселок', 'посёлок', $val));
            }
            if (strpos($val, 'посёлок')) {
                $fullCities[] = trim(str_replace('посёлок', 'поселок', $val));
            }
        }
        if (strpos($fullCity, 'дачный')) {
            $val = trim(str_replace(' дачный', '', $fullCity));
            $fullCities[] = $val;
            /** ищем поселок с буквой ё так же */
            if (strpos($val, 'поселок')) {
                $fullCities[] = trim(str_replace('поселок', 'посёлок', $val));
            }
            if (strpos($val, 'посёлок')) {
                $fullCities[] = trim(str_replace('посёлок', 'поселок', $val));
            }
        }
        return array_unique($fullCities);
    }

    /**
     * @param DadataLocation $entity
     *
     * @return string
     */
    private function getCityName(DadataLocation $entity): string
    {
        $city = !empty($entity->getSettlement()) && $entity->getSettlementType() !== 'мкр' ? $entity->getSettlement() : '';
        if (empty($city)) {
            $city = !empty($entity->getCity()) ? $entity->getCity() : '';
        }
        return $city;
    }
}
