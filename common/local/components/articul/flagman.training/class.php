<?php

use Bitrix\Main\Loader;
use Bitrix\Iblock\SectionTable;

/**
 * Class FlagmanTraining
 */
class FlagmanTraining extends \CBitrixComponent
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
        $result = SectionTable::query()
            ->setSelect(['ID', 'NAME'])
            ->setFilter([
                '=IBLOCK_ID' => $iblockId,
                '=ACTIVE'    => 'Y',
            ])
            ->exec()
            ->fetchAll();
        
        foreach ($result as $key => $item) {
            preg_match('/([0-9]{2,4}).([0-9]{2,4}).([0-9]{2,4})/', $item['NAME'], $matches);
            if (strtotime($matches[0]) < strtotime('today')) {
                unset($result[$key]);
            }
        }
        
        return $result;
    }
}
