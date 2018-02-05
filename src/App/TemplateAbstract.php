<?php

namespace FourPaws\App;

use Bitrix\Main\Context;
use Bitrix\Main\Context\Culture;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Response;
use Bitrix\Main\Server;
use Bitrix\Main\Web\Uri;

/**
 * Class TemplateAbstract
 *
 * Класс для управления условиями в шаблонах.
 *
 * Определяется три типа методов:
 *
 * - is... : определяет атомарное условие или группу условий (например, isIndex())
 * - has... : композиция условий типа is..., определяет наличие блока в шаблоне. Не должен содержать никакой логики,
 *            помимо вызова методов is и условных операторов
 * - get... : получение чего-либо, используемого в шаблоне.
 *
 * @package FourPaws\App
 */
abstract class TemplateAbstract
{
    protected static $instance;
    
    private          $context;
    
    private          $path;
    
    private          $dir;
    
    /**
     * @param Context $context
     *
     * @return TemplateAbstract
     */
    public static function getInstance(Context $context) : self
    {
        if (!static::$instance) {
            static::$instance = new static($context);
        }
        
        return static::$instance;
    }
    
    /**
     * TemplateAbstract constructor.
     *
     * @param Context $context
     */
    protected function __construct(Context $context)
    {
        $this->context = $context;
        
        $uri        = $this->getUri();
        $this->path = $uri->getPath();
        $this->dir  = $context->getRequest()->getRequestedPageDirectory();
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
        return $this->path === $page;
    }
    
    /**
     * @return string
     */
    public function getPath() : string
    {
        return $this->path;
    }
    
    /**
     * @return bool
     */
    public function isAjaxRequest() : bool
    {
        $server = $this->getServer();
        
        return $server->get('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest' || $server->get('HTTP_BX_AJAX') === 'true';
    }
    
    /**
     * @return HttpRequest
     */
    public function getRequest() : HttpRequest
    {
        return $this->context->getRequest();
    }
    
    /**
     * @return Uri
     */
    public function getUri() : Uri
    {
        return new Uri($this->getRequest()->getRequestUri());
    }
    
    /**
     * @return Server
     */
    public function getServer() : Server
    {
        return $this->context->getServer();
    }
    
    /**
     * @return Culture
     */
    public function getCulture() : Culture
    {
        return $this->context->getCulture();
    }
    
    /**
     * @return Response
     */
    public function getResponse() : Response
    {
        return $this->context->getResponse();
    }
    
    public function getDir() : string
    {
        return $this->dir;
    }
    
    public function isPartitionDir(string $src) : bool
    {
        return preg_match(sprintf('~^%s/[-/@\w]+~', $src), $this->getDir()) > 0;
    }
    
    public function isDir($dir) : bool
    {
        return $this->dir === $dir;
    }
}
