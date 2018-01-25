<?php

namespace FourPaws\MobileApiBundle\Dto\Response;

use JMS\Serializer\Annotation as Serializer;

/**
 * api data response /start
 * Class StartData
 * @package FourPaws\MobileApiBundle\Dto
 */
class StartResponse
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
     * @return StartResponse
     */
    public function setAccessId(string $accessId): StartResponse
    {
        $this->accessId = $accessId;
        return $this;
    }
}
