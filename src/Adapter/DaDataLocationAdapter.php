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
     * @param $entity
     *
     * @return mixed
     */
    public function convert($entity)
    {
        /** @var DadataLocation $entity */
        $bitrixLocation = new BitrixLocation();

        /** @var LocationService $locationService */
        try {
            $locationService = Application::getInstance()->getContainer()->get('location.service');
            $country = !empty($entity->getCountry()) ? $entity->getCountry().' ' : '';
            $region = !empty($entity->getRegion()) ? $entity->getRegion().' ' : '';
            $city = !empty($entity->getCity()) ? : '';
            $cities = $locationService->findLocationCity($country.$region.$city, '', 1, true);
            $city = reset($cities);
            $bitrixLocation = $this->convertDataToEntity($city, BitrixLocation::class);

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
     * @return mixed
     */
    public function convertFromArray(array $data)
    {
        /** @var DadataLocation $entity */
        $entity = $this->convertDataToEntity($data, DadataLocation::class);
        return $this->convert($entity);
    }
}
