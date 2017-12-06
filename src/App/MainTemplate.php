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
    
    public function hasHeaderDetailPageContainer() : bool
    {
        return $this->isDetailNews() || $this->isDetailArticles();
    }
    
    public function isDetailNews() : bool
    {
        return $this->isPartitionDir('/company/news');
    }
    
    public function isDetailArticles()
    {
        return $this->isPartitionDir('/services/articles');
    }
}
