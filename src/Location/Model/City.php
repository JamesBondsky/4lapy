<?php

namespace FourPaws\Location\Model;

use Bitrix\Main\Entity\DataManager;
use FourPaws\App\Application;
use FourPaws\BitrixOrm\Model\HlbItemBase;
use FourPaws\BitrixOrm\Model\ModelInterface;
use FourPaws\Location\Exception\CityNotFoundException;
use FourPaws\Location\Query\CityQuery;

/**
 * Class User
 *
 * @param
 *
 * @package FourPaws\BitrixOrm\Model
 */
class City extends HlbItemBase
{
    protected $UF_NAME;

    protected $UF_LOCATION;

    protected $UF_ACTIVE;

    protected $UF_DELIVERY_TEXT;

    protected $UF_PHONE;

    protected $UF_WORKING_HOURS;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->UF_NAME;
    }

    /**
     * @return string
     */
    public function getLocations(): array
    {
        return $this->UF_LOCATION;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->UF_PHONE;
    }

    /**
     * @return string
     */
    public function getActive(): bool
    {
        return $this->UF_ACTIVE;
    }

    /**
     * @return string
     */
    public function getDeliveryText(): string
    {
        return $this->UF_DELIVERY_TEXT;
    }

    /**
     * @return string
     */
    public function getWorkingHours(): string
    {
        return $this->UF_WORKING_HOURS;
    }

    /**
     * @param string $name
     *
     * @return City
     */
    public function withName(string $name): City
    {
        $this->UF_NAME = $name;

        return $this;
    }

    /**
     * @param string $locations
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

    /**
     * @param string $active
     *
     * @return City
     */
    public function withActive(bool $active): City
    {
        $this->UF_ACTIVE = $active;

        return $this;
    }

    public function withDeliveryText(string $deliveryText): City
    {
        $this->UF_DELIVERY_TEXT = $deliveryText;

        return $this;
    }

    /**
     * @param string $active
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
     * @return \FourPaws\BitrixOrm\Model\ModelInterface
     *
     * @throws \FourPaws\User\Exceptions\NotFoundException
     */
    public static function createFromPrimary(string $id): ModelInterface
    {
        /** @var DataManager $dataManager */
        $dataManager = Application::getInstance()->getContainer()->get('bx.hlblock.cities');

        $city = (new CityQuery($dataManager::query()))->withFilterParameter('ID', $id)->exec()->first();

        if (!$city) {
            throw new CityNotFoundException(sprintf('City with id %s is not found.', $id));
        }

        return $city;
    }

    public static function createFromLocation(string $locationCode): ModelInterface
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
