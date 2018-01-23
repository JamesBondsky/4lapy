<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Application;
use FourPaws\Enum\IblockCode;

class BrandReferenceCodes20180120221402 extends SprintMigrationBase
{
    protected $description = 'Изменение кодов брендов под обмен с SAP';
    
    public function parseFile() {
        $prepared = [];
        
        $csv = fopen(__DIR__ . '/brand_map.csv', 'r');
        
        while ($row = fgetcsv($csv, 0, ';')) {
            $prepared[] = [
                'id'   => $row[0],
                'name' => $row[1],
            ];
        }
        
        return $prepared;
    }
    
    public function up() {
        $id = (new HelperManager())->Iblock()->getIblockId(IblockCode::BRANDS);
        
        $connection = Application::getConnection();
        
        $data = $this->parseFile();
        
        foreach ($data as $element) {
            $element['name'] = str_replace("'", "''", $element['name']);
            $element['id']   = str_replace("'", "''", $element['id']);
            $code            = \CUtil::translit($element['name'], 'ru', [
                'replace_space' => '-',
                'replace_other' => '-',
            ]);
            $connection->query(sprintf("UPDATE b_iblock_element SET CODE='%s', XML_ID='%s', NAME='%s' WHERE IBLOCK_ID='%s' AND NAME='%s'",
                                       $code, $element['id'], $element['name'], $id, $element['name']));
            $connection->query(sprintf("UPDATE b_iblock_element SET CODE='%s', XML_ID='%s', NAME='%s' WHERE IBLOCK_ID='%s' AND NAME='%s'",
                                       $code, $element['id'], $element['name'], $id, $element['id']));
        }
    }
    
    public function down() {
        /**
         * Нет необходимости в откате справочника
         */
    }
    
}
