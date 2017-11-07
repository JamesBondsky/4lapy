<?php

namespace FourPaws\Health;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Application;
use FourPaws\Helpers\Exception\HealthException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Class HealthService
 *
 * @package FourPaws\Health
 */
class HealthService implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    
    const STATUS_UNAVAILABLE = false;
    
    const STATUS_AVAILABLE   = true;
    
    const SERVICE_MANZANA    = 'manzana';
    
    const SERVICE_SMS        = 'sms';
    
    const OPTION_MODULE_ID   = 'health';
    
    const STATUS_LOG_NAME    = 'health';
    
    public function __construct()
    {
        $this->setLogger(LoggerFactory::create(self::STATUS_LOG_NAME));
    }
    
    /**
     * @param string $service
     * @param bool   $status
     *
     * @throws \FourPaws\Helpers\Exception\HealthException
     */
    public function setStatus(string $service, bool $status)
    {
        try {
            switch ($service) {
                case self::SERVICE_MANZANA:
                case self::SERVICE_SMS:
                    try {
                        $this->saveStatus($service, $status);
                    } catch (\Exception $e) {
                        throw new HealthException(sprintf('Unknown error: %s.', $e->getMessage()));
                    }
                    break;
                default:
                    throw new HealthException(sprintf('Unknown service %s.', $service));
            }
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Health error: %s', $e->getMessage()));
        }
    }
    
    /**
     * @param string $service
     * @param bool   $status
     *
     * @throws \Bitrix\Main\DB\SqlQueryException
     */
    protected function saveStatus(string $service, bool $status)
    {
        if (!$this->checkStatus($service, $status)) {
            $connection = Application::getConnection();
            
            $sql = sprintf("UPDATE b_option SET VALUE = '%s' WHERE MODULE_ID = '%s' AND NAME = '%s' AND SITE_ID = '%s'",
                           (int)$status,
                           self::OPTION_MODULE_ID,
                           $service,
                           SITE_ID);

            $connection->query($sql);
            
            $logMessage = sprintf('Сервис %s %s', $service, $status ? 'упал' : 'поднялся');
            $this->logger->critical($logMessage);
        }
    }
    
    /**
     * @param string $service
     * @param bool   $status
     *
     * @return bool
     *
     * @throws \Bitrix\Main\DB\SqlQueryException
     */
    protected function checkStatus(string $service, bool $status) : bool
    {
        $connection = Application::getConnection();
        
        $sql = sprintf("SELECT VALUE FROM b_option WHERE MODULE_ID = '%s' AND NAME = '%s' AND SITE_ID = '%s'",
                       self::OPTION_MODULE_ID,
                       $service,
                       SITE_ID);
        
        $option = $connection->query($sql)->fetch();
        
        if (null === $option) {
            $sql = sprintf("INSERT INTO b_option(SITE_ID, MODULE_ID, NAME) VALUES ('%s','%s','%s')",
                           SITE_ID,
                           self::OPTION_MODULE_ID,
                           $service);
            $connection->query($sql);
        }
        
        $value = (int)($option['VALUE'] ?? -1);
        
        return $value === (int)$status;
    }
}
