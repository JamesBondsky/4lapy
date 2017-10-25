<?php

namespace FourPaws\External;

use FourPaws\App\Application;
use FourPaws\External\Exception\SmsSendErrorException;
use FourPaws\External\SmsTraffic\Client;
use FourPaws\External\SmsTraffic\Exception\SmsTrafficApiException;
use FourPaws\External\SmsTraffic\Sms\IndividualSms;
use FourPaws\Helpers\Exception\HealthException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Class SmsService
 *
 * @package FourPaws\External
 */
class SmsService implements LoggerAwareInterface

{
    use LoggerAwareTrait;
    
    /**
     * @var \FourPaws\External\SmsTraffic\Client
     */
    protected $_client;
    
    /**
     * @var \FourPaws\Health\HealthService
     */
    protected $_healthService;
    
    /**
     * SmsService constructor.
     *
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \RuntimeException
     */
    public function __construct()
    {
        /**
         * @todo move into parameters
         */
        $this->_healthService = Application::getInstance()->getContainer()->get('health.service');
        $this->_client        = new Client('', '', '4lapy');
    }
    
    /**
     * @param string $text
     * @param string $number
     */
    public function sendSms(string $text, string $number)
    {
        try {
            if ($this->isSendSmsAvailabilityPeriod()) {
                $this->sendSmsImmediate($text, $number);
            } else {
                $this->addSmsIntoQueue($text, $number);
            }
            
            try {
                $this->_healthService->setStatus($this->_healthService::SERVICE_SMS,
                                                 $this->_healthService::STATUS_AVAILABLE);
            } catch (HealthException $e) {
            }
        } catch (SmsSendErrorException $e) {
            try {
                $this->_healthService->setStatus($this->_healthService::SERVICE_SMS,
                                                 $this->_healthService::STATUS_UNAVAILABLE);
            } catch (HealthException $e) {
            }
            
            $this->logger->error(sprintf('Sms send error: %s.', $e->getMessage()));
            $this->addSmsIntoQueue($text, $number);
        }
    }
    
    /**
     * @param string $text
     * @param string $number
     */
    public function addSmsIntoQueue(string $text, string $number)
    {
        /**
         * @todo implement this
         */
    }
    
    /**
     * @return bool
     */
    public function isSendSmsAvailabilityPeriod() : bool
    {
        $time = date('G');
        
        return 9 < $time && $time < 21;
    }
    
    /**
     * @param string $text
     * @param string $number
     *
     * @throws \FourPaws\External\Exception\SmsSendErrorException
     */
    public function sendSmsImmediate(string $text, string $number)
    {
        try {
            $this->_client->send(new IndividualSms([
                                                       $number,
                                                       $text,
                                                   ]));
        } catch (SmsTrafficApiException $e) {
            throw new SmsSendErrorException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
