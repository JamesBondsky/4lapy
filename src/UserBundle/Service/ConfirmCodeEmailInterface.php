<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\Service;

use Bitrix\Main\ArgumentException;
use FourPaws\UserBundle\Exception\ExpiredConfirmCodeException;
use FourPaws\UserBundle\Exception\NotFoundConfirmedCodeException;

interface ConfirmCodeEmailInterface
{
    /**
     * @param string $confirmCode
     *
     * @return bool
     * @throws ExpiredConfirmCodeException
     * @throws NotFoundConfirmedCodeException
     * @throws \Exception
     */
    public static function checkConfirmEmail(string $confirmCode): bool;

    /**
     * @param string $text
     * @param string $type
     *
     * @param int    $time
     *
     * @throws \RuntimeException
     * @throws ArgumentException
     * @throws \Exception
     */
    public static function setGeneratedHash(string $text, string $type = 'sms', int $time = 0): void;
}
