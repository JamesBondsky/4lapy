<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\Service;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Type\DateTime;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\Exception\SmsSendErrorException;
use FourPaws\External\SmsService;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\UserBundle\Exception\ExpiredConfirmCodeException;
use FourPaws\UserBundle\Exception\NotFoundConfirmedCodeException;
use FourPaws\UserBundle\Model\ConfirmCode;
use FourPaws\UserBundle\Query\ConfirmCodeQuery;
use FourPaws\UserBundle\Table\ConfirmCodeTable;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class ConfirmCodeService
 *
 * @package FourPaws\ConfirmCode
 */
class ConfirmCodeService implements ConfirmCodeInterface
{
    const LIFE_TIME = 30 * 60;
    
    /**
     * @throws \Exception
     */
    public static function delExpiredCodes()
    {
        $ConfirmCodeQuery = new ConfirmCodeQuery(ConfirmCodeTable::query());
        $ConfirmCode      = $ConfirmCodeQuery->withFilter(
            ['<DATE' => DateTime::createFromTimestamp(time() - static::LIFE_TIME)]
        )->withSelect(['ID'])->exec();
        /** @var ConfirmCode $confirmCode */
        foreach ($ConfirmCode as $confirmCode) {
            ConfirmCodeTable::delete($confirmCode->getId());
        }
    }
    
    /**
     * @param string $phone
     *
     * @return bool
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws InvalidArgumentException
     * @throws \RuntimeException
     * @throws ApplicationCreateException
     * @throws \Exception
     * @throws SmsSendErrorException
     * @throws WrongPhoneNumberException
     */
    public static function sendConfirmSms(string $phone) : bool
    {
        $phone = PhoneHelper::normalizePhone($phone);
        if (PhoneHelper::isPhone($phone)) {
            $generatedCode = static::generateCode($phone);
            static::setGeneratedCode($phone);
            
            if (!empty($generatedCode)) {
                $smsService = new SmsService();
                $text       = 'Ваш код подверждения - ' . $generatedCode;
                $smsService->sendSmsImmediate($text, $phone);
                
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * @param string $phone
     *
     * @return bool|string
     */
    public static function generateCode(string $phone)
    {
        return empty($phone) ? false : (string)hexdec(substr(md5($phone . time()), 7, 5));
    }
    
    /**
     * @param $phone
     *
     * @throws \Exception
     */
    public static function setGeneratedCode($phone)
    {
        if (!empty($phone)) {
            if (!empty($_COOKIE['SMS_ID'])) {
                static::delCurrentCode();
            }
            $_COOKIE['SMS_ID'] = $smsId = session_id() . '_' . time();
            if (!setcookie('SMS_ID', $smsId, time() + static::LIFE_TIME, '/')) {
                throw new \RuntimeException('ошибка установки куков');
            }
            $res = ConfirmCodeTable::add(
                [
                    'ID'   => $smsId,
                    'CODE' => static::generateCode($phone),
                ]
            );
            if (!$res->isSuccess()) {
                throw new ArgumentException($res->getErrorMessages());
            }
        }
    }
    
    /**
     * @throws \Exception
     */
    public static function delCurrentCode()
    {
        if (!empty($_COOKIE['SMS_ID'])) {
            setcookie('SMS_ID', '', time() - 5, '/');
            ConfirmCodeTable::delete($_COOKIE['SMS_ID']);
            unset($_COOKIE['SMS_ID']);
        }
    }
    
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
    public static function checkConfirmSms(string $phone, string $confirmCode) : bool
    {
        $phone = PhoneHelper::normalizePhone($phone);
        if (PhoneHelper::isPhone($phone)) {
            $generatedCode = static::getGeneratedCode();
            if (!empty($generatedCode)) {
                $confirmed = $confirmCode === $generatedCode;
                if ($confirmed) {
                    static::delCurrentCode();
                }
                
                return $confirmed;
            }
        }
        
        return false;
    }
    
    /**
     *
     * @throws NotFoundConfirmedCodeException
     * @throws ExpiredConfirmCodeException
     * @throws \Exception
     *
     * @return string
     */
    public static function getGeneratedCode() : string
    {
        $ConfirmCodeQuery = new ConfirmCodeQuery(ConfirmCodeTable::query());
        /** @var ConfirmCode $confirmCode */
        $confirmCode = $ConfirmCodeQuery->withFilter(['ID' => $_COOKIE['SMS_ID']])->exec()->first();
        
        if(!($confirmCode instanceof ConfirmCode)){
            throw new NotFoundConfirmedCodeException('не найден код');
        }
        if (static::isExpire($confirmCode)) {
            static::delCurrentCode();
            throw new ExpiredConfirmCodeException('истек срок действия кода');
        }
        
        return $confirmCode->getCode();
    }
    
    /**
     * @param ConfirmCode $confirmCode
     *
     * @return bool
     */
    public static function isExpire(ConfirmCode $confirmCode) : bool
    {
        return $confirmCode->getDate()->getTimestamp() < (time() - static::LIFE_TIME);
    }
}
