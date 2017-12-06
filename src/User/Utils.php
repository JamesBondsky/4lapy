<?php

namespace FourPaws\User;

use FourPaws\Helpers\PhoneHelper;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;

class Utils
{

    
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
            $filter[0][] = ['=PERSONAL_PHONE' => PhoneHelper::normalizePhone($rawLogin)];
        } catch (WrongPhoneNumberException $e) {
        }
        
        return $filter;
    }
}
