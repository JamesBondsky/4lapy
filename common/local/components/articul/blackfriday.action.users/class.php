<?php

use Articul\BlackFriday\Orm\BFActionUsersTable;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Loader;
use \Bitrix\Iblock\Model\Section;

/**
 * Class BlackFridayActionUsers
 */
class BlackFridayActionUsers extends \CBitrixComponent
{
    /**
     * @var int $iblockId
     */
    private $iblockId;
    
    /**
     * @var string $iblockCode
     */
    private $iblockCode = 'black_friday_action_user';
    
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
        
        if ($this->StartResultCache($this->arParams['CACHE_TIME'])) {
            $this->arResult['ITEMS'] = $this->getItems();
            
            if (count($this->arResult['ITEMS']) <= 0) {
                $this->abortResultCache();
            }
            
            $this->includeComponentTemplate();
        }
    }
    
    /**
     * @return array
     */
    private function getItems()
    {
        $items = BFActionUsersTable::query()
            ->setSelect(['ID', 'NAME', 'PREVIEW_PICTURE', 'LINK' => 'UTS.LINK'])
            ->setFilter(['=IBLOCK_ID' => $this->iblockId, '=ACTIVE' => 'Y'])
            ->setCacheTtl($this->arParams['CACHE_TIME'])
            ->exec()
            ->fetchAll();
        
        foreach ($items as &$item) {
            $item['PREVIEW_PICTURE'] = \CFile::GetPath($item['PREVIEW_PICTURE']);
        }
        
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
