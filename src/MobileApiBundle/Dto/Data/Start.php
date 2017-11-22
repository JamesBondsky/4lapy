<?php

namespace FourPaws\MobileApiBundle\Dto\Data;

use JMS\Serializer\Annotation as Serializer;

/**
 * api data response /start
 * Class StartData
 * @package FourPaws\MobileApiBundle\Dto
 */
class Start
{
    /**
     * @Serializer\SerializedName("access_id")
     * @Serializer\Type("string")
     * @var string
     */
    private $accessId;

    /**
     * StartData constructor.
     * @param string $accessId
     */
    public function __construct(string $accessId)
    {
        $this->accessId = $accessId;
    }

    /**
     * @return string
     */
    public function getAccessId(): string
    {
        return $this->accessId;
    }

    /**
     * @param string $accessId
     * @return Start
     */
    public function setAccessId(string $accessId): Start
    {
        $this->accessId = $accessId;
        return $this;
    }
}
