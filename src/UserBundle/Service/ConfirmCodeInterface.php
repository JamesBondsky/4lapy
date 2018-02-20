<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\Service;

use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\Exception\SmsSendErrorException;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\UserBundle\Exception\ExpiredConfirmCodeException;
use FourPaws\UserBundle\Exception\NotFoundConfirmedCodeException;
use FourPaws\UserBundle\Model\ConfirmCode;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

interface ConfirmCodeInterface
{
    /**
     * @throws \Exception
     */
    public static function delExpiredCodes();

    /**
     * @param string $phone
     *
     * @return bool
     * @throws WrongPhoneNumberException
     * @throws \Exception
     */
    public static function sendConfirmSms(string $phone) : bool;
    
    /**
     * @param string $phone
     *
     * @return bool|string
     */
    public static function generateCode(string $phone);
    
    /**
     * @param $phone
     *
     * @throws \Exception
     */
    public static function setGeneratedCode($phone);
    
    /**
     * @throws \Exception
     */
    public static function delCurrentCode();

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
    public static function checkConfirmSms(string $phone, string $confirmCode) : bool;

    /**
     *
     * @throws NotFoundConfirmedCodeException
     * @throws ExpiredConfirmCodeException
     * @throws \Exception
     *
     * @return string
     */
    public static function getGeneratedCode() : string;

    /**
     * @param ConfirmCode $confirmCode
     *
     * @return bool
     */
    public static function isExpire(ConfirmCode $confirmCode) : bool;
}
