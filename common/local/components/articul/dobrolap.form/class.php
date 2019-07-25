<?php

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Result;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use Psr\Log\LoggerAwareTrait;

/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 24.07.2019
 * Time: 12:31
 */

class CDobrolapFormComponent extends \CBitrixComponent
{
    private $dobrolapFormIblockId;

    const FANS_TABLE = '4lapy_dobrolap_fans';

    use LoggerAwareTrait;

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

            $checkStatusResult = $this->validateCheckNumber($checkNumber);

            if($checkStatusResult->isSuccess()){
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
                    $result = $this->useCheckNumber($checkNumber);
                    if(!$result->isSuccess()){
                        $errMsg = implode('; ', $checkStatusResult->getErrorMessages());
                        $this->logger->error(sprintf("Ошибка обновления чека: %s", $errMsg), ['USER_ID' => $USER->GetID()]);
                    }
                } else {
                    $responce->setData(['error' => $obElem->LAST_ERROR, 'error_message' => 'Не удалось зарегистрировать чек, пожалуйста, обратитесь к администратору']);
                    $this->logger->error(sprintf("Не удалось зарегистрировать чек: %s", $obElem->LAST_ERROR), ['USER_ID' => $USER->GetID()]);
                }
            } else {
                $errMsg = implode('; ', $checkStatusResult->getErrorMessages());
                $responce->setData(['error' => $checkStatusResult->getErrorMessages(), 'error_message' => $errMsg]);
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

    private function validateCheckNumber($checkNumber)
    {
        global $DB;

        $result = new Result;

        $query = "SELECT * FROM ".self::FANS_TABLE." WHERE UF_CHECK = '{$checkNumber}'";
        $check = $DB->Query($query)->Fetch();

        if(empty($check)){
            $result->addError(new \Bitrix\Main\Error('Чек не найден, проверьте правильность введённого номера'));
        }
        else if(!empty($check['UF_IS_USED'])){
            $result->addError(new \Bitrix\Main\Error('Указанный чек уже был зарегистрирован ранее'));
        }

        return $result;
    }

    private function useCheckNumber($checkNumber)
    {
        global $DB, $USER;
        $result = new Result;

        $query = "SELECT * FROM ".self::FANS_TABLE." WHERE UF_CHECK = '{$checkNumber}'";
        $check = $DB->Query($query)->Fetch();

        if(empty($check)){
            $result->addError(new \Bitrix\Main\Error('Чек не найден'));
        } else {
            $query = "UPDATE ".self::FANS_TABLE." set UF_IS_USED = 1, UF_USER_ID = {$USER->GetID()}, UF_DATE_CLOSE = '".date('Y-m-d H:i:s')."'  WHERE ID = '{$check['ID']}'";
            $DB->Query($query);
        }

        return $result;
    }
}