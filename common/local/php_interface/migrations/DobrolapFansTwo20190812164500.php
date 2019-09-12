<?php


namespace Sprint\Migration;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Entity\Base;
use CUserFieldEnum;

class DobrolapFansTwo20190812164500 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    protected $description = "Дополняет HL-блок \"Добролап: фаны\" недостающими онлайновыми промокодами из csv по пути: /upload/dobrolap_fans/5.csv";

    const HL_BLOCK_NAME = 'DobrolapFans';

    protected $hlBlockData = [
        'NAME'       => self::HL_BLOCK_NAME,
        'TABLE_NAME' => '4lapy_dobrolap_fans',
        'LANG'       => [
            'ru' => [
                'NAME' => 'Добролап: фаны',
            ],
        ],
    ];

    public function up(){
        $helper = new HelperManager();


        $hlBlockHelper = $helper->Hlblock();


        /** @var \Sprint\Migration\Helpers\UserTypeEntityHelper $userTypeEntityHelper */
        $userTypeEntityHelper = $this->getHelper()->UserTypeEntity();

        $hlBlockId = $hlBlockHelper->getHlblockId(static::HL_BLOCK_NAME);
        $hlblock = HighloadBlockTable::getById($hlBlockId)->fetch();
        $entity  = HighloadBlockTable::compileEntity( $hlblock ); //генерация класса
        $entityClass = $entity->getDataClass();


        $typeNames = [
            "Фотосессия",
            "Футболка",
            "Рубрика",
            "Лицо рекламы",
        ];
        $types = [];

        foreach ($typeNames as $typeName){
            $typeField = CUserFieldEnum::GetList([], ['VALUE' => $typeName])->Fetch();
            $types[] = $typeField['ID'];
        }

        $dir = "/upload/dobrolap_fans";

        for($i=0; $i<count($typeNames); $i++){
            $type = $types[$i];
            $file = $_SERVER['DOCUMENT_ROOT'].$dir.'/'.($i+1).'.csv';

            if(!file_exists($file)){
                echo sprintf("Файл не найден %s", $file);
                return false;
            }

            $handle = fopen($file, "r");
            while(($row = fgetcsv($handle)) !== false){
                $entityClass::add([
                    'UF_CHECK' => $row[0],
                    'UF_TYPE'  => $type,
                ]);
            }
        }
    }

    public function down(){
        return true;
    }
}
