<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class CompatibilityRequest implements SimpleUnserializeRequest, GetRequest
{
    /**
     * Версия билда
     * @Assert\NotBlank()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("v")
     *
     * @var string
     */
    protected $buildVersion;

    /**
     * Тип операционной системы
     * @Assert\NotBlank()
     * @Assert\Choice({"ios", "android"})
     * @Serializer\Type("string")
     * @Serializer\SerializedName("os")
     *
     * @var 'ios'|'android'
     */
    protected $osType;

    /**
     * @return string|null
     */
    public function getBuildVersion()
    {
        return $this->buildVersion;
    }

    /**
     * @param string $buildVersion
     * @return CompatibilityRequest
     */
    public function setBuildVersion($buildVersion): CompatibilityRequest
    {
        $this->buildVersion = $buildVersion;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOsType()
    {
        return $this->osType;
    }

    /**
     * @param string $osType
     * @return CompatibilityRequest
     */
    public function setOsType($osType): CompatibilityRequest
    {
        $this->osType = $osType;
        return $this;
    }

}
