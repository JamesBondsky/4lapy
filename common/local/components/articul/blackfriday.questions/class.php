<?php

use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Loader;
use Bitrix\Iblock\ElementTable;

/**
 * Class BlackFridayQuestions
 */
class BlackFridayQuestions extends \CBitrixComponent
{
    /**
     * @var int $iblockId
     */
    private $iblockId;
    
    /**
     * @var string $iblockCode
     */
    private $iblockCode = 'black_friday_questions';
    
    /**
     * @param array $params
     *
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
        $this->iblockId = $this->getIblockId();
        
        $params['CACHE_TIME'] = $params['CACHE_TIME'] ?? 360000;
        $params['CACHE_TYPE'] = $params['CACHE_TYPE'] ?? 'Y';
        
        return parent::onPrepareComponentParams($params);
    }
    
    /**
     * @return mixed|void
     * @throws \Bitrix\Main\LoaderException
     */
    public function executeComponent()
    {
        if (!Loader::includeModule('articul.blackfriday')) {
            echo 'Модуль Лэндинга Черной Пятницы не установлен';
            return;
        }
        
       //if ($this->StartResultCache($this->arParams['CACHE_TIME'])) {
            $this->arResult['ITEMS'] = $this->getItems();
    
           echo '<pre>';
           print_r($this->arResult['ITEMS']);
           echo '</pre>';
           die;
            // if (count($this->arResult['ITEMS']) <= 0) {
            //     $this->abortResultCache();
            // }
            //
            // $this->includeComponentTemplate();
      // }
    }
    
    /**
     * @return array
     */
    private function getItems()
    {
        $items = \Bitrix\Iblock\ElementTable::query()
            ->setSelect(['ID', 'NAME', 'PREVIEW_TEXT'])
            ->setFilter(['=IBLOCK_ID' => $this->iblockId, '=ACTIVE' => 'Y'])
            ->setCacheTtl($this->arParams['CACHE_TIME'])
            ->exec()
            ->fetchAll();
        
        return $items;
    }
    
    /**
     * @return mixed
     */
    private function getIblockId()
    {
        return \CIBlock::GetList([], ['CODE' => $this->iblockCode])->Fetch()['ID'];
    }
}
