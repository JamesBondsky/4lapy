<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\Service;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Db\SqlQueryException;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Type\DateTime;
use FourPaws\App\Application;
use FourPaws\BitrixOrm\Utils\MysqlBatchOperations;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\UserBundle\Exception\ExpiredConfirmCodeException;
use FourPaws\UserBundle\Exception\NotFoundConfirmedCodeException;
use FourPaws\UserBundle\Model\ConfirmCode;
use FourPaws\UserBundle\Query\ConfirmCodeQuery;
use FourPaws\UserBundle\Table\ConfirmCodeTable;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class ConfirmCodeService
 *
 * @package FourPaws\ConfirmCode
 */
class ConfirmCodeService implements ConfirmCodeInterface, ConfirmCodeSmsInterface
{
    /** смс коды храним 30 минут */
    public const SMS_LIFE_TIME = 30 * 60;
    /** Email коды храним неделю */
    public const EMAIL_LIFE_TIME = 7 * 24 * 60 * 60;

    /**
     *
     * @throws ArgumentException
     * @throws SqlQueryException
     */
    public static function delExpiredCodes(): void
    {
        $time = time();
        $query = ConfirmCodeTable::query();
        $query->where(Query::filter()->logic('or')->where([
            Query::filter()->where('DATE', '<',
                DateTime::createFromTimestamp($time - static::SMS_LIFE_TIME))
                ->where('TYPE', 'sms'),
            Query::filter()->where('DATE', '<',
                DateTime::createFromTimestamp($time - static::EMAIL_LIFE_TIME))
                ->whereLike('TYPE', 'email_%'),
        ]));
        (new MysqlBatchOperations($query))->batchDelete();
    }

    /**
     * @param string $phone
     *
     * @return bool
     * @throws ArgumentException
     * @throws \RuntimeException
     * @throws WrongPhoneNumberException
     * @throws \Exception
     */
    public static function sendConfirmSms(string $phone): bool
    {
        $phone = PhoneHelper::normalizePhone($phone);
        if (PhoneHelper::isPhone($phone)) {
            $generatedCode = static::generateCode($phone);
            static::setGeneratedCode($phone, 'sms');

            if (!empty($generatedCode)) {
                $text = 'Ваш код: ' . $generatedCode;
                try {
                    $smsService = Application::getInstance()->getContainer()->get('sms.service');
                    $smsService->sendSmsImmediate($text, $phone);

                    return true;
                } catch (\Exception $exception) {
                    $logger = LoggerFactory::create('sms');
                    $logger->error(sprintf('%s exception: %s', __FUNCTION__, $exception->getMessage()));
                }
            }
        }

        return false;
    }

    /**
     * @param string $text
     *
     * @return bool|string
     */
    public static function generateCode(string $text): ?string
    {
        return empty($text) ? false : str_pad((string)hexdec(substr(static::getConfirmHash($text), 7, 5)), 5,
            random_int(0, 9));
    }

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
    public static function setGeneratedCode(string $text, string $type = 'sms', int $time = 0): void
    {
        if (!empty($text)) {
            if ($time === 0) {
                $time = time();
            }
            if (!empty($_COOKIE[ToUpper($type) . '_ID'])) {
                static::delCurrentCode($type);
            }
            $_COOKIE[ToUpper($type) . '_ID'] = $smsId = session_id() . '_' . $time;
            if (!setcookie(ToUpper($type) . '_ID', $smsId, $time + static::SMS_LIFE_TIME, '/')) {
                throw new \RuntimeException('ошибка установки куков');
            }
            $res = ConfirmCodeTable::add(
                [
                    'ID'   => $smsId,
                    'CODE' => static::generateCode($text),
                    'TYPE' => $type,
                ]
            );
            if (!$res->isSuccess()) {
                throw new ArgumentException($res->getErrorMessages());
            }
        }
    }

    /**
     * @param string $type
     *
     * @throws \Exception
     */
    public static function delCurrentCode(string $type = 'sms'): void
    {
        if (!empty($_COOKIE[ToUpper($type) . '_ID'])) {
            setcookie(ToUpper($type) . '_ID', '', time() - 5, '/');
            ConfirmCodeTable::delete($_COOKIE[ToUpper($type) . '_ID']);
            unset($_COOKIE[ToUpper($type) . '_ID']);
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
    public static function checkConfirmSms(string $phone, string $confirmCode): bool
    {
        $phone = PhoneHelper::normalizePhone($phone);
        if (PhoneHelper::isPhone($phone)) {
            return static::checkCode($confirmCode, 'sms');
        }

        return false;
    }

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
    public static function checkCode(string $confirmCode, string $type = 'sms'): bool
    {
        $generatedCode = static::getGeneratedCode($type);
        if (!empty($generatedCode)) {
            $confirmed = $confirmCode === $generatedCode;
            if ($confirmed) {
                static::delCurrentCode($type);
            }

            return $confirmed;
        }
        return false;
    }

    /**
     *
     * @param string $type
     *
     * @return string
     * @throws ExpiredConfirmCodeException
     * @throws NotFoundConfirmedCodeException
     * @throws \Exception
     */
    public static function getGeneratedCode(string $type = 'sms'): string
    {
        $ConfirmCodeQuery = new ConfirmCodeQuery(ConfirmCodeTable::query());
        /** @var ConfirmCode $confirmCode */
        $confirmCode = $ConfirmCodeQuery->withFilter(['ID' => $_COOKIE[ToUpper($type) . '_ID']])->exec()->first();

        if (!($confirmCode instanceof ConfirmCode)) {
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
     * @param string      $type
     *
     * @return bool
     */
    public static function isExpire(ConfirmCode $confirmCode, string $type = 'sms'): bool
    {
        $constName = ToUpper($type) . '_LIFE_TIME';
        return $confirmCode->getDate()->getTimestamp() < (time() - \constant('self::' . $constName));
    }

    /**
     * @param string $text
     *
     * @param int    $time
     *
     * @return string
     */
    public static function getConfirmHash(string $text, int $time = 0): string
    {
        if ($time === 0) {
            $time = time();
        }
        return md5('confirm' . $text . $time);
    }


    /**
     * @param string $confirmCode
     *
     * @return bool
     * @throws ExpiredConfirmCodeException
     * @throws NotFoundConfirmedCodeException
     * @throws \Exception
     */
    public static function checkConfirmEmail(string $confirmCode): bool
    {
        return static::checkCode($confirmCode, 'email');
    }

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
    public static function setGeneratedHash(string $text, string $type = 'sms', int $time = 0): void
    {
        if (!empty($text)) {
            if ($time === 0) {
                $time = time();
            }
            if (!empty($_COOKIE[ToUpper($type) . '_ID'])) {
                static::delCurrentCode($type);
            }
            $_COOKIE[ToUpper($type) . '_ID'] = $smsId = session_id() . '_' . $time;
            if (!setcookie(ToUpper($type) . '_ID', $smsId, $time + static::EMAIL_LIFE_TIME, '/')) {
                throw new \RuntimeException('ошибка установки куков');
            }
            $res = ConfirmCodeTable::add(
                [
                    'ID'   => $smsId,
                    'CODE' => static::getConfirmHash($text, $time),
                    'TYPE' => $type,
                ]
            );
            if (!$res->isSuccess()) {
                throw new ArgumentException($res->getErrorMessages());
            }
        }
    }
}
