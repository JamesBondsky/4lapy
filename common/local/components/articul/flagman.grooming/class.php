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
     * @param array $params
     *
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
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
        if ($this->startResultCache($this->arParams['CACHE_TIME'])) {
            Loader::includeModule('iblock');
            $this->iblockId = $this->getIblockId();
    
            $this->arResult['CLINICS'] = $this->getClinics();

            $this->includeComponentTemplate();
            
            if (!$this->iblockId) {
                $this->abortResultCache();
            }
        }
    }
    
    /**
     * @return mixed
     */
    private function getIblockId()
    {
        return \CIBlock::GetList([], ['CODE' => $this->iblockCode])->Fetch()['ID'];
    }
    
    private function getClinics()
    {
        $result = [];
        
        try {
            $result = SectionTable::query()
                ->setSelect(['ID', 'NAME'])
                ->setFilter(['=IBLOCK_ID' => $this->iblockId, '=ACTIVE' => 'Y', '=DEPTH_LEVEL' => 1])
                ->setOrder(['SORT' => 'ASC'])
                ->exec()
                ->fetchAll();
        
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        
        return $result;
    }
}
