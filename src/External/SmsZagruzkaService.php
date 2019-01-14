<?php

namespace FourPaws\External;


use FourPaws\App\Application;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\External\ZagruzkaCom\Sms;
use FourPaws\External\ZagruzkaCom\Client;
use GuzzleHttp\Psr7\Response;



class SmsZagruzkaService extends SmsService
{

    /** @var Client */
    protected $client;
    protected $parameters;
    protected const DATE_FORMAT = 'H';

    /**
     * SmsZagruzkaService constructor.
     */
    public function __construct()
    {
        $container = Application::getInstance()->getContainer();
        $this->parameters = $container->getParameter('sms');
        $this->client = new Client($this->parameters['login'], $this->parameters['password'], $this->parameters['originator']);
        $this->setLogger(LoggerFactory::create('sms'));
    }

    /**
     * @param string $text
     * @param string $number
     * @return
     */
    public function sendSmsImmediate(string $text, string $number)
    {
        return $this->sendSms($text, $number, true);
    }


    public function sendSms(string $text, string $number, bool $immediate = false)
    {
        $logContext = [
            'number' => $number,
            'immediate' => $immediate,
            'message' => $text,
        ];

        try {

            $number = $this->clearPhone($number);
            $sms = new Sms($number, $text);

            if ($immediate || $this->canSendNow($this->parameters['start_messaging'], $this->parameters['stop_messaging'])) {
                /** nothing doing here, can send immediate */
            } else {
                $queueTime = $this->buildQueueTime(
                    $this->parameters['start_messaging'],
                    $this->parameters['stop_messaging']
                );

                $sms->setFromHour($queueTime->getFrom()->format(self::DATE_FORMAT));
                $sms->setToHour($queueTime->getTo()->format(self::DATE_FORMAT));
            }

            $result = $this->client->send($sms);
            $this->logger->info(\sprintf('Sms was sent: %s.', $number), array_merge($logContext, ['service' => self::class, 'result' => [$result->getStatusCode(), $result->getBody()->getContents()]]));

        } catch (\Exception $e) {
            $this->logger->error(\sprintf('Sms send error: %s.', $e->getMessage()), $logContext);
            $result = (new Response());
        }

        return $result;
    }


}