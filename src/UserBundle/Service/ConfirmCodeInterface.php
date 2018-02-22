<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\Service;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlQueryException;
use FourPaws\UserBundle\Exception\ExpiredConfirmCodeException;
use FourPaws\UserBundle\Exception\NotFoundConfirmedCodeException;
use FourPaws\UserBundle\Model\ConfirmCode;

interface ConfirmCodeInterface
{
    /**
     *
     * @throws ArgumentException
     * @throws SqlQueryException
     */
    public static function delExpiredCodes();

    /**
     * @param string $text
     *
     * @return bool|string
     */
    public static function generateCode(string $text);

    /**
     * @param string $text
     * @param string $type
     *
     * @throws \Exception
     */
    public static function setGeneratedCode(string $text, string $type = 'sms');

    /**
     * @param string $type
     *
     * @throws \Exception
     */
    public static function delCurrentCode(string $type = 'sms');

    /**
     * @param string $confirmCode
     *
     * @param string $type
     *
     * @return bool
     * @throws ExpiredConfirmCodeException
     * @throws NotFoundConfirmedCodeException
     * @throws \Exception
     */
    public static function checkCode(string $confirmCode, string $type = 'sms'): bool;

    /**
     *
     * @param string $type
     *
     * @return string
     * @throws ExpiredConfirmCodeException
     * @throws NotFoundConfirmedCodeException
     * @throws \Exception
     */
    public static function getGeneratedCode(string $type = 'sms'): string;

    /**
     * @param ConfirmCode $confirmCode
     *
     * @param string      $type
     *
     * @return bool
     */
    public static function isExpire(ConfirmCode $confirmCode, string $type = 'sms'): bool;

    /**
     * @param string $text
     *
     * @return string
     */
    public static function getConfirmHash(string $text): string;
}
