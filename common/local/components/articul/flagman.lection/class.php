<?php

use Bitrix\Main\Loader;
use Articul\Landing\Orm\LectionsTable;
use Bitrix\Main\Entity\ReferenceField;

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
            $iblockId = $this->getIblockId();
            $items    = $this->getItems($iblockId);
            
            $this->groupItems($items);
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
            ->setSelect([
                'ID',
                'NAME',
                'PREVIEW_PICTURE',
                'FREE_SITS'         => 'UTS.FREE_SITS',
                'SITS'              => 'UTS.SITS',
                'SECTION_NAME'      => 'SECTION.NAME',
                'SECTION_ID'        => 'SECTION.ID',
                'MAIN_SECTION_NAME' => 'MAIN_SECTION.NAME',
            ])
            ->setFilter(['=IBLOCK_ID' => $iblockId, '>UTS.FREE_SITS' => 0])
            ->registerRuntimeField(new ReferenceField(
                'SECTION',
                'Bitrix\Iblock\SectionTable',
                ['=this.IBLOCK_SECTION_ID' => 'ref.ID']
            ))
            ->registerRuntimeField(new ReferenceField(
                'MAIN_SECTION',
                'Bitrix\Iblock\SectionTable',
                ['=this.SECTION.IBLOCK_SECTION_ID' => 'ref.ID']
            ))
            ->exec()
            ->fetchAll();
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
    
    private function groupItems($items)
    {
        foreach ($items as $key => $item) {
            $this->arResult['ITEMS'][$item['SECTION_ID']]['SECTION_NAME']      = $item['SECTION_NAME'];
            $this->arResult['ITEMS'][$item['SECTION_ID']]['MAIN_SECTION_NAME'] = $item['MAIN_SECTION_NAME'];
            $this->arResult['ITEMS'][$item['SECTION_ID']][$key]['NAME']              = $item['NAME'];
        }
    }
}
