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

class CDobrolapNecessaryComponent extends \CBitrixComponent
{
    private $iblockId;


    public function onPrepareComponentParams($params): array
    {
        if (!isset($params['CACHE_TIME'])) {
            $params['CACHE_TIME'] = 86400;
        }
        $this->iblockId = IblockUtils::getIblockId(IblockType::GRANDIN, IblockCode::DOBROLAP_NECESSARY);
        return parent::onPrepareComponentParams($params);
    }

    public function executeComponent()
    {
        global $USER, $APPLICATION;

        $dbres = \CIBlockElement::GetList([], ['IBLOCK_ID' => $this->iblockId, 'ACTIVE' => 'Y']);
        while($row = $dbres->GetNextElement()){
            $element = $row->GetFields();
            $element['PROPERTIES'] = $row->GetProperties();

            $arProgress = [0,0,0];
            $progressValue = $element['PROPERTIES']['PROGRESS']['VALUE'] * 3;

            for($i = 0; $i < 4; $i++){
                $arProgress[$i] = $progressValue;
                $progressValue -= 100;

                if($progressValue > 0) {
                    $arProgress[$i] = 100;
                } else {
                    $arProgress[$i] = 100 - abs($progressValue);
                    break;
                }
            }

            if($arProgress[2] == 100){
                $element['FULL'] = true;
            }

            $element["PROGRESS"] = $arProgress;

            $this->arResult['ELEMENTS'][] = $element;
        }


        $this->includeComponentTemplate();
    }



}