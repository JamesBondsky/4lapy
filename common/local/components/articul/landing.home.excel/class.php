<?php

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Result;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\External\Export\ExcelExport;
use Psr\Log\LoggerAwareTrait;

/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 24.07.2019
 * Time: 12:31
 */

class CLandingHomeExcelComponent extends \CBitrixComponent
{
    private $iblockId;
    private $items;

    use LazyLoggerAwareTrait;

    public function onPrepareComponentParams($params): array
    {
        $this->iblockId = IblockUtils::getIblockId(IblockType::GRANDIN, IblockCode::HOME_IMAGES);
        return parent::onPrepareComponentParams($params);
    }

    public function executeComponent()
    {
        $dbresElem = ElementTable::getList([
            'order'  => ['ID' => 'DESC'],
            'select' => ['*'],
            'filter' => ['IBLOCK_ID' => $this->iblockId],
            //'limit' => 2,
        ]);

        while($item = $dbresElem->fetch()){
            $dbresProps = CIBlockElement::GetProperty($this->iblockId, $item['ID']);

            while($prop = $dbresProps->Fetch()){
                $item['PROPERTIES'][$prop['CODE']] = [
                        'NAME' => $prop['NAME'],
                        'VALUE' => $prop['VALUE'],
                        'CODE' => $prop['CODE'],
                    ];
            }

            $this->items[$item['ID']] = $item;
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
        if(!(count($this->items) > 0)){
            throw new \Exception("Нет подходящих заявок");
        }

        $excelExport = new ExcelExport('Уютно_жить_раскраски');

        $headers = [
            "Дата загрузки",
            "Id пользователя",
            "Логин",
            "ФИО",
            "Телефон",
            "Email",
            "Рисунок"
        ];

        $rows = [];
        foreach($this->items as $i => $item){
            $rows[$i] = [
                "Дата загрузки"   => $item['DATE_CREATE']->format('d.m.y H:i:s'),
                "Id пользователя" => $item['PROPERTIES']['USER_ID']['VALUE'],
                "Логин"           => $item['PROPERTIES']['LOGIN']['VALUE'],
                "ФИО"             => $item['PROPERTIES']['FIO']['VALUE'],
                "Телефон"         => $item['PROPERTIES']['USER_ID']['VALUE'],
                "Email"           => $item['PROPERTIES']['USER_ID']['VALUE'],
                "Рисунок"         =>
                    [
                        'type'  => 'bitrix_img',
                        'value' => $item['PREVIEW_PICTURE'],
                    ],
            ];
        }

        $excelExport->generateExcel($headers, $rows);
    }

}