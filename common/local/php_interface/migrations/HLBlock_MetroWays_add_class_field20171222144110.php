<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;

class HLBlock_MetroWays_add_class_field20171222144110 extends SprintMigrationBase
{
    protected $description = 'Добавление поля класс';

    public function up()
    {
        $helper = new HelperManager();
        
        $hlblockId = $helper->Hlblock()->getHlblockId('MetroWays');
    
        $entityId  = 'HLBLOCK_' . $hlblockId;
    
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            'UF_CLASS',
            [
                'FIELD_NAME'        => 'UF_CLASS',
                'USER_TYPE_ID'      => 'string',
                'XML_ID'            => '',
                'SORT'              => '100',
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'N',
                'SHOW_FILTER'       => 'N',
                'SHOW_IN_LIST'      => 'Y',
                'EDIT_IN_LIST'      => 'Y',
                'IS_SEARCHABLE'     => 'N',
                'SETTINGS'          => [
                    'SIZE'          => 20,
                    'ROWS'          => 1,
                    'REGEXP'        => '',
                    'MIN_LENGTH'    => 0,
                    'MAX_LENGTH'    => 0,
                    'DEFAULT_VALUE' => '',
                ],
                'EDIT_FORM_LABEL'   => [
                    'ru' => 'Класс',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Класс',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Класс',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Класс',
                ],
            ]
        );
    }

    public function down()
    {
    }
}
