<?php

namespace FourPaws\Helpers;

use FourPaws\Helpers\Exception\WrongPhoneNumberException;

class PhoneHelper
{
    const FORMAT_DEFAULT = '+7 %s%s%s %s%s%s-%s%s-%s%s';

    const FORMAT_URL = '8%s%s%s%s%s%s%s%s%s%s';

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
        $phone = preg_replace('~(^(\D)*7|^8)|\D~', '', $rawPhone);

        if (mb_strlen($phone) === 10) {
            return $phone;
        }

        throw new WrongPhoneNumberException('Неверный номер телефона');
    }

    /**
     * Проверяет телефон по правилам нормализации. Допускаются 10только десятизначные номера с ведущими 7 или 8
     *
     * @param string $phone
     *
     * @return bool
     */
    public static function isPhone(string $phone)
    {
        try {
            self::normalizePhone($phone);

            return true;
        } catch (WrongPhoneNumberException $e) {
            return false;
        }
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
            $normalized = self::normalizePhone($phone);

            return vsprintf($format, str_split($normalized));
        } catch (WrongPhoneNumberException $e) {
            return $phone;
        }
    }
}
