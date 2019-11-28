<?php

use Articul\Landing\Orm\GroomingTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Query\Join;
use \Bitrix\Main\Entity\Query;

/**
 * Class FlagmanGrooming
 */
class FlagmanGrooming extends \CBitrixComponent
{
    private $iblockCode = 'flagman_grooming';
    private $iblockId;
    
    /**
     * @param $arParams
     * @return array
     * @throws \Bitrix\Main\LoaderException
     */
    public function onPrepareComponentParams($arParams)
    {
        Loader::includeModule('iblock');
        $this->iblockId = $this->getIblockId();
        
        return parent::onPrepareComponentParams($arParams);
    }
    
    /**
     * @return mixed|void
     */
    public function executeComponent()
    {
        $result = $this->getDays();

        foreach ($result as $item) {
            if (!empty($item['TIME'])) {
                preg_match('/([0-9]{2,4}).([0-9]{2,4}).([0-9]{2,4})/', $item['NAME'], $matches);

                if (strtotime($matches[0]) < strtotime('today')) {
                    continue;
                }
    
                preg_match('/^[0-9]{2}/', $item['TIME'], $pregMTime);
                if ($matches[0] == date('d.m.Y') && $pregMTime[0] <= date('H')) {
                    continue;
                }
                
                $this->arResult['DAYS'][$item['ID']] = $item['NAME'];
            }
        }
        
        $this->sortDays();

        $this->includeComponentTemplate();
    }
    
    /**
     * @return array
     */
    private function getDays()
    {
        $result = [];
        
        try {
            $result = SectionTable::query()
                ->setSelect(['ID', 'NAME', 'TIME' => 'TIMES.NAME'])
                ->registerRuntimeField(
                    new ReferenceField(
                        'TIMES',
                        'Bitrix\Iblock\ElementTable',
                        ['=this.ID' => 'ref.IBLOCK_SECTION_ID']
                    ))
                ->setFilter(['=IBLOCK_ID' => $this->iblockId, '=ACTIVE' => 'Y'])
                ->exec()
                ->fetchAll();
            
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * @return mixed
     */
    private function getIblockId()
    {
        return \CIBlock::GetList([], ['CODE' => $this->iblockCode])->Fetch()['ID'];
    }
    
    private function sortDays()
    {
        natsort($this->arResult['DAYS']);
    }
}