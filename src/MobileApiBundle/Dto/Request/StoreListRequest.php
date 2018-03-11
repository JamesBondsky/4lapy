<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class StoreListRequest
 * @package FourPaws\MobileApiBundle\Dto\Request
 */
class StoreListRequest implements SimpleUnserializeRequest, GetRequest
{
    /**
     * проверку ставить нельзя так как если поле не приходит должно выводиться все
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("city_id")
     */
    protected $cityId = '';

    /**
     * @var array
     * @Serializer\Type("array")
     * @Serializer\SerializedName("metro_station")
     * @Serializer\SkipWhenEmpty()
     */
    protected $metroStation = [];

    /**
     * @var float
     * @Serializer\Type("float")
     * @Serializer\SerializedName("lat")
     * @Serializer\SkipWhenEmpty()
     */
    protected $latitude = 0;

    /**
     * @var float
     * @Serializer\Type("float")
     * @Serializer\SerializedName("lon")
     * @Serializer\SkipWhenEmpty()
     */
    protected $longitude = 0;

    /**
     * @return string
     */
    public function getCityId(): string
    {
        return $this->cityId ?? '';
    }

    /**
     * @param string $cityId
     *
     * @return StoreListRequest
     */
    public function setCityId(string $cityId): StoreListRequest
    {
        $this->cityId = $cityId;
        return $this;
    }

    /**
     * @return array
     */
    public function getMetroStation(): array
    {
        return $this->metroStation ?? [];
    }

    /**
     * @param array $metroStation
     *
     * @return StoreListRequest
     */
    public function setMetroStation(array $metroStation): StoreListRequest
    {
        $this->metroStation = $metroStation;
        return $this;
    }

    /**
     * @return float
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }

    /**
     * @param float $latitude
     *
     * @return StoreListRequest
     */
    public function setLatitude(float $latitude): StoreListRequest
    {
        $this->latitude = $latitude;
        return $this;
    }

    /**
     * @return float
     */
    public function getLongitude(): float
    {
        return $this->longitude;
    }

    /**
     * @param float $longitude
     *
     * @return StoreListRequest
     */
    public function setLongitude(float $longitude): StoreListRequest
    {
        $this->longitude = $longitude;
        return $this;
    }
}
