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
        Loader::includeModule('iblock');
    
        $iblockId = $this->getIblockId();
    
        $result = SectionTable::query()
            ->setSelect(['ID', 'NAME'])
            ->setFilter([
                '=IBLOCK_ID' => $iblockId,
                '=ACTIVE'    => 'Y',
            ])
            ->exec()
            ->fetchAll();

        if (empty($result)) {
            $this->arParams['SHOW_TRAINING'] = 'N';
        }
    }
    
    private function getIblockId()
    {
        return \CIBlock::GetList([], [
            'TYPE' => 'grandin',
            'CODE' => 'flagman_training',
        ])->Fetch()['ID'];
    }
}
