<?php

namespace FourPaws\MobileApiBundle\Services;

interface CaptchaServiceInterface
{
    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @param string $id
     * @param string $code
     * @return bool
     */
    public function checkCode(string $id, string $code): bool;

    /**
     * @return string
     */
    public function getCode(): string;
}
