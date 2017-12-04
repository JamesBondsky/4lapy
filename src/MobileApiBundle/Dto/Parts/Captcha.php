<?php

namespace FourPaws\MobileApiBundle\Dto\Parts;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

trait Captcha
{
    /**
     * @Assert\NotBlank()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("captcha_id")
     * @var string
     */
    protected $captchaId = '';

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
    public function getCaptchaId(): string
    {
        return $this->captchaId;
    }

    /**
     * @param string $captchaId
     * @return Captcha
     */
    public function setCaptchaId(string $captchaId): Captcha
    {
        $this->captchaId = $captchaId;
        return $this;
    }

    /**
     * @return string
     */
    public function getCaptchaValue(): string
    {
        return $this->captchaValue;
    }

    /**
     * @param string $captchaValue
     * @return Captcha
     */
    public function setCaptchaValue(string $captchaValue): Captcha
    {
        $this->captchaValue = $captchaValue;
        return $this;
    }
}
