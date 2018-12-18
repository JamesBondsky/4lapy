<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Request;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class CityNearestRequest implements GetRequest, SimpleUnserializeRequest
{
    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("lat")
     * @Assert\NotBlank()
     * @var string
     */
    protected $lat;
    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("lon")
     * @Assert\NotBlank()
     * @var string
     */
    protected $lon;

    /**
     * @return string
     */
    public function getLat(): string
    {
        return $this->lat;
    }

    /**
     * @param $lat
     * @return CityNearestRequest
     */
    public function setLat($lat): CityNearestRequest
    {
        $this->lat = $lat;
        return $this;
    }

    /**
     * @return string
     */
    public function getLon(): string
    {
        return $this->lon;
    }

    /**
     * @param $lon
     * @return CityNearestRequest
     */
    public function setLon($lon): CityNearestRequest
    {
        $this->lon = $lon;
        return $this;
    }
}
