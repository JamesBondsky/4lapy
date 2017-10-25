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
    protected $client;
    
    /**
     * @var \FourPaws\Health\HealthService
     */
    protected $healthService;
    
    /**
     * SmsService constructor.
     *
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \RuntimeException
     */
    public function __construct()
    {
        $container = Application::getInstance()->getContainer();
        
        list($login, $password, $originator) = $container->getParameter('sms');
        
        $this->healthService = $container->get('health.service');
        $this->client        = new Client($login, $password, $originator);
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
                $this->healthService->setStatus($this->healthService::SERVICE_SMS,
                                                $this->healthService::STATUS_AVAILABLE);
            } catch (HealthException $e) {
            }
        } catch (SmsSendErrorException $e) {
            try {
                $this->healthService->setStatus($this->healthService::SERVICE_SMS,
                                                $this->healthService::STATUS_UNAVAILABLE);
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
            $this->client->send(new IndividualSms([
                                                       $number,
                                                       $text,
                                                   ]));
        } catch (SmsTrafficApiException $e) {
            throw new SmsSendErrorException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
