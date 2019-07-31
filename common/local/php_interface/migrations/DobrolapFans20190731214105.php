<?php

namespace Sprint\Migration;


use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Entity\Base;
use CUserFieldEnum;

class DobrolapFans20190731214105 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

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

    protected $fields = [
        [
            "FIELD_NAME" => "UF_CHECK",
            "USER_TYPE_ID" => "string",
            "XML_ID" => "",
            "SORT" => "100",
            "MULTIPLE" => "N",
            "MANDATORY" => "Y",
            "SHOW_FILTER" => "N",
            "SHOW_IN_LIST" => "Y",
            "EDIT_IN_LIST" => "Y",
            "IS_SEARCHABLE" => "N",
            "SETTINGS" => [
                "SIZE" => 20,
                "ROWS" => 1,
                "REGEXP" => "",
                "MIN_LENGTH" => 0,
                "MAX_LENGTH" => 0,
                "DEFAULT_VALUE" => "",
            ],
            "EDIT_FORM_LABEL" => [
                "ru" => "Номер чека",
            ],
            "LIST_COLUMN_LABEL" => [
                "ru" => "Номер чека",
            ],
            "LIST_FILTER_LABEL" => [
                "ru" => "Номер чека",
            ],
            "ERROR_MESSAGE" => [
                "ru" => "",
            ],
            "HELP_MESSAGE" => [
                "ru" => "",
            ],
        ],
        [
            "FIELD_NAME" => "UF_DATE_CLOSE",
            "USER_TYPE_ID" => "datetime",
            "XML_ID" => "",
            "SORT" => "100",
            "MULTIPLE" => "N",
            "MANDATORY" => "N",
            "SHOW_FILTER" => "N",
            "SHOW_IN_LIST" => "Y",
            "EDIT_IN_LIST" => "Y",
            "IS_SEARCHABLE" => "N",
            "SETTINGS" => [
                "DEFAULT_VALUE" => [
                    "TYPE" => "NONE",
                    "VALUE" => "",
                ],
                "USE_SECOND" => "Y",
            ],
            "EDIT_FORM_LABEL" => [
                "ru" => "Дата применения"
            ],
            "LIST_COLUMN_LABEL" => [
                "ru" => "Дата применения"
            ],
            "LIST_FILTER_LABEL" => [
                "ru" => "Дата применения"
            ],
            "ERROR_MESSAGE" => [
                "ru" => ""
            ],
            "HELP_MESSAGE" => [
                "ru" => ""
            ],
        ],
        [
            "FIELD_NAME" => "UF_USER_ID",
            "USER_TYPE_ID" => "integer",
            "XML_ID" => "",
            "SORT" => "100",
            "MULTIPLE" => "N",
            "MANDATORY" => "N",
            "SHOW_FILTER" => "N",
            "SHOW_IN_LIST" => "Y",
            "EDIT_IN_LIST" => "Y",
            "IS_SEARCHABLE" => "N",
            "SETTINGS" => [
                "SIZE" => 20,
                "MIN_VALUE" => 0,
                "MAX_VALUE" => 0,
                "DEFAULT_VALUE" => "",
            ],
            "EDIT_FORM_LABEL" => [
                "ru" => "Пользователь",
            ],
            "LIST_COLUMN_LABEL" => [
                "ru" => "Пользователь",
            ],
            "LIST_FILTER_LABEL" => [
                "ru" => "Пользователь",
            ],
            "ERROR_MESSAGE" => [
                "ru" => "",
            ],
            "HELP_MESSAGE" => [
                "ru" => "",
            ]
        ],
        [
            "FIELD_NAME" => "UF_IS_USED",
            "USER_TYPE_ID" => "boolean",
            "XML_ID" => "",
            "SORT" => "100",
            "MULTIPLE" => "N",
            "MANDATORY" => "N",
            "SHOW_FILTER" => "N",
            "SHOW_IN_LIST" => "Y",
            "EDIT_IN_LIST" => "Y",
            "IS_SEARCHABLE" => "N",
            "SETTINGS" => [
                "DEFAULT_VALUE" => 0,
                "DISPLAY" => "CHECKBOX",
                "LABEL" => [
                    0 => "",
                    1 => "",
                ],
                "LABEL_CHECKBOX" => "",
            ],
            "EDIT_FORM_LABEL" => [
                "ru" => "Применён",
            ],
            "LIST_COLUMN_LABEL" => [
                "ru" => "Применён",
            ],
            "LIST_FILTER_LABEL" => [
                "ru" => "Применён",
            ],
            "ERROR_MESSAGE" => [
                "ru" => "",
            ],
            "HELP_MESSAGE" => [
                "ru" => "",
            ],
        ],
        [
            "FIELD_NAME" => "UF_TYPE",
            "USER_TYPE_ID" => "enumeration",
            "XML_ID" => "",
            "SORT" => "100",
            "MULTIPLE" => "N",
            "MANDATORY" => "N",
            "SHOW_FILTER" => "N",
            "SHOW_IN_LIST" => "Y",
            "EDIT_IN_LIST" => "Y",
            "IS_SEARCHABLE" => "N",
            "SETTINGS" => [
                "DISPLAY" => "LIST",
                "LIST_HEIGHT" => 5,
                "CAPTION_NO_VALUE" => "",
                "SHOW_NO_VALUE" => "Y",
            ],
            "EDIT_FORM_LABEL" => [
                "ru" => "Тип",
            ],
            "LIST_COLUMN_LABEL" => [
                "ru" => "Тип",
            ],
            "LIST_FILTER_LABEL" => [
                "ru" => "Тип",
            ],
            "ERROR_MESSAGE" => [
                "ru" => "",
            ],
            "HELP_MESSAGE" => [
                "ru" => "",
            ],
            'ENUMS' => [
                'n1' => [
                    'VALUE' => 'Фотосессия'
                ],
                'n2' => [
                    'VALUE' => 'Футболка',
                ],
                'n3' => [
                    'VALUE' => 'Рубрика'
                ],
                'n4' => [
                    'VALUE' => 'Лицо рекламы'
                ],
            ]
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
            "Онлайн",
        ];
        $types = [];

        foreach ($typeNames as $typeName){
            $typeField = CUserFieldEnum::GetList([], ['VALUE' => $typeName])->Fetch();
            $types[] = $typeField['ID'];
        }

        $dir = "/upload/dobrolap_fans";

        for($i=0; $i<5; $i++){
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
        $helper = new HelperManager();
        return true;
    }

}
