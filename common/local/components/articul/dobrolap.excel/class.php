<?php

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Iblock\ElementTable;
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

    private $fans;

    private $hlFans;

    private $enumTypes;

    const HL_BLOCK_NAME = 'DobrolapFans';

    use LazyLoggerAwareTrait;

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
        $dbresElem = ElementTable::getList([
            'select' => ['*'],
            'filter' => ['IBLOCK_ID' => $this->dobrolapFormIblockId],
            //'limit' => 2,
        ]);
        while($fan = $dbresElem->fetch()){
            $dbresProps = CIBlockElement::GetProperty($this->dobrolapFormIblockId, $fan['ID']);
            while($prop = $dbresProps->Fetch()){
                $fan['PROPERTIES'][$prop['CODE']] = [
                        'NAME' => $prop['NAME'],
                        'VALUE' => $prop['VALUE'],
                        'CODE' => $prop['CODE'],
                    ];
            }

            if($fan['PROPERTIES']['USER_ID']['VALUE'] > 0){
                $user = CUser::GetByID($fan['PROPERTIES']['USER_ID']['VALUE'])->Fetch();
                if(!$user){
                    $this->log()->error(sprintf("Пользователь не найден: %s [%s]", $fan['PROPERTIES']['USER_ID']['VALUE'], $fan['ID']));
                    continue;
                }
            } else {
                $this->log()->error("Пользователь не привязан");
                continue;
            }

            $fan['USER'] = $user;
            $this->fans[$fan['ID']] = $fan;
        }

        try {
            $this->obtainCheckTypes();
            $this->generateExcel();
        } catch (\Exception $e) {
            $this->log()->error(sprintf("Не удалось сгенерировать Excel: %s", $e->getMessage()));
        }
    }

    /**
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     */
    private function generateExcel()
    {
        if(!(count($this->fans) > 0)){
            throw new \Exception("Нет подходящих заявок");
        }

        $phpExcel = new PHPExcel();
        $phpExcel->setActiveSheetIndex(0);
        $page = $phpExcel->getActiveSheet();

        $page->setCellValue('A1', "Номер чека")
            ->setCellValue('B1', "ФИО")
            ->setCellValue('C1', "Телефон")
            ->setCellValue('D1', "Email")
            ->setCellValue('E1', "Дата оформления")
            ->setCellValue('F1', "Тип")
        ;

        $i = 2;
        foreach($this->fans as $fan){
            $page->setCellValue('A'.$i, $fan['PROPERTIES']['CHECK_NUMBER']['VALUE'])
                ->setCellValue('B'.$i, implode(' ',array_filter([$fan['USER']['LAST_NAME'], $fan['USER']['NAME'], $fan['USER']['SECOND_NAME']])))
                ->setCellValue('C'.$i, $fan['USER']['PERSONAL_PHONE'])
                ->setCellValue('D'.$i, $fan['USER']['EMAIL'])
                ->setCellValue('E'.$i, $fan['DATE_CREATE'])
                ->setCellValue('F'.$i, $this->getEnumTypeValue($fan['PROPERTIES']['CHECK_NUMBER']['VALUE']))
            ;
            $i++;
        }

        foreach(range('A','F') as $columnID) {
            $phpExcel->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition:attachment;filename="Dobrolap_' .date('Y_m_d'). '.xls"');

        $objWriter = PHPExcel_IOFactory::createWriter($phpExcel, 'Excel2007');
        $objWriter->save('php://output');

        exit();
    }

    /**
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private function obtainCheckTypes()
    {
        if(!$this->fans){
            return;
        }

        $fansIds = [];
        foreach($this->fans as $fan) {
            $fansIds[] = $fan['PROPERTIES']['CHECK_NUMBER']['VALUE'];
        }

        $hlData = HighloadBlockTable::getList([
                'select' => array('*'),
                'filter' => ['NAME' => self::HL_BLOCK_NAME],
        ])->fetch();

        if(!$hlData){
            throw new \Exception('HL-блок не найден');
        }

        $entity = HighloadBlockTable::compileEntity($hlData);
        $entityClass = $entity->getDataClass();

        $entityId = 'HLBLOCK_'.$hlData['ID'];
        $fieldData = CUserTypeEntity::GetList([], ['ENTITY_ID' => $entityId, 'FIELD_NAME' => 'UF_TYPE'])->fetch();

        $dbres = CUserFieldEnum::GetList([], ['USER_FIELD_ID' => $fieldData['ID']]);
        while($row = $dbres->Fetch()){
            $this->enumTypes[$row['ID']] = $row;
        }

        $dbres = $entityClass::getList([
            'select' => ['*'],
            'filter' => ['UF_CHECK' => $fansIds]
        ]);
        while($row = $dbres->fetch()){
            $this->hlFans[$row['UF_CHECK']] = $row;
        }
    }

    /**
     * @param $fanId
     * @return mixed
     */
    private function getEnumTypeValue($fanId)
    {
        $valueId = $this->hlFans[$fanId]['UF_TYPE'];
        return $this->enumTypes[$valueId]['VALUE'];
    }

}