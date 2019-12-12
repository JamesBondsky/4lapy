<?php

use Bitrix\Main\Loader;
use Bitrix\Iblock\SectionTable;

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
        $this->deactivateTrainingIfEmpty();
        
        $this->includeComponentTemplate();
    }
    
    private function deactivateTrainingIfEmpty()
    {
        if ($this->StartResultCache(36000)) {
            Loader::includeModule('iblock');
            
            $iblockTrainingId = $this->getIblockTrainingId();
            $iblockLectionId = $this->getIblockLectionId();
            $iblockGroomingId = $this->getIblockGroomingId();
            
            $groomingActivity = $this->setGroomingActivity($iblockGroomingId);
            $trainingActivity = $this->setTrainingActivity($iblockTrainingId);
            $lectionActivity = $this->setLectionActivity($iblockLectionId);
            
            if (!$groomingActivity && !$trainingActivity && !$lectionActivity) {
                $this->abortResultCache();
            }
        }
    }
    
    private function getIblockTrainingId()
    {
        return \CIBlock::GetList([], [
            'TYPE' => 'grandin',
            'CODE' => 'flagman_training',
        ])->Fetch()['ID'];
    }
    
    private function getIblockLectionId()
    {
        return \CIBlock::GetList([], [
            'TYPE' => 'grandin',
            'CODE' => 'flagman_lections',
        ])->Fetch()['ID'];
    }
    
    private function getIblockGroomingId()
    {
        return \CIBlock::GetList([], [
            'TYPE' => 'grandin',
            'CODE' => 'flagman_grooming',
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
                '=IBLOCK_ID' => $id,
                '=ACTIVE'    => 'Y',
                '=DEPTH_LEVEL' => 1
            ])
            ->exec()
            ->fetchAll();
        
        if (!empty($items)) {
            $this->arParams['SHOW_LECTION'] = 'Y';
            return true;
        }
    }
}
