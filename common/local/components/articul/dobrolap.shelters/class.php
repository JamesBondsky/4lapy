<?php

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 24.07.2019
 * Time: 12:31
 */

class CDobrolapSheltersComponent extends \CBitrixComponent
{
    private $iblockId;


    public function onPrepareComponentParams($params): array
    {
        if (!isset($params['CACHE_TIME'])) {
            $params['CACHE_TIME'] = 86400;
        }
        $this->iblockId = IblockUtils::getIblockId(IblockType::GRANDIN, IblockCode::DOBROLAP_SHELTERS);
        return parent::onPrepareComponentParams($params);
    }

    public function executeComponent()
    {
        global $DB;

        if(true) {

            // часть инф-ы по приютам хранится в отдельной таблице, связка по barcode
            $query = "SELECT * FROM 4lapy_animal_shelters";
            $dbres = $DB->Query($query);
            $arShelteres = [];
            while($row = $dbres->Fetch()){
                $arShelteres[$row['barcode']] = $row;
            }

            $dbres = \CIBlockElement::GetList([], ['IBLOCK_ID' => $this->iblockId, 'ACTIVE' => 'Y']);
            while ($row = $dbres->GetNextElement()) {
                $element = $row->GetFields();
                $element['PROPERTIES'] = $row->GetProperties();

                $element['IMG'] = $element['PROPERTIES']['IMG']['VALUE'] ? \CFile::GetPath($element['PROPERTIES']['IMG']['VALUE']) : '/dobrolap/images/shelter_logo/blank_logo.png';
                $element['CITY'] = $this->formatCity($arShelteres[$element['XML_ID']]['city']);
                $element['DESCRIPTION'] = $arShelteres[$element['XML_ID']]['description'];

                $this->arResult['ELEMENTS'][] = $element;
            }

            $this->includeComponentTemplate();
        }
    }

    private function formatCity($city)
    {
        return str_replace('Московская область,', '', $city);
    }

}