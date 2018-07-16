<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\ReCaptchaBundle\Service;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Application;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Uri;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerAwareInterface;

class ReCaptchaService implements LoggerAwareInterface, ReCaptchaInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var ClientInterface
     */
    protected $guzzle;

    private $parameters;

    /** @noinspection SpellCheckingInspection */

    /**
     * ReCaptchaService constructor.
     *
     * @param ClientInterface $client
     *
     * @param array           $parameters
     *
     * @throws \RuntimeException
     */
    public function __construct(ClientInterface $client, array $parameters)
    {
        $this->guzzle = $client;
        $this->parameters = $parameters;
    }

    /**
     * @param string $additionalClass
     *
     * @param bool   $isAjax
     *
     * @return string
     */
    public function getCaptcha(string $additionalClass = '', bool $isAjax = false): string
    {
        if (!$isAjax) {
            $script = '';
            $this->addJs();
        } else {
            $script = $this->getJs();
        }

        return $script . '<div class="g-recaptcha' . $additionalClass . '" data-sitekey="' . $this->parameters['key']
            . '"></div>';
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return ['sitekey' => $this->parameters['key']];
    }

    public function addJs()
    {
        Asset::getInstance()->addJs('https://www.google.com/recaptcha/api.js?hl=ru');
    }

    /**
     * @return string
     */
    public function getJs(): string
    {
        return '<script data-skip-moving=true async src="https://www.google.com/recaptcha/api.js?hl=ru"></script>';
    }

    /**
     * @param string $recaptcha
     *
     * @throws \RuntimeException
     * @throws SystemException
     * @return bool
     */
    public function checkCaptcha(string $recaptcha = ''): bool
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $context = Application::getInstance()->getContext();
        if (empty($recaptcha)) {
            $recaptcha = (string)$context->getRequest()->get('g-recaptcha-response');
        }
        $uri = new Uri($this->parameters['serviceUri']);
        $uri->addParams(
            [
                'secret'   => $this->parameters['secretKey'],
                'response' => $recaptcha,
                'remoteip' => $context->getServer()->get('REMOTE_ADDR'),
            ]
        );
        if (!empty($recaptcha)) {
            try {
                $res = $this->guzzle->request('get', $uri->getUri());
            } catch (GuzzleException $e) {
                return false;
            }
            if ($res->getStatusCode() === 200) {
                $data = json_decode($res->getBody()->getContents());
                if ($data && $data->success) {
                    return true;
                }
            }
        }

        return false;
    }
}
