<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Request\Types\GetRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class StoreProductAvailableRequest
 * @package FourPaws\MobileApiBundle\Dto\Request
 */
class ShopsForCheckoutRequest implements SimpleUnserializeRequest, GetRequest
{
    /**
     * @var int[]
     * @Serializer\Type("array<int>")
     * @Serializer\SerializedName("metro_station")
     */
    protected $metroStations = [];

    /**
     * @return int[]
     */
    public function getMetroStations(): array
    {
        return $this->metroStations;
    }

    /**
     * @param int[] $metroStations
     * @return ShopsForCheckoutRequest
     */
    public function setMetroStations(array $metroStations): ShopsForCheckoutRequest
    {
        $this->metroStations = $metroStations;
        return $this;
    }
}
