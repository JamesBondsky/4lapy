<?php

namespace FourPaws\Adapter;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\Adapter\Model\Input\DadataLocation;
use FourPaws\Adapter\Model\Output\BitrixLocation;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\LocationBundle\Exception\CityNotFoundException;
use FourPaws\LocationBundle\LocationService;
use FourPaws\StoreBundle\Service\StoreService;

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
            $country = !empty($entity->getCountry()) ? $entity->getCountry() : '';
            $fullCity = $city = !empty($entity->getCity()) ? $entity->getCity() : '';
            if (empty($city)) {
                $city = $entity->getSettlement();
                $type = $entity->getSettlementTypeFull();
                if ($entity->getSettlementType() === 'рп') {
                    $type = 'рабочий посёлок';
                }
                $fullCity = $entity->getSettlement() . ' ' . $type;
            }
            $region = !empty($entity->getRegion()) && $city !== $entity->getRegion() ? ' ' . str_replace('/', '',
                    $entity->getRegion()) : '';
            /** из-за того что битрикс не сортирует по релевантности получаем количество адресов больше чем надо
             * и щем в нем нужный нам адрес - количество для поиска надо подобрать, на 100 работает
             */
            $cities = $locationService->findLocationCity($city, $country . $region, 100, true);
            $countCities = \count($cities);
            if ($countCities > 1) {
                foreach ($cities as $bitrixCity) {
                    if ($bitrixCity['NAME'] === $fullCity) {
                        $selectedCity = $bitrixCity;
                        break;
                    }
                }
            }
            if (!isset($selectedCity)) {
                $selectedCity = reset($cities);
            }

            /** установка ид региона дополнительно из запроса, при необходимости именно здесь устанавливать доп. данные */
            $selectedCity['REGION_ID'] = LocationService::getRegion($selectedCity['CODE']);

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
