<?php

namespace FourPaws\MobileApiBundle\Services;

class FakeCaptchaService implements CaptchaServiceInterface
{
    /**
     * @return string
     */
    public function getId(): string
    {
        return 'cool';
    }

    /**
     * @param string $id
     * @param string $code
     * @return bool
     */
    public function checkCode(string $id, string $code): bool
    {
        return true;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return 'i_have_an_apple';
    }
}
