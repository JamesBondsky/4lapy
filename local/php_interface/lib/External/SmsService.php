<?php

namespace FourPaws\External;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
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
    
    protected $startMessaging;
    
    protected $stopMessaging;
    
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
        
        list($this->startMessaging, $this->stopMessaging, $login, $password, $originator) =
            array_values($container->getParameter('sms'));
        
        $this->healthService = $container->get('health.service');
        
        $this->client = new Client($login, $password, $originator);
        $this->setLogger(LoggerFactory::create('sms'));
    }
    
    /**
     * @param string $text
     * @param string $number
     * @param bool   $immediate
     */
    public function sendSms(string $text, string $number, bool $immediate = false)
    {
        try {
            $sms = new IndividualSms([
                                         [
                                             $this->clearPhone($number),
                                             $text,
                                         ],
                                     ]);
            
            if (!$immediate) {
                $sms->updateParameters([
                                           'start_date'          => $this->buildQueueTime($this->startMessaging),
                                           'stop_date'           => $this->buildQueueTime($this->stopMessaging),
                                           'isSendNextDay'       => '1',
                                           'isAbonentLocaleTime' => '1',
                                       ]);
            }
            
            try {
                $this->client->send($sms);
            } catch (SmsTrafficApiException $e) {
                throw new SmsSendErrorException($e->getMessage(), $e->getCode(), $e);
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
        }
    }
    
    /**
     * @param string $text
     * @param string $number
     *
     * @throws SmsSendErrorException
     */
    public function sendSmsImmediate(string $text, string $number)
    {
        $this->sendSms($text, $number, true);
    }
    
    /**
     * @param string $time
     *
     * @return string
     */
    protected function buildQueueTime(string $time) : string
    {
        return (new \DateTime($time))->format('Y-m-d H:i:s');
    }
    
    /**
     * @param string $phone
     *
     * @return string
     *
     * @throws SmsSendErrorException
     */
    protected function clearPhone(string $phone) : string
    {
        $phone = '7' . preg_replace('~(^(\D)*7|8)|\D~', '', $phone);
        
        if (strlen($phone) === 11) {
            return $phone;
        }
        
        throw new SmsSendErrorException(sprintf('Неверный формат номера телефона (%s)', $phone));
    }
}
