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
     * @throws ArgumentException
     * @throws SqlQueryException
     */
    public static function delExpiredCodes(): void;

    /**
     * @param string $text
     *
     * @return string
     */
    public static function generateCode(string $text): string;

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
    public static function setGeneratedCode(string $text, string $type = 'sms', int $time = 0): void;

    /**
     * @param string $type
     *
     * @throws \Exception
     */
    public static function delCurrentCode(string $type = 'sms'): void;

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
     * @param int    $time
     *
     * @return string
     */
    public static function getConfirmHash(string $text, int $time = 0): string;

    /**
     * @param $id
     * @param $code
     * @param $type
     *
     * @return bool
     * @throws ArgumentException
     * @throws \Exception
     */
    public static function writeGeneratedCode($id, $code, $type): bool;

    /**
     * @param string $code
     * @param string $type
     * @param int    $time
     *
     * @throws ArgumentException
     * @throws \Exception
     */
    public static function setCode(string $code, string $type, int $time = 0): void;

    /**
     * @param  string $type
     * @param int     $time
     *
     * @return string
     * @throws \Exception
     */
    public static function setCookie(string $type, int $time = 0): string;

    /**
     * @param string $type
     * @param bool   $upper
     *
     * @return string
     */
    public static function getPrefixByType(string $type, bool $upper = false): string;
}
