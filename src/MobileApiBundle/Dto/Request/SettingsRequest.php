<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Object\Settings;
use FourPaws\MobileApiBundle\Dto\Request\Types\PostRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SettingsRequest
 *
 * @package FourPaws\MobileApiBundle\Dto\Request
 */
class SettingsRequest implements SimpleUnserializeRequest, PostRequest
{
    /**
     * @Assert\Valid()
     * @Assert\NotBlank()
     * @Serializer\SerializedName("settings")
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\Settings")
     * @var Settings
     */
    protected $settings;

    /**
     * @return Settings
     */
    public function getSettings(): Settings
    {
        return $this->settings;
    }
}
