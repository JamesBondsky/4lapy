<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\ConfirmCode\Controller;

use FourPaws\App\Application;

/**
 * Class ConfirmCodeAgents
 *
 * @package FourPaws\ConfirmCode\Controller
 */
class ConfirmCodeAgent
{
    /**
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Exception
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @return string
     */
    public static function delExpiredCodes() : string
    {
        $ConfirmCodeService = Application::getInstance()->getContainer()->get('confirm_code.service');
        $ConfirmCodeService::delExpiredCodes();
        return '\\' . __METHOD__.'();';
    }
}
