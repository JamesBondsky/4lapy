<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\HLBlock\HLBlockUtils;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class AddPropertyShares20190625150723 extends SprintMigrationBase
{
    private $code = 'FILE_IMPORT';
    protected $description = 'Добавляет свойство в инфоблок - "Файл привязки"';
    public function up()
    {
        $arFields = Array(
            "NAME" => "Файл привязки",
            "CODE" => $this->code,
            "PROPERTY_TYPE" => "F",
            "IBLOCK_ID" => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::SHARES),
        );

        $ibp = new \CIBlockProperty;
        $PropID = $ibp->Add($arFields);
        var_dump($PropID);
        var_dump($arFields);
    }

    public function down()
    {
        $props = \CIBlockProperty::GetList([], [
            'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::SHARES),
            'CODE' => $this->code,
        ]);

        while ($propItem = $props->GetNext()) {
            \CIBlockProperty::Delete($propItem['ID']);
        }
    }
}
