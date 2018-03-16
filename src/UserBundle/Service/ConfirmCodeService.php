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
class ConfirmCodeService implements ConfirmCodeInterface, ConfirmCodeSmsInterface, ConfirmCodeEmailInterface
{
    /** смс коды храним 30 минут */
    public const SMS_LIFE_TIME = 30 * 60;
    /** Email коды храним неделю */
    public const EMAIL_LIFE_TIME = 7 * 24 * 60 * 60;
    /** все остальное храним час */
    public const ANOTHER_LIFE_TIME = 60 * 60;

    /**
     * @throws ArgumentException
     * @throws SqlQueryException
     */
    public static function delExpiredCodes(): void
    {
        $time = time();
        $query = ConfirmCodeTable::query();
        $query->setSelect(['ID']);
        $query->where(Query::filter()->logic('or')->where([
            [
                Query::filter()->where('DATE', '<',
                    DateTime::createFromTimestamp($time - static::SMS_LIFE_TIME))
                    ->where('TYPE', 'sms'),
            ],
            [
                Query::filter()->where('DATE', '<',
                    DateTime::createFromTimestamp($time - static::EMAIL_LIFE_TIME))
                    ->whereLike('TYPE', 'email_%'),
            ],
            [
                Query::filter()->where('DATE', '<',
                    DateTime::createFromTimestamp($time - static::ANOTHER_LIFE_TIME))
                    ->whereNotLike('TYPE', 'email_%')
                    ->whereNot('TYPE', 'sms'),
            ],
        ]));
        (new MysqlBatchOperations($query))->batchDelete();
    }

    /**
     * @param string $phone
     *
     * @return bool
     * @throws NotFoundConfirmedCodeException
     * @throws ExpiredConfirmCodeException
     * @throws ArgumentException
     * @throws \RuntimeException
     * @throws WrongPhoneNumberException
     * @throws \Exception
     */
    public static function sendConfirmSms(string $phone): bool
    {
        $phone = PhoneHelper::normalizePhone($phone);
        if (PhoneHelper::isPhone($phone)) {
            static::setGeneratedCode($phone, 'sms');
            $generatedCode = static::getGeneratedCode('sms');

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
     * @return string
     */
    public static function generateCode(string $text): string
    {
        return empty($text) ? '' : str_pad((string)hexdec(substr(static::getConfirmHash($text), 7, 5)), 5,
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
            static::setCode(static::generateCode($text), $type, $time);
        }
    }

    /**
     * @param string $code
     * @param string $type
     * @param int    $time
     *
     * @throws ArgumentException
     * @throws \Exception
     */
    public static function setCode(string $code, string $type, int $time = 0): void
    {
        if (!empty($code)) {
            if ($time === 0) {
                $time = time();
            }

            $id = static::setCookie($type, $time);
            static::prepareData($id, $code, $type);

            static::writeGeneratedCode($id, $code, $type);
        }
    }

    /**
     * @param string $type
     *
     * @throws \Exception
     */
    public static function delCurrentCode(string $type = 'sms'): void
    {
        $codeType = ToUpper($type) . '_ID';
        if (!empty($_COOKIE[$codeType])) {
            setcookie($codeType, '', time() - 5, '/');
            ConfirmCodeTable::delete($_COOKIE[$codeType]);
            unset($_COOKIE[$codeType]);
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
        return substr(md5('confirm' . $text . $time), 0, 255);
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
            static::setCode(static::getConfirmHash($text, $time), $type, $time);
        }
    }

    /**
     * @param $id
     * @param $code
     * @param $type
     */
    public static function prepareData(&$id, &$code, &$type): void
    {
        $id = substr($id, 0, 255);
        $code = substr($code, 0, 255);
        $type = substr($type, 0, 50);
    }

    /**
     * @param $id
     * @param $code
     * @param $type
     *
     * @return bool
     * @throws ArgumentException
     * @throws \Exception
     */
    public static function writeGeneratedCode($id, $code, $type): bool
    {
        $res = ConfirmCodeTable::add(
            [
                'ID'   => substr($id, 0, 255),
                'CODE' => substr($code, 0, 255),
                'TYPE' => substr($type, 0, 50),
            ]
        );
        if (!$res->isSuccess()) {
            throw new ArgumentException(implode(', ', $res->getErrorMessages()));
        }
        return true;
    }

    /**
     * @param  string $type
     * @param int     $time
     *
     * @return string
     * @throws \Exception
     */
    public static function setCookie(string $type, int $time = 0): string
    {
        if ($time === 0) {
            $time = time();
        }
        if (!empty($_COOKIE[ToUpper($type) . '_ID'])) {
            static::delCurrentCode($type);
        }
        $lifeTime = \constant(static::getPrefixByType($type, true) . '_LIFE_TIME');
        $cookieCode = ToUpper($type) . '_ID';
        $_COOKIE[$cookieCode] = $id = session_id() . '_' . $time;
        if (!setcookie($cookieCode, $id, $time + $lifeTime, '/')) {
            throw new \RuntimeException('ошибка установки куков');
        }
        return $id;
    }

    /**
     * @param string $type
     * @param bool   $upper
     *
     * @return string
     */
    public static function getPrefixByType(string $type, bool $upper = false): string
    {
        if ($type === 'sms') {
            $return = 'sms';
        } else {
            if (strpos($type, 'email_') !== false) {
                $return = 'email';
            } else {
                $return = 'another';
            }
        }
        if ($upper) {
            $return = ToUpper($return);
        }
        return $return;
    }
}
