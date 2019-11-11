<?php

use Bitrix\Main\Loader;
use Articul\Landing\Orm\LectionsTable;

/**
 * Class FlagmanLection
 */
class FlagmanLection extends \CBitrixComponent
{
    /**
     * @return mixed|void
     */
    public function executeComponent()
    {
        Loader::includeModule('iblock');
        
        try {
            $iblockId                = $this->getIblockId();
            $this->arResult['ITEMS'] = $this->getItems($iblockId);
            $this->setPathforImage();
            
            $this->checkAvailabitity();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        
        $this->includeComponentTemplate();
    }
    
    private function getIblockId()
    {
        return \CIBlock::GetList([], ['=CODE' => 'flagman_lections'])->Fetch()['ID'];
    }
    
    private function getItems($iblockId)
    {
        return LectionsTable::query()
            ->setSelect(['ID', 'NAME', 'PREVIEW_PICTURE', 'DATE' => 'UTS.EVENT_DATE', 'TIME' => 'UTS.EVENT_TIME', 'FREE_SITS' => 'UTS.FREE_SITS', 'SITS' => 'UTS.SITS'])
            ->setFilter(['=IBLOCK_ID' => $iblockId])
            ->exec()
            ->fetchAll();
    }
    
    private function setPathforImage()
    {
        foreach ($this->arResult['ITEMS'] as &$item) {
            $item['PREVIEW_PICTURE'] = \CFile::GetPath($item['PREVIEW_PICTURE']);
        }
    }
    
    private function checkAvailabitity()
    {
        foreach ($this->arResult['ITEMS'] as &$item) {
            $item['AVAILABLE'] = 'N';
            
            if ($item['FREE_SITS'] > 0) {
                $item['AVAILABLE'] = 'Y';
            }
        }
    }
}
