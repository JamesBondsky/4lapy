<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\Service;

use Bitrix\Main\ArgumentException;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\UserBundle\Exception\ExpiredConfirmCodeException;
use FourPaws\UserBundle\Exception\NotFoundConfirmedCodeException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

interface ConfirmCodeSmsInterface
{
    /**
     * @param string $phone
     *
     * @return bool
     * @throws ArgumentException
     * @throws \RuntimeException
     * @throws WrongPhoneNumberException
     * @throws \Exception
     */
    public static function sendConfirmSms(string $phone): bool;

    /**
     * @param string $phone
     * @param string $confirmCode
     *
     * @throws NotFoundConfirmedCodeException
     * @throws ServiceNotFoundException
     * @throws ExpiredConfirmCodeException
     * @throws WrongPhoneNumberException
     * @throws \Exception
     * @return bool
     */
    public static function checkConfirmSms(string $phone, string $confirmCode): bool;
}
