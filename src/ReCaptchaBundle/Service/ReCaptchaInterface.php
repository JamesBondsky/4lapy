<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 16.07.18
 * Time: 11:46
 */

namespace FourPaws\ReCaptchaBundle\Service;

use Bitrix\Main\SystemException;

interface ReCaptchaInterface
{
    /**
     * @param string $additionalClass
     * @param bool   $isAjax
     * @param string $callback
     * @param string $id
     *
     * @return string
     */
    public function getCaptcha(string $additionalClass = '', bool $isAjax = false, string $callback = '', string $id = ''): string;

    /**
     * @return array
     */
    public function getParams(): array;

    public function addJs();

    /**
     * @return string
     */
    public function getJs(): string;

    /**
     * @param string $recaptcha
     *
     * @throws \RuntimeException
     * @throws SystemException
     * @return bool
     */
    public function checkCaptcha(?string $recaptcha = ''): bool;
}