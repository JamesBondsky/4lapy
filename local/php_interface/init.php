<?php

use Adv\Bitrixtools\IBlockPropertyType\YesNoPropertyType;
use Bitrix\Main\EventManager;
use FourPaws\App\EventInitializer;
use FourPaws\Helpers\MailHelper;
use WebArch\BitrixNeverInclude\BitrixNeverInclude;

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/vendor/autoload.php';

BitrixNeverInclude::registerModuleAutoload();

/**
 * Логируем письма не админам вместо отправки их, если окружение отличается от прода
 *
 * @param        $to
 * @param        $subject
 * @param        $message
 * @param string $additional_headers
 * @param string $additional_parameters
 *
 * @return bool
 */
function custom_mail(
    string $to,
    string $subject,
    string $message,
    string $additional_headers = '',
    string $additional_parameters = ''
)
{
    if (MailHelper::isMailForbidden($to)) {
        return MailHelper::logBitrixMail($to, $subject, $message, $additional_headers, $additional_parameters);
    }
    
    if ($additional_parameters) {
        return @mail($to, $subject, $message, $additional_headers, $additional_parameters);
    }
    
    return @mail($to, $subject, $message, $additional_headers);
}

YesNoPropertyType::init();

/**
 * Регистрируем события
 */
(new EventInitializer())(EventManager::getInstance());
