<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\Controller;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlQueryException;
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
     * @return string
     */
    public static function delExpiredCodes(): string
    {
        try {
            $ConfirmCodeService = Application::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
            $ConfirmCodeService::delExpiredCodes();
        } catch (ArgumentException|SqlQueryException $e) {
            $logger = LoggerFactory::create('sql');
            $logger->error('sql error - ' . $e->getMessage());
        } catch (ApplicationCreateException|ServiceCircularReferenceException|ServiceNotFoundException|\Exception $e) {
            $logger = LoggerFactory::create('system');
            $logger->error('system error - ' . $e->getMessage());
        }

        return '\\' . __METHOD__ . '();';
    }
}
