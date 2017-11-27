<?php

namespace FourPaws\MobileApiBundle\Services;

use CCaptcha;

class BitrixCaptchaService implements CaptchaServiceInterface
{
    /**
     * @var \CCaptcha
     */
    protected $captchaProvider;

    public function __construct()
    {
        /**
         * К сожалению только таким образом можно напрямую получить объект CCaptcha
         */
        include_once $_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/classes/general/captcha.php';

        $this->captchaProvider = new CCaptcha();
        $this->captchaProvider->SetCode();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->captchaProvider->GetSID();
    }

    /**
     * @param string $id
     * @param string $code
     * @return bool
     */
    public function checkCode(string $id, string $code): bool
    {
        return $this->captchaProvider->CheckCaptchaCode($code, $id);
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->captchaProvider->code;
    }
}
