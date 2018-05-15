<?php

namespace FourPaws\External\SmsTraffic;

use Exception;
use FourPaws\External\SmsTraffic\Exception\ParsingException;
use FourPaws\External\SmsTraffic\Exception\SendingException;
use FourPaws\External\SmsTraffic\Exception\TransportException;
use FourPaws\External\SmsTraffic\Sms\AbstractSms;
use FourPaws\External\SmsTraffic\Transport\GuzzleHttpTransport;
use FourPaws\External\SmsTraffic\Transport\TransportInterface;

/**
 * Class Client
 *
 * @package FourPaws\External\SmsTraffic
 */
class Client
{
    /**
     * API Url by default
     */
    const DEFAULT_API_URL = 'http://sds.smstraffic.ru/smartdelivery-in/multi.php';

    /**
     * Reserved API Url
     */
    const RESERVE_API_URL = 'http://91.238.120.150/smartdelivery-in/multi.php';

    /**
     * Sms Traffic Login
     *
     * @var string
     */
    protected $login;

    /**
     * Sms Traffic Password
     *
     * @var string
     */
    protected $password;

    /**
     * Route
     *
     * По умолчанию - сначала пытаемся отправить в вайбер, если в течение 90с сообщение не пришло, то отправляем в смс
     *
     * @var string
     */
    protected $route = 'viber(90)-sms';

    /**
     * Message sender
     *
     * @var string
     */
    protected $originator = '4lapy';

    /**
     * Sms Traffic URL
     *
     * @var string
     */
    protected $apiUrl = self::DEFAULT_API_URL;

    /**
     * @var TransportInterface
     */
    protected $transport;

    /**
     * @var callable
     */
    protected $preRequestCallback;

    /**
     * @var callable
     */
    protected $postRequestCallback;

    /**
     * Client constructor.
     *
     * @param string $login Sms Traffic login
     * @param string $password Sms Traffic Password
     * @param string|null $originator Sms sender
     */
    public function __construct($login, $password, $originator = null)
    {
        $this->login = $login;
        $this->password = $password;
        $this->originator = $originator;
    }

    /**
     * Sends message
     *
     * @param AbstractSms $sms
     *
     * @return array
     *
     * @throws ParsingException
     * @throws SendingException
     * @throws TransportException
     */
    public function send(AbstractSms $sms): array
    {
        if (!$this->transport instanceof TransportInterface) {
            $this->transport = new GuzzleHttpTransport();
        }

        $params = $sms->getParameters();
        $params[$sms::PARAMETER_LOGIN] = $this->login;
        $params[$sms::PARAMETER_PASSWORD] = $this->password;
        $params[$sms::PARAMETER_ORIGINATOR] = $this->originator;
        $params[$sms::PARAMETER_ROUTE] = $this->route;

        try {
            if (\is_callable($this->preRequestCallback)) {
                \call_user_func($this->preRequestCallback,
                    $params,
                    $this->apiUrl);
            }

            $result = $this->transport->doRequest($this->apiUrl, $params);
        } catch (Exception $e) {
            throw new TransportException($e->getMessage(), $e->getCode(), $e);
        }

        $ret = $this->parseSendingResult($result);

        if (\is_callable($this->postRequestCallback)) {
            \call_user_func($this->postRequestCallback,
                $ret,
                $params,
                $this->apiUrl
            );
        }

        return $ret;
    }

    /**
     * @param TransportInterface $transport
     *
     * @return $this
     */
    public function setTransport(TransportInterface $transport)
    {
        $this->transport = $transport;

        return $this;
    }

    /**
     * @param string $route
     *
     * @return $this
     */
    public function setRoute(string $route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * @param string $apiUrl
     *
     * @return $this
     */
    public function setApiUrl($apiUrl)
    {
        $this->apiUrl = $apiUrl;

        return $this;
    }

    /**
     * Set callback function perform before API http request
     *
     * @param callable $preRequestCallback
     *
     * @return $this
     */
    public function setPreRequestCallback(callable $preRequestCallback)
    {
        $this->preRequestCallback = $preRequestCallback;

        return $this;
    }

    /**
     * Set callback function perform after API http request
     *
     * @param callable $postRequestCallback
     *
     * @return $this
     */
    public function setPostRequestCallback(callable $postRequestCallback)
    {
        $this->postRequestCallback = $postRequestCallback;

        return $this;
    }

    /**
     * Parses sending result
     *
     * @param string $result
     *
     * @return array
     * @throws ParsingException
     * @throws SendingException
     */
    protected function parseSendingResult($result): array
    {
        $xml = \simplexml_load_string($result);
        $ret = \json_decode(\json_encode($xml), true);
        $requiredFields = [
            'result',
            'code',
        ];
        foreach ($requiredFields as $field) {
            if (!isset($ret[$field])) {
                throw new ParsingException("Incorrect answer. Key '" . $field . "' does not exist.");
            }
        }
        if ($ret['code'] !== '0') {
            $message = empty($ret['description']) ? 'Unknown Error' : $ret['description'];
            throw (new SendingException($message, (int)$ret['code']))->setAnswer($result);
        }

        return $ret;
    }

    /**
     * @param string $login
     */
    public function setLogin(string $login): void
    {
        $this->login = $login;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }
}
