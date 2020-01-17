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
     * @throws \Bitrix\Main\LoaderException
     */
    public function executeComponent()
    {
        Loader::includeModule('iblock');
        
        try {
            $iblockId = $this->getIblockId();
            $items    = $this->getItems($iblockId);
            
            $this->groupItems($items);
            $this->sortItems();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        
        $this->includeComponentTemplate();
    }
    
    /**
     * @return mixed
     */
    private function getIblockId()
    {
        return \CIBlock::GetList([], ['=CODE' => 'flagman_lections'])->Fetch()['ID'];
    }
    
    /**
     * @param $iblockId
     * @return array
     */
    private function getItems($iblockId)
    {
        return LectionsTable::query()
            ->setSelect([
                'ID',
                'NAME',
                'FREE_SITS'         => 'UTS.FREE_SITS',
                'SITS'              => 'UTS.SITS',
                'SECTION_NAME'      => 'SECTION.NAME',
                'SECTION_ID'        => 'SECTION.ID',
                'MAIN_SECTION_NAME' => 'MAIN_SECTION.NAME',
                'MAIN_SECTION_SORT' => 'MAIN_SECTION.SORT',
                'PICTURE'           => 'MAIN_SECTION.PICTURE',
            ])
            ->setFilter(['=IBLOCK_ID' => $iblockId, '=ACTIVE' => 'Y'])
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
            ->setOrder(['SORT' => 'ASC'])
            ->exec()
            ->fetchAll();
    }
    
    /**
     * @param $items
     */
    private function groupItems($items)
    {
        foreach ($items as $key => $item) {
            $this->arResult['ITEMS'][$item['SECTION_ID']]['SECTION_NAME']      = $item['SECTION_NAME'];
            $this->arResult['ITEMS'][$item['SECTION_ID']]['PICTURE']           = \CFile::GetPath($item['PICTURE']);
            $this->arResult['ITEMS'][$item['SECTION_ID']]['MAIN_SECTION_NAME'] = $item['MAIN_SECTION_NAME'];
            $this->arResult['ITEMS'][$item['SECTION_ID']]['MAIN_SECTION_SORT'] = $item['MAIN_SECTION_SORT'];
            if ($item['FREE_SITS'] <= 0) {
                continue;
            }
            
            $this->arResult['ITEMS'][$item['SECTION_ID']]['DETAIL_INFO'][$key]['NAME'] = $item['NAME'];
            $this->arResult['ITEMS'][$item['SECTION_ID']]['DETAIL_INFO'][$key]['ID']   = $item['ID'];
        }
        
        foreach ($this->arResult['ITEMS'] as &$element) {
            $element['AVAILABLE'] = 'Y';
            
            if (!$element['DETAIL_INFO']) {
                $element['AVAILABLE'] = 'N';
            }
        }
    }
    
    private function sortItems()
    {
        foreach ($this->arResult['ITEMS'] as $key => &$item) {
            uasort($item['DETAIL_INFO'], function ($a, $b) {
                preg_match('/^([0-9]{2})/', $a['NAME'], $matchesA);
                preg_match('/^([0-9]{2})/', $b['NAME'], $matchesB);
                
                return ($matchesA[0] > $matchesB[0]) ? 1 : -1;
            });
        }
        
        // uasort($this->arResult['ITEMS'], function ($a, $b) {
        //     return ($a > $b) ? -1 : 1;
        // });
    }
}
