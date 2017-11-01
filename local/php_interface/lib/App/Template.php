<?php

namespace FourPaws\App;

use Bitrix\Main\Context;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Server;
use Bitrix\Main\Web\Uri;

/**
 * Class Template
 *
 * @package FourPaws\App
 */
class Template
{
    protected static $instance;
    
    private   $context;
    
    /**
     * @param \Bitrix\Main\Context $context
     *
     * @return \FourPaws\App\Template
     */
    public static function getInstance(Context $context) : self
    {
        if (!static::$instance) {
            static::$instance = new static($context);
        }
        
        return static::$instance;
    }
    
    protected function __construct(Context $context)
    {
        $this->context = $context;
    }
    
    public function isIndex() : bool
    {
        return $this->isPage('/');
    }
    
    /**
     * Страница 404
     *
     * @return bool
     */
    public function is404() : bool
    {
        return defined('ERROR_404') && ERROR_404 === 'Y';
    }
    
    /**
     * Находимся на странице $page
     *
     * @param string $page
     *
     * @return bool
     */
    public function isPage(string $page) : bool
    {
    
    }
    
    public function isAjaxRequest() {
    
    }
    
    /**
     * @return HttpRequest
     */
    public function getRequest() : HttpRequest {
        return $this->context->getRequest();
    }
    
    /**
     * @return Uri
     */
    public function getUri() : Uri {
        return new Uri($this->getRequest()->getRequestUri());
    }
    
    /**
     * @return Server
     */
    public function getServer() : Server {
        return $this->context->getServer();
    }
}
