<?php

namespace FourPaws\User;

/**
 * Class UserServiceHandlers
 *
 * Обработчики событий
 *
 * @package FourPaws\User
 */
class UserServiceHandlers
{
    /**
     * @param array $fields
     *
     * @return bool
     */
    public static function checkSocserviseRegisterHandler(array $fields) : bool
    {
        /**
         * @todo может, можно как-то иначе?
         */
        global $APPLICATION;
        
        if ($fields['EXTERNAL_AUTH_ID'] === 'socservices' && !$fields['PERSONAL_PHONE']) {
            $APPLICATION->ThrowException('Phone number must be defined');
            
            return false;
        }
        
        return true;
    }
}