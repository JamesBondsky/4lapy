<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\Service;

interface ConfirmCodeInterface
{
    /**
     * @throws \Exception
     */
    public static function delExpiredCodes();
    
    /**
     * @param string $phone
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \FourPaws\External\Exception\SmsSendErrorException
     * @throws \FourPaws\Helpers\Exception\WrongPhoneNumberException
     * @throws \Exception
     * @return bool
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
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \FourPaws\UserBundle\Exception\ExpiredConfirmCodeException
     * @throws \FourPaws\Helpers\Exception\WrongPhoneNumberException
     * @throws \Exception
     * @return bool
     */
    public static function checkConfirmSms(string $phone, string $confirmCode) : bool;
    
    /**
     *
     * @throws \FourPaws\UserBundle\Exception\ExpiredConfirmCodeException
     * @throws \Exception
     *
     * @return string
     */
    public static function getGeneratedCode() : string;
    
    /**
     * @param \FourPaws\UserBundle\Model\ConfirmCode $confirmCode
     *
     * @return bool
     */
    public static function isExpire(\FourPaws\UserBundle\Model\ConfirmCode $confirmCode) : bool;
}
