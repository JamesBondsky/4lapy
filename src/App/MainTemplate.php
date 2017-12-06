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
        $src = '/company/news';
        
        return $this->isPartitionDir($src);
    }
    
    public function isPartitionDir(string $src) : bool
    {
        $dir = $this->getDir();
        if (preg_match(sprintf('~^%s/\w+~', $src), $dir)) {
            $newStr = str_replace($src, '', $dir);
            if ($newStr) {
                return true;
            }
        }
        
        return false;
    }
    
    public function getDir() : string
    {
        return $this->getRequest()->getRequestedPageDirectory();
    }
    
    public function isDetailArticles()
    {
        $src = '/services/articles';
        
        return $this->isPartitionDir($src);
    }
}
