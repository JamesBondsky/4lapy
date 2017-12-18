<?php

namespace FourPaws\App;

/**
 * Class MainTemplate
 *
 * Класс для основного шаблона
 *
 * @package FourPaws\App
 */
class MainTemplate extends TemplateAbstract
{
    /**
     * @return bool
     */
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
        return \defined('ERROR_404') && ERROR_404 === 'Y';
    }
    
    /**
     * @return bool
     */
    public function hasHeaderPublicationListContainer() : bool
    {
        return $this->isListNews() || $this->isListArticles();
    }
    
    /**
     * @return bool
     */
    public function hasHeaderDetailPageContainer() : bool
    {
        return $this->isDetailNews() || $this->isDetailArticles();
    }
    
    /**
     * @return bool
     */
    public function isDetailNews() : bool
    {
        return $this->isPartitionDir('/company/news');
    }
    
    /**
     * @return bool
     */
    public function isDetailArticles()
    {
        return $this->isPartitionDir('/services/articles');
    }
    
    /**
     * @return bool
     */
    public function hasHeaderPersonalContainer() : bool
    {
        return ($this->isPersonalDirectory() || $this->isPersonal()) && !$this->isRegister() && !$this->isForgotPassword();
    }
    
    /**
     * @return bool
     */
    public function isPersonal() : bool
    {
        return $this->isDir('/personal');
    }
    
    /**
     * @return bool
     */
    public function isPersonalDirectory() : bool
    {
        return $this->isPartitionDir('/personal');
    }
    
    /**
     * @return bool
     */
    public function isRegister() : bool
    {
        return $this->isDir('/personal/register');
    }
    
    /**
     * @return bool
     */
    public function isForgotPassword() : bool
    {
        return $this->isDir('/personal/forgot-password');
    }
    
    public function isListNews()
    {
        return $this->isDir('/company/news');
    }
    
    public function isListArticles()
    {
        return $this->isDir('/services/articles');
    }
}
