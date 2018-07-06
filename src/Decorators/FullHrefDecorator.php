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
    /** @var string Домен */
    private static $host = null;
    /** @var string Протокол: http|https */
    private static $proto = null;

    /**
     * FullHrefDecorator constructor.
     *
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->setPath($path);
    }
    
    /**
     * @param $path
     */
    public function setPath($path): void
    {
        $this->path = $path;
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
                $logger->critical('Системная ошибка при получении пукбличного пути ' . $e->getTraceAsString());
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
        $prefix = $this->getProto();
        $host = $this->getHost();

        return $prefix . '://' . $host . $this->path;
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
