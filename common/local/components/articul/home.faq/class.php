<?php

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class CHomeFaqComponent extends \CBitrixComponent
{
    private $iblockId;

    public function onPrepareComponentParams($params): array
    {
        if (!isset($params['CACHE_TIME'])) {
            $params['CACHE_TIME'] = 86400;
        }
        $params['TYPE'] = $params['TYPE'] ?: 'default';
        $this->iblockId = IblockUtils::getIblockId(IblockType::GRANDIN, IblockCode::HOME_FAQ);
        return parent::onPrepareComponentParams($params);
    }

    public function executeComponent()
    {
        if($this->startResultCache()){
            $filter = [
                'IBLOCK_ID'    => $this->iblockId,
                'ACTIVE'       => 'Y',
                'SECTION_CODE' => $this->arParams['SECTION_CODE'] ?: false,
            ];

            $dbres = \CIBlockElement::GetList(['SORT' => 'ASC'], $filter, false, false, ['ID', 'NAME', 'PREVIEW_TEXT', 'PREVIEW_PICTURE']);
            while($row = $dbres->GetNextElement()){
                $element = $row->GetFields();
                $this->arResult['ELEMENTS'][] = $element;
            }

            $this->includeComponentTemplate();
        }
    }
}