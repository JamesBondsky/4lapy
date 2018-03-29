<?php

namespace FourPaws\Adapter;

use Bitrix\Sale\Location\Admin\LocationHelper;
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
     * @param $data
     *
     * @return mixed
     */
    public function convert($data)
    {
        /** @var DadataLocation $entity */
        $entity = $this->convertDataToEntity($data, DadataLocation::class);
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
        } catch (ApplicationCreateException $e) {
        }
        return $bitrixLocation;
    }
}
