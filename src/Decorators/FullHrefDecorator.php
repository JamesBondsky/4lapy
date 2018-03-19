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
        $context = Application::getInstance()->getContext();
        $host    = $context->getServer()->getHttpHost();
        $prefix  = 'http';
        if ($context->getRequest()->isHttps()) {
            $prefix .= 's';
        }
        
        return $prefix . '://' . $host . $this->path;
    }
    
    /**
     * @return string
     */
    public function getStartPath() : string
    {
        return $this->path;
    }
}
