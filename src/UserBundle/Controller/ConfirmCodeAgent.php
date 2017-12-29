<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\Controller;

use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\UserBundle\Service\ConfirmCodeInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class ConfirmCodeAgents
 *
 * @package FourPaws\ConfirmCode\Controller
 */
class ConfirmCodeAgent
{
    /**
     * @throws ServiceNotFoundException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     * @return string
     */
    public static function delExpiredCodes() : string
    {
        $ConfirmCodeService = Application::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
        $ConfirmCodeService::delExpiredCodes();
        
        return '\\' . __METHOD__ . '();';
    }
}
