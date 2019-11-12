<?php

use Bitrix\Main\Loader;
use Bitrix\Iblock\SectionTable;

/**
 * Class FlagmanTraining
 */
class FlagmanTraining1 extends \CBitrixComponent
{
    /**
     * @return mixed|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function executeComponent()
    {
        try {
            Loader::includeModule('iblock');
            
            $iblockId = $this->getIblockId();
            
            $this->arResult['SECTIONS'] = $this->getSections($iblockId);
            
            $this->includeComponentTemplate();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
    
    private function getIblockId()
    {
        return \CIBlock::GetList([], [
            'TYPE' => 'grandin',
            'CODE' => 'flagman_training',
        ])->Fetch()['ID'];
    }
    
    private function getSections($iblockId)
    {
        return SectionTable::query()
            ->setSelect(['ID', 'NAME'])
            ->setFilter([
                '=IBLOCK_ID' => $iblockId,
                '=ACTIVE'    => 'Y',
            ])
            ->exec()
            ->fetchAll();
    }
}
