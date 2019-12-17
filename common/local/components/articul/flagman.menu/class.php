<?php

use Bitrix\Main\Loader;
use Bitrix\Iblock\SectionTable;
use FourPaws\Enum\IblockType;
use FourPaws\Enum\IblockCode;

/**
 * Class FlagmanMenu
 */
class FlagmanMenu extends \CBitrixComponent
{
    /**
     * @return mixed|void
     */
    public function executeComponent()
    {
        if ($this->StartResultCache(36000)) {
            $this->deactivateTrainingIfEmpty();
    
            $this->includeComponentTemplate();
        }
    }
    
    private function deactivateTrainingIfEmpty()
    {
        Loader::includeModule('iblock');
        
        $iblockTrainingId = $this->getIblockId(IblockCode::FLAGMAN_TRAINING);
        $iblockLectionId  = $this->getIblockId(IblockCode::FLAGMAN_LECTIONS);
        $iblockGroomingId = $this->getIblockId(IblockCode::FLAGMAN_GROOMING);
        
        $groomingActivity = $this->setGroomingActivity($iblockGroomingId);
        $trainingActivity = $this->setTrainingActivity($iblockTrainingId);
        $lectionActivity = $this->setLectionActivity($iblockLectionId);
    
        if (!$groomingActivity && !$trainingActivity && !$lectionActivity) {
            $this->abortResultCache();
        }
    }
    
    private function getIblockId($code)
    {
        return \CIBlock::GetList([], [
            'TYPE' => IblockType::GRANDIN,
            'CODE' => $code,
        ])->Fetch()['ID'];
    }
    
    private function setGroomingActivity($id)
    {
        $items = SectionTable::query()
            ->setSelect(['ID', 'NAME'])
            ->setFilter([
                '=IBLOCK_ID' => $id,
                '=ACTIVE'    => 'Y',
            ])
            ->exec()
            ->fetchAll();
        
        foreach ($items as $item) {
            preg_match('/^([0-9]{2,4}).([0-9]{2,4}).([0-9]{2,4})/', $item['NAME'], $matches);
            
            if (strtotime(date('d.m.Y')) < strtotime($matches[0])) {
                $this->arParams['SHOW_GROOMING'] = 'Y';
                
                return true;
            }
        }
    }
    
    private function setTrainingActivity($id)
    {
        $items = SectionTable::query()
            ->setSelect(['ID', 'NAME'])
            ->setFilter([
                '=IBLOCK_ID' => $id,
                '=ACTIVE'    => 'Y',
            ])
            ->exec()
            ->fetchAll();
        
        foreach ($items as $item) {
            preg_match('/^([0-9]{2,4}).([0-9]{2,4}).([0-9]{2,4})/', $item['NAME'], $matches);
            
            if (strtotime(date('d.m.Y')) < strtotime($matches[0])) {
                $this->arParams['SHOW_TRAINING'] = 'Y';
                
                return true;
            }
        }
    }
    
    private function setLectionActivity($id)
    {
        $items = SectionTable::query()
            ->setSelect(['ID', 'NAME'])
            ->setFilter([
                '=IBLOCK_ID'   => $id,
                '=ACTIVE'      => 'Y',
                '=DEPTH_LEVEL' => 1,
            ])
            ->exec()
            ->fetchAll();
        
        if (!empty($items)) {
            $this->arParams['SHOW_LECTION'] = 'Y';
            return true;
        }
    }
}
