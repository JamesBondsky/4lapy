<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\Controller;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Exception;
use FourPaws\App\Application;
use FourPaws\UserBundle\Service\ConfirmCodeInterface;
use RuntimeException;

/**
 * Class ConfirmCodeAgents
 *
 * @package FourPaws\ConfirmCode\Controller
 */
class ConfirmCodeAgent
{
    /**
     * @return string
     *
     * @throws RuntimeException
     */
    public static function delExpiredCodes(): string
    {
        try {
            $confirmCodeService = Application::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
            $confirmCodeService::delExpiredCodes();
        } catch (Exception $e) {
            $logger = LoggerFactory::create('ConfirmCodeAgent');
            $logger->error('Error - ' . $e->getMessage());
        }

        return '\\' . __METHOD__ . '();';
    }
}
