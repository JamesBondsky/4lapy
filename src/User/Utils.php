<?php

namespace FourPaws\User;

use FourPaws\User\Exceptions\WrongPhoneNumberException;

class Utils
{
    /**
     * Нормализует телефонный номер.
     * Возвращает телефонный номер в формате xxxxxxxxxx (10 цифр без разделителя)
     * Кидает исключение, если $phone - не номер
     *
     * @param string $rawPhone
     *
     * @return string
     *
     * @throws \FourPaws\User\Exceptions\WrongPhoneNumberException
     */
    public static function normalizePhone(string $rawPhone) : string
    {
        $phone = preg_replace('~(^(\D)*7|8)|\D~', '', $rawPhone);
        
        if (strlen($phone) === 10) {
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
     * Возвращает фильтр для получения логина по сырому логину
     *
     * @param string $rawLogin
     *
     * @return array
     */
    public static function getLoginFilterByRaw(string $rawLogin) : array
    {
        $filter = [
            'ACTIVE' => 'Y',
            [
                'LOGIC' => 'OR',
                [
                    '=LOGIN' => $rawLogin,
                ],
                [
                    '=EMAIL' => $rawLogin,
                ],
                [
                    '=PERSONAL_PHONE' => $rawLogin,
                ],
            ],
        ];
        
        if ($email = filter_var($rawLogin, FILTER_SANITIZE_EMAIL)) {
            $filter[0][] = ['=EMAIL' => $email];
        }
        
        try {
            $filter[0][] = ['=PERSONAL_PHONE' => self::normalizePhone($rawLogin)];
        } catch (WrongPhoneNumberException $e) {
        }
        
        return $filter;
    }
}
