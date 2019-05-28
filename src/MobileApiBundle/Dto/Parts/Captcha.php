<?php

namespace FourPaws\MobileApiBundle\Dto\Parts;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

trait Captcha
{
    use CaptchaId;

    /**
     * @Assert\NotBlank()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("captcha_value")
     * @var string
     */
    protected $captchaValue = '';

    /**
     * @return string
     */
    public function getCaptchaValue(): string
    {
        return $this->captchaValue;
    }

    /**
     * @param string $captchaValue
     * @return $this
     */
    public function setCaptchaValue(string $captchaValue)
    {
        $this->captchaValue = $captchaValue;
        return $this;
    }
}
