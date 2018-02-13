<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\External;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\Exception\SmsSendErrorException;
use FourPaws\External\SmsTraffic\Client;
use FourPaws\External\SmsTraffic\Exception\SmsTrafficApiException;
use FourPaws\External\SmsTraffic\Sms\IndividualSms;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class SmsService
 *
 * @package FourPaws\External
 */
class SmsService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var Client
     */
    protected $client;

    protected $startMessaging;

    protected $stopMessaging;

    /**
     * SmsService constructor.
     *
     * @throws ApplicationCreateException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws InvalidArgumentException
     * @throws \RuntimeException
     */
    public function __construct()
    {
        $container = Application::getInstance()->getContainer();

        list(
            $this->startMessaging, $this->stopMessaging, $login, $password, $originator
            ) = \array_values($container->getParameter('sms'));

        $this->client = new Client($login, $password, $originator);
        $this->setLogger(LoggerFactory::create('sms'));
    }

    /**
     * @param string $text
     * @param string $number
     *
     */
    public function sendSmsImmediate(string $text, string $number)
    {
        $this->sendSms($text, $number, true);
    }

    /**
     * @param string $text
     * @param string $number
     * @param bool $immediate
     */
    public function sendSms(string $text, string $number, bool $immediate = false)
    {
        try {
            $sms = new IndividualSms(
                [
                    [
                        $this->clearPhone($number),
                        $text,
                    ],
                ]
            );

            if (!$immediate) {
                $sms->updateParameters(
                    [
                        'start_date' => $this->buildQueueTime($this->startMessaging),
                        'stop_date' => $this->buildQueueTime($this->stopMessaging),
                        'isSendNextDay' => '1',
                        'isAbonentLocaleTime' => '1',
                    ]
                );
            }

            try {
                $this->client->send($sms);
            } catch (SmsTrafficApiException $e) {
                throw new SmsSendErrorException($e->getMessage(), $e->getCode(), $e);
            }
        } catch (SmsSendErrorException $e) {
            $this->logger->error(sprintf('Sms send error: %s.', $e->getMessage()));
        }
    }

    /**
     * @param string $phone
     *
     * @throws SmsSendErrorException
     * @return string
     *
     */
    protected function clearPhone(string $phone): string
    {
        try {
            $formatedPhone = PhoneHelper::normalizePhone($phone);
            $phone = '7' . $formatedPhone;

            if (\mb_strlen($phone) === 11) {
                return $phone;
            }
        } catch (WrongPhoneNumberException $e) {
        }

        throw new SmsSendErrorException(sprintf('Неверный формат номера телефона (%s)', $phone));
    }

    /**
     * @param string $time
     *
     * @return string
     */
    protected function buildQueueTime(string $time): string
    {
        return (new \DateTime($time))->format('Y-m-d H:i:s');
    }
}
