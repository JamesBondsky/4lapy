<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Request\Types\PostRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class ReportRequest implements SimpleUnserializeRequest, PostRequest
{
    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("summary")
     * @Assert\NotBlank()
     * @var string
     */
    protected $summary;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("device_info")
     *
     * @var string
     */
    protected $deviceInfo;

    /**
     * @return string|null
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @param string $summary
     * @return ReportRequest
     */
    public function setSummary(string $summary): ReportRequest
    {
        $this->summary = $summary;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDeviceInfo()
    {
        return $this->deviceInfo;
    }

    /**
     * @param string $deviceInfo
     * @return ReportRequest
     */
    public function setDeviceInfo(string $deviceInfo): ReportRequest
    {
        $this->deviceInfo = $deviceInfo;
        return $this;
    }

}
