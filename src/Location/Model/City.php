<?php

namespace FourPaws\Location\Model;

use Bitrix\Main\Entity\DataManager;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\BitrixOrm\Model\HlbItemBase;
use FourPaws\BitrixOrm\Model\Interfaces\ActiveReadModelInterface;
use FourPaws\Location\Exception\CityNotFoundException;
use FourPaws\Location\Query\CityQuery;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class User
 *
 * @param
 *
 * @package FourPaws\BitrixOrm\Model
 */
class City extends HlbItemBase implements ActiveReadModelInterface
{
    protected $UF_LOCATION = [];

    protected $UF_DELIVERY_TEXT = '';

    protected $UF_PHONE = '';

    protected $UF_WORKING_HOURS = '';

    /**
     * @return array
     */
    public function getLocations(): array
    {
        return $this->UF_LOCATION ?: [];
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return (string)$this->UF_PHONE;
    }

    /**
     * @return string
     */
    public function getDeliveryText(): string
    {
        return (string)$this->UF_DELIVERY_TEXT;
    }

    /**
     * @return string
     */
    public function getWorkingHours(): string
    {
        return (string)$this->UF_WORKING_HOURS;
    }

    /**
     * @param array $locations
     *
     * @return City
     */
    public function withLocations(array $locations): City
    {
        $this->UF_LOCATION = $locations;

        return $this;
    }

    /**
     * @param string $phone
     *
     * @return City
     */
    public function withPhone(string $phone): City
    {
        $this->UF_PHONE = $phone;

        return $this;
    }

    public function withDeliveryText(string $deliveryText): City
    {
        $this->UF_DELIVERY_TEXT = $deliveryText;

        return $this;
    }

    /**
     * @param string $workingHours
     *
     * @return City
     */
    public function withWorkingHours(string $workingHours): City
    {
        $this->UF_WORKING_HOURS = $workingHours;

        return $this;
    }

    /**
     * @param string $id
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws CityNotFoundException
     * @throws ApplicationCreateException
     * @return City
     */
    public static function createFromPrimary(string $id): City
    {
        /** @var DataManager $dataManager */
        $dataManager = Application::getInstance()->getContainer()->get('bx.hlblock.cities');

        $city = (new CityQuery($dataManager::query()))->withFilterParameter('ID', $id)->exec()->first();

        if (!$city) {
            throw new CityNotFoundException(sprintf('City with id %s is not found.', $id));
        }

        return $city;
    }

    /**
     * @param string $locationCode
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws CityNotFoundException
     * @throws ApplicationCreateException
     * @return City
     */
    public static function createFromLocation(string $locationCode): City
    {
        /** @var DataManager $dataManager */
        $dataManager = Application::getInstance()->getContainer()->get('bx.hlblock.cities');

        $city = (new CityQuery($dataManager::query()))->withFilterParameter('UF_LOCATION', $locationCode)
                                                      ->exec()
                                                      ->first();

        if (!$city) {
            throw new CityNotFoundException(sprintf('City with location code %s is not found.', $locationCode));
        }

        return $city;
    }
}
