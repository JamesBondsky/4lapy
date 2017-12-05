<?php

namespace FourPaws\Decorators;

use Bitrix\Main\Application;

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
     * @return string
     */
    public function __toString()
    {
        return $this->getFullPublicPath();
    }
    
    /**
     * @param $path
     */
    public function setPath($path)
    {
        $this->path = $path;
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
     */
    public function getFullPublicPath() :string{
        $context = Application::getInstance()->getContext();
        $host   = $context->getServer()->getHttpHost();
        $prefix = 'http';
        if ($context->getRequest()->isHttps()) {
            $prefix .= 's';
        }
    
        return $prefix . '://' . $host . $this->path;
        
    }
}
