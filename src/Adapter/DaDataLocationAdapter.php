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
    /**
     * @param DadataLocation $entity
     *
     * @return BitrixLocation
     */
    public function convert($entity): BitrixLocation
    {
        /** @var DadataLocation $entity */
        $bitrixLocation = new BitrixLocation();

        /** @var LocationService $locationService */
        try {
            $locationService = Application::getInstance()->getContainer()->get('location.service');
            /** пока доставка в одной стране - убираем поиск по стране */
            $fullCity = $city = !empty($entity->getCity()) ? $entity->getCity() : '';
            if (empty($city)) {
                $city = $entity->getSettlement();
                $type = $entity->getSettlementTypeFull();
                if ($entity->getSettlementType() === 'рп') {
                    $type = 'рабочий посёлок';
                }
                $fullCity = $city . ' ' . $type;
            }
            $fullRegion = '';
            $region = trim(!empty($entity->getRegion()) && $city !== $entity->getRegion() ? str_replace('/', '',
                    $entity->getRegion()) : '');
            if (!empty($region)) {
                $regionType = trim($entity->getRegionTypeFull());
                $regionExcluded = ['Кабардино-Балкарская', 'Удмуртская', 'Чеченская', 'Чувашская'];
                if ($entity->getRegionType() === 'Респ' && !\in_array($region, $regionExcluded, true)) {
                    $fullRegion = $regionType . ' ' . $region;
                } else {
                    $fullRegion = $region . ' ' . $regionType;
                }
            }
            $cities = $locationService->findLocationCity(trim($fullCity), trim($fullRegion), 1, true, true);
            $selectedCity = reset($cities);

            /** установка ид региона дополнительно из запроса, при необходимости именно здесь устанавливать доп. данные */
            foreach ($selectedCity['PATH'] as $pathItem) {
                if (ToUpper($pathItem['TYPE']['CODE']) === 'REGION') {
                    $selectedCity['REGION_ID'] = $pathItem['ID'];
                    $selectedCity['REGION_CODE'] = $pathItem['CODE'];
                    break;
                }
            }

            $selectedCity['REGION'] = $entity->getRegion();
            $bitrixLocation = $this->convertDataToEntity($selectedCity, BitrixLocation::class);

        } catch (CityNotFoundException $e) {
            /** не нашли - возвращаем пустой объект - должно быть сведено к 0*/
            $logger = LoggerFactory::create('dadataAdapter');
            $logger->error('не найдено');
        } catch (ApplicationCreateException $e) {
            $logger = LoggerFactory::create('system');
            $logger->error('системная ошибка загрузки сервисов');
        }
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
}
