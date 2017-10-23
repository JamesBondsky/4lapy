<?php

namespace FourPaws\External;

use FourPaws\External\Exception\SmsSendErrorException;
use FourPaws\External\SmsTraffic\Client;
use FourPaws\External\SmsTraffic\Exception\SmsTrafficApiException;
use FourPaws\External\SmsTraffic\Sms\IndividualSms;

class SmsService
{
    protected $client;
    
    /**
     * SmsService constructor.
     */
    public function __construct()
    {
        /**
         * @todo move into parameters
         */
        $this->client = new Client('', '', '4lapy');
    }
    
    /**
     * @param string $text
     * @param string $number
     * @param int    $userTime
     */
    public function sendSms(string $text, string $number, int $userTime = 0)
    {
        $userTime = $userTime ?: time();
        
        try {
            if ($this->isSendSmsAvailabilityPeriod($userTime)) {
                $this->sendSmsImmediate($text, $number);
            } else {
                $this->addSmsIntoQueue($text, $number);
            }
        } catch (SmsSendErrorException $e) {
            /**
             * @todo log and add into queue
             */
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
     * @param int $userTime
     *
     * @return bool
     */
    public function isSendSmsAvailabilityPeriod(int $userTime) : bool
    {
        /**
         * @todo implement this
         */
        
        return true;
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
