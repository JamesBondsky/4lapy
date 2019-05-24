<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\Decorators;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Application;
use Bitrix\Main\SystemException;

/**
 * Project specific SvgDecorator
 *
 * @package FourPaws\Decorators
 */
class FullHrefDecorator
{
    private $path;
    private $query;
    /** @var string Домен */
    private static $host = null;
    /** @var string Протокол: http|https */
    private static $proto = null;

    /**
     * FullHrefDecorator constructor.
     *
     * @param string $url
     */
    public function __construct(string $url)
    {
        if ($parsedUrl = parse_url($url)) {
            if ($parsedUrl['host']) {
                $this::$host = $parsedUrl['host'];
            }
            if ($parsedUrl['path']) {
                $this->setPath($parsedUrl['path']);
            }
            if ($parsedUrl['query']) {
                $this->setQuery($parsedUrl['query']);
            }
        }
    }
    
    /**
     * @param $path
     */
    public function setPath($path): void
    {
        $this->path = $path;
    }

    public function setQuery($query): void
    {
        $this->query = $query;
    }
    
    /**
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->getFullPublicPath();
        } catch (SystemException $e) {
            try {
                $logger = LoggerFactory::create('fullHrefDecorator');
                $logger->critical('Системная ошибка при получении публичного пути ' . $e->getTraceAsString());
            } catch (\RuntimeException $e) {
            }
            
            return '';
        }
    }
    
    /**
     * @throws SystemException
     * @return string
     */
    public function getFullPublicPath() : string
    {
        if (!$this->path) {
            return '';
        }
        $prefix = $this->getProto();
        $host = $this->getHost();

        $url = $prefix . '://' . $host . $this->path;

        if ($this->query) {
            $url .= '?' . $this->query;
        }

        return $url;
    }
    
    /**
     * @return string
     */
    public function getStartPath() : string
    {
        return $this->path;
    }

    /**
     * @return string
     * @throws SystemException
     */
    public function getProto() : string
    {
        if (static::$proto === null) {
            static::$proto  = 'http';
            $context = Application::getInstance()->getContext();
            if ($context->getRequest()->isHttps()) {
                static::$proto .= 's';
            }
        }

        return static::$proto;
    }

    /**
     * Сброс значения протокола
     */
    public function flushProto() : void
    {
        static::$proto = null;
    }

    /**
     * @param string $host
     *
     * @return FullHrefDecorator
     */
    public function setHost(string $host): FullHrefDecorator
    {
        $this::$host = $host;

        return $this;
    }

    /**
     * @return string
     * @throws SystemException
     */
    public function getHost() : string
    {
        if (static::$host === null) {
            $context = Application::getInstance()->getContext();
            static::$host = $context->getServer()->getHttpHost();
            static::$host = static::$host ? trim(static::$host) : '';

            // в cli нет HTTP_HOST, пробуем через константу
            if (static::$host === '' && defined('SITE_SERVER_NAME')) {
                static::$host = trim(SITE_SERVER_NAME);
            }
            // ... или через сайт
            if (static::$host === '') {
                $site = \CSite::GetList(
                    $by = 'SORT',
                    $order = 'ASC',
                    [
                        'ACTIVE' => 'Y',
                        //'DEFAULT' => 'Y',
                    ]
                )->Fetch();
                if ($site) {
                    static::$host = $site['SERVER_NAME'];
                }
            }
        }

        return static::$host;
    }

    /**
     * Сброс значения хоста
     */
    public function flushHost() : void
    {
        static::$host = null;
    }
}
