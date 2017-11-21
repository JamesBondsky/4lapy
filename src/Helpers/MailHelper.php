<?php

namespace FourPaws\Helpers;

use Adv\Bitrixtools\Tools\EnvType;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\UserGroupTable;

class MailHelper
{
    /**
     * @param string $email
     *
     * @return bool
     */
    public static function isMailForbidden(string $email) : bool
    {
        return !(EnvType::isProd() || self::isUserAdminByEmail($email));
    }
    
    /**
     * @param string $email
     *
     * @return bool
     */
    public static function isUserAdminByEmail(string $email) : bool
    {
        try {
            $count = UserGroupTable::getList([
                                                 'filter' => [
                                                     'USER.EMAIL' => $email,
                                                     'GROUP_ID'   => 1,
                                                 ],
                                                 'select' => [
                                                     'GROUP_ID',
                                                     'USER_ID',
                                                 ],
                                             ])->getSelectedRowsCount();
        } catch (ArgumentException $e) {
            return false;
        }
        
        return $count > 0;
    }
    
    /**
     * @param array ...$arguments
     *
     * @return bool
     *
     * @throws \RuntimeException
     */
    public static function logBitrixMail(...$arguments) : bool
    {
        LoggerFactory::create('mail', 'mail')->info(implode(' | ', $arguments));
        
        return true;
    }
}
