<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\Controller;

use FourPaws\App\Application;
use FourPaws\UserBundle\Service\ConfirmCodeInterface;

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
        $ConfirmCodeService = Application::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
        $ConfirmCodeService::delExpiredCodes();
        
        return '\\' . __METHOD__ . '();';
    }
}
