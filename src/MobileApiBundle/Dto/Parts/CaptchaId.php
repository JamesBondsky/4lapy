<?php

namespace FourPaws\MobileApiBundle\Dto\Parts;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

trait CaptchaId
{
    /**
     * @Assert\NotBlank()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("captcha_id")
     * @var string
     */
    protected $captchaId = '';

    /**
     * @return string
     */
    public function getCaptchaId(): string
    {
        return $this->captchaId;
    }

    /**
     * @param string $captchaId
     * @return $this
     */
    public function setCaptchaId(string $captchaId)
    {
        $this->captchaId = $captchaId;
        return $this;
    }
}
