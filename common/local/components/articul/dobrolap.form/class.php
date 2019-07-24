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

class CDobrolapFormComponent extends \CBitrixComponent
{
    private $dobrolapFormIblockId;


    public function onPrepareComponentParams($params): array
    {
        if (!isset($params['CACHE_TIME'])) {
            $params['CACHE_TIME'] = 86400;
        }

        $this->dobrolapFormIblockId = IblockUtils::getIblockId(IblockType::GRANDIN, IblockCode::DOBROLAP_FORM);

        return parent::onPrepareComponentParams($params);
    }

    public function executeComponent()
    {
        global $USER, $APPLICATION;

        $this->arResult['USER_ID'] = $USER->GetId();

        if($this->isFormSubmit()){
            $responce = new FourPaws\App\Response\JsonResponse();
            $checkNumber = $_REQUEST['check_number'];
            $name = $checkNumber." ".$USER->GetFullName().' ['.$USER->GetID().']';

            if($this->isCorrectCheckNumber($checkNumber)){
                $obElem = new \CIBlockElement;
                $ID = $obElem->Add([
                    'IBLOCK_ID' => $this->dobrolapFormIblockId,
                    'NAME' => $name,
                    'PROPERTY_VALUES' => [
                        'CHECK_NUMBER' => $checkNumber,
                        'USER_ID' => $this->arResult['USER_ID'],
                    ]
                ]);

                if($ID > 0){
                    $responce->setData(['success' => 1]);
                } else {
                    $responce->setData(['error' => $obElem->LAST_ERROR, 'error_message' => 'Не удалось зарегистрировать чек, пожалуйста, обратитесь к администратору']);
                }
            }

            $APPLICATION->RestartBuffer();
            return $responce->send();
        } else {
            $this->includeComponentTemplate();
        }

    }

    private function isFormSubmit()
    {
        global $USER;
        return $USER->IsAuthorized() && !empty($_REQUEST['check_number']);
    }

    private function isCorrectCheckNumber($checkNumber)
    {
        return true;
    }



}