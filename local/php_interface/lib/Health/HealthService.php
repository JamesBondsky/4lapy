<?php

namespace FourPaws\Health;

use Bitrix\Main\Config\Option;
use FourPaws\Helpers\Exception\HealthException;

/**
 * Class HealthService
 *
 * @package FourPaws\Health
 */
class HealthService
{
    const STATUS_UNAVAILABLE = 0;
    
    const STATUS_AVAILABLE   = 1;
    
    const SERVICE_MANZANA    = 'manzana';
    
    const SERVICE_SMS        = 'sms';
    
    const OPTION_MODULE_ID   = 'health';
    
    public function __construct()
    {
    }
    
    /**
     * @param string $service
     * @param int    $status
     *
     * @throws \FourPaws\Helpers\Exception\HealthException
     */
    public function setStatus(string $service, int $status)
    {
        if ($status !== self::STATUS_AVAILABLE || $status !== self::STATUS_UNAVAILABLE) {
            throw new HealthException('Unknown health status');
        }
        
        switch ($service) {
            case self::SERVICE_MANZANA:
            case self::SERVICE_SMS:
                try {
                    Option::set(self::OPTION_MODULE_ID, $service, $status);
                } catch (\Exception $e) {
                    throw new HealthException(sprintf('Unknown error: %s.', $e->getMessage()));
                }
                break;
            default:
                throw new HealthException(sprintf('Unknown service %s.', $service));
        }
    }
}
