<?php

namespace Sprint\Migration;


use Bitrix\Main\DB\Exception;

class HLBlockFestivalUsersDataPassportFieldAdd20190523152724 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = "Добавление поля UF_PASSPORT в HL-блок FestivalUsersData";

    public function up()
    {
        $helper = new HelperManager();

        $hlblockId = $helper->Hlblock()->getHlblockId('FestivalUsersData');
        $entityId = 'HLBLOCK_' . $hlblockId;
        if ($entityId) {
            $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_PASSPORT', array(
                'FIELD_NAME' => 'UF_PASSPORT',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => '',
                'SORT' => '100',
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'E',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' =>
                    array(
                        'SIZE' => 20,
                        'ROWS' => 1,
                        'REGEXP' => '',
                        'MIN_LENGTH' => 0,
                        'MAX_LENGTH' => 0,
                        'DEFAULT_VALUE' => '',
                    ),
                'EDIT_FORM_LABEL' =>
                    array(
                        'ru' => 'Номер паспорта',
                    ),
                'LIST_COLUMN_LABEL' =>
                    array(
                        'ru' => 'Номер паспорта',
                    ),
                'LIST_FILTER_LABEL' =>
                    array(
                        'ru' => 'Номер паспорта',
                    ),
                'ERROR_MESSAGE' =>
                    array(
                        'ru' => '',
                    ),
                'HELP_MESSAGE' =>
                    array(
                        'ru' => '',
                    ),
            ));
        } else {
            throw new Exception('Пустой $entityId');
        }
    }

    public function down()
    {
        $helper = new HelperManager();

        $hlblockId = $helper->Hlblock()->getHlblockId('FestivalUsersData');
        $entityId = 'HLBLOCK_' . $hlblockId;
        if ($entityId) {
            $helper->UserTypeEntity()->deleteUserTypeEntityIfExists($entityId, 'UF_PASSPORT');
        } else {
            throw new Exception('Пустой $entityId');
        }
    }

}
