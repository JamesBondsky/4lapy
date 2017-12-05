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
        return defined('ERROR_404') && ERROR_404 === 'Y';
    }
    
    public function isDetailNews() : bool{
        $detailPages = ['/company/news', '/services/articles'];
        $dir = $this->getRequest()->getRequestedPageDirectory();
        foreach($detailPages as $src){
            if (stripos($dir, $src) !== false){
                $newStr = str_replace($src, '', $dir);
                if(!empty($newStr)) {
                    return true;
                }
            }
        }
        return false;
    }
    
    public function getHeaderDetailArea()
    {
        ob_start();?>
            <div class="b-container b-container--news-detail">
                <div class="b-detail-page">
                    <?php global $APPLICATION;
                    $APPLICATION->IncludeComponent('bitrix:breadcrumb',
                                                         'breadcrumb',
                                                         [
                                                             'PATH'       => '',
                                                             'SITE_ID'    => SITE_ID,
                                                             'START_FROM' => '0',
                                                         ]); ?>
                    <h1 class="b-title b-title--h1">
                        <?php $APPLICATION->ShowTitle(false) ?>
                    </h1>
                    <?$APPLICATION->ShowViewContent('header_news_display_date');?>
                </div>
            </div>
        <?php
        return ob_get_clean();

    }
    
    public function hasDetailNews() : bool{
        return $this->isDetailNews();
    }
}
