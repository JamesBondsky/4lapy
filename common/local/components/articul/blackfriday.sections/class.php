<?php

use Articul\BlackFriday\Orm\BFSectionsTable;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Loader;
use \Bitrix\Iblock\Model\Section;

/**
 * Class BlackFridaySections
 */
class BlackFridaySections extends \CBitrixComponent
{
    /**
     * @var int $iblockId
     */
    private $iblockId;
    
    /**
     * @var string $iblockCode
     */
    private $iblockCode = 'black_friday_sections';
    
    /**
     * @var $sectinonsWithElements
     */
    private $sectinonsWithElements;
    
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
            $this->arResult['SECTION_WITH_ELEMENTS'] = $this->getElements();
            $this->arResult['EMPTY_SECTIONS']        = $this->getSections();
            
            if (!$this->arResult['SECTION_WITH_ELEMENTS'] && !$this->arResult['EMPTY_SECTIONS']) {
                $this->abortResultCache();
            }
            
            $this->includeComponentTemplate();
        }
    }
    
    /**
     * @return array
     */
    private function getElements()
    {
        $elements = BFSectionsTable::query()
            ->setSelect(['ID', 'IBLOCK_SECTION_ID', 'NAME', 'LINK' => 'UTS.LINK', 'SECTION_NAME' => 'SECTION.NAME'])
            ->setFilter(['=IBLOCK_ID' => $this->iblockId, '=ACTIVE' => 'Y'])
            ->registerRuntimeField(new ReferenceField(
                'SECTION',
                '\Bitrix\Iblock\SectionTable',
                ['=this.IBLOCK_SECTION_ID' => 'ref.ID']
            ))
            ->setCacheTtl($this->arParams['CACHE_TIME'])
            ->exec()
            ->fetchAll();
        
        $result = $this->allocateElements($elements);
        
        return $result;
    }
    
    /**
     * @return mixed
     */
    private function getSections()
    {
        $entity   = Section::compileEntityByIblock($this->iblockId);
        $sections = $entity::query()
            ->setSelect(['ID', 'NAME', 'UF_LINK'])
            ->setFilter(['=IBLOCK_ID' => $this->iblockId, '=ACTIVE' => 'Y', '!ID' => $this->sectinonsWithElements])
            ->setCacheTtl($this->arParams['CACHE_TIME'])
            ->exec()
            ->fetchAll();
        
        return $sections;
    }
    
    /**
     * @return mixed
     */
    private function getIblockId()
    {
        return \CIBlock::GetList([], ['CODE' => $this->iblockCode])->Fetch()['ID'];
    }
    
    /**
     * @param $elements
     * @return array
     */
    private function allocateElements($elements)
    {
        $result = [];
        
        foreach ($elements as $key => $element) {
            if (!in_array($element['IBLOCK_SECTION_ID'], $this->sectinonsWithElements)) {
                $this->sectinonsWithElements[] = $element['IBLOCK_SECTION_ID'];
            }
            $result[$element['IBLOCK_SECTION_ID']]['SECTION_NAME'] = $element['SECTION_NAME'];
            $result[$element['IBLOCK_SECTION_ID']]['ITEMS'][]      = $element;
        }
        
        return $result;
    }
}
