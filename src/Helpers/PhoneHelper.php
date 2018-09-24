<?php

namespace FourPaws\Helpers;

use CMain;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;

/**
 * Class PhoneHelper
 *
 * @package FourPaws\Helpers
 */
class PhoneHelper
{
    public const FORMAT_FULL = '+7 (%s%s%s) %s%s%s-%s%s-%s%s';
    public const FORMAT_DEFAULT = '+7 %s%s%s %s%s%s-%s%s-%s%s';
    public const FORMAT_URL = '8%s%s%s%s%s%s%s%s%s%s';
    public const FORMAT_INTERNATIONAL = '+7%s%s%s%s%s%s%s%s%s%s';
    public const FORMAT_MANZANA = '7%s%s%s%s%s%s%s%s%s%s';
    public const FORMAT_SHORT = '%s%s%s%s%s%s%s%s%s%s';

    /**
     * Проверяет телефон по правилам нормализации. Допускаются 10только десятизначные номера с ведущими 7 или 8
     *
     * @param string $phone
     *
     * @return bool
     */
    public static function isPhone(string $phone): bool
    {
        try {
            self::normalizePhone($phone);

            return true;
        } catch (WrongPhoneNumberException $e) {
            return false;
        }
    }

    /**
     * Нормализует телефонный номер.
     * Возвращает телефонный номер в формате xxxxxxxxxx (10 цифр без разделителя)
     * Кидает исключение, если $phone - не номер
     *
     * @param string $rawPhone
     *
     * @return string
     *
     * @throws WrongPhoneNumberException
     */
    public static function normalizePhone(string $rawPhone): string
    {
        $phone = preg_replace('~\D~', '', $rawPhone);
        if (\mb_strlen($phone) > 10) {
            $phone = preg_replace('~^7|^8~', '', $phone);
        }
        if (\mb_strlen($phone) === 10) {
            return $phone;
        }

        throw new WrongPhoneNumberException('Неверный номер телефона');
    }

    /**
     * @param string $rawPhone
     *
     * @return string
     * @throws WrongPhoneNumberException
     */
    public static function getManzanaPhone(string $rawPhone): string
    {
        return \vsprintf(static::FORMAT_MANZANA, \str_split(static::normalizePhone($rawPhone)));
    }

    /**
     * @param string $phone
     * @param string $format
     *
     * @return string
     */
    public static function formatPhone(string $phone, string $format = self::FORMAT_DEFAULT): string
    {
        try {
            return self::formatPhoneRaw($phone, $format);
        } catch (WrongPhoneNumberException $e) {
            return $phone;
        }
    }

    /**
     * @param string $phone
     * @param string $format
     *
     * @return string
     */
    public static function formatPhoneRaw(string $phone, string $format = self::FORMAT_DEFAULT): string
    {
        return \vsprintf($format, \str_split(self::normalizePhone($phone)));
    }

    /**
     * @global CMain $APPLICATION
     *
     * @return string
     */
    public static function getCityPhone(): string
    {
        global $APPLICATION;

        \ob_start();
        $APPLICATION->IncludeComponent(
            'fourpaws:city.phone',
            'template.text',
            [],
            false,
            ['HIDE_ICONS' => 'Y']
        );

        return \ob_get_clean();
    }
}
