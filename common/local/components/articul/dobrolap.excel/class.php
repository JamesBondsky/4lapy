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
        ;

        $i = 2;
        foreach($this->fans as $fan){
            $page->setCellValue('A'.$i, $fan['PROPERTIES']['CHECK_NUMBER']['VALUE'])
                ->setCellValue('B'.$i, implode(' ',array_filter([$fan['USER']['LAST_NAME'], $fan['USER']['NAME'], $fan['USER']['SECOND_NAME']])))
                ->setCellValue('C'.$i, $fan['USER']['PERSONAL_PHONE'])
                ->setCellValue('D'.$i, $fan['USER']['EMAIL'])
                ->setCellValue('E'.$i, $fan['DATE_CREATE'])
            ;
            $i++;
        }

        foreach(range('A','E') as $columnID) {
            $phpExcel->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header("Content-Disposition:attachment;filename='Заявки по лендингу Добролап_".date('Y-m-d').".xls'");

        $objWriter = PHPExcel_IOFactory::createWriter($phpExcel, 'Excel2007');
        $objWriter->save('php://output');

        exit();
    }

}