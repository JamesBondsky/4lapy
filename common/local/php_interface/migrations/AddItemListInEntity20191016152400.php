<?php


namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\HLBlock\HLBlockUtils;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Highloadblock\HighloadBlockTable;
use FourPaws\Enum\HlblockCode;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;


class AddItemListInEntity20190731175343 extends SprintMigrationBase
{
    protected $description = 'Добавляет новый тип пуша';
    
    private const PROP_CODE = 'UF_TYPE';
    private const NEW_PROP_CODE = 'category';
    
    public function up()
    {
        $hlId = HLBlockUtils::getHLBlockIdByName(HlblockCode::PUSH_MESSAGES);
        
        $entity = 'HLBLOCK_' . $hlId;
        $dbRes = \CUserTypeEntity::GetList(
            array(),
            array('ENTITY_ID' => $entity , 'FIELD_NAME' => self::PROP_CODE)
        );
        
        $field = $dbRes->Fetch();
        
        $obEnum = new \CUserFieldEnum;
        $obEnum->SetEnumValues($field['ID'], [
            'n0' => [
                'XML_ID' => self::NEW_PROP_CODE,
                'VALUE' => 'ID раздела каталога'
            ]
        ]);
        
        return true;
    }
    
    public function down()
    {
    
    }
}
