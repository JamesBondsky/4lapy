<?php

namespace Sprint\Migration;

use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class MainMenuItems20180212170000 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {
    protected $description = 'Ссылки на втором уровне главного меню товаров по питомцам';
    protected $sectionCodes = [
        'cat', 'dog', 'rodent',
        'bird', 'fish', 'reptile',
    ];

    public function up() {
        $result = false;
        $iblockHelper = $this->getHelper()->Iblock();
        $iblockId = $iblockHelper->getIblockId(IblockCode::MAIN_MENU, IblockType::MENU);
        if ($iblockId) {
            $result = true;
            $items = \CIBlockSection::GetList(
                [],
                [
                    'IBLOCK_ID' => $iblockId,
                    '=CODE' => $this->sectionCodes,
                    'DEPTH_LEVEL' => 2
                ],
                false,
                [
                    'ID', 'UF_HREF'
                ]
            );
            while ($item = $items->fetch()) {
                if (stripos(trim($item['UF_HREF']), 'javascript') !== false) {
                    $iblockSection = new \CIBlockSection();
                    $res = $iblockSection->Update($item['ID'], ['UF_HREF' => '']);
                    if (!$res) {
                        $result = false;
                        $this->log()->error('Не удалось изменить секцию: '.$iblockSection->LAST_ERROR);
                    } else {
                        $this->log()->info('Изменена секция id: '.$item['ID']);
                    }
                }
            }
        } else {
            $this->log()->error('Не найден инфоблок меню');
        }

        return $result;
    }

    public function down() {
        //
    }
}
