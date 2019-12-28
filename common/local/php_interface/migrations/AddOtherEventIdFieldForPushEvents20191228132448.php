<?php

namespace Sprint\Migration;


class AddOtherEventIdFieldForPushEvents20191228132448 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    
    protected $description = "Добавление дополнительного поля для нестандартных ид заказов (н-р по подписке)";
    
    public function up()
    {
        $helper = new HelperManager();
        
        $hlblockId = $helper->Hlblock()->getHlblockId('PushMessages');
        $entityId  = 'HLBLOCK_' . $hlblockId;
        
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_OTHER_EVENT_ID', [
            'FIELD_NAME'        => 'UF_OTHER_EVENT_ID',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => '',
            'SORT'              => '100',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          =>
                [
                    'SIZE'          => 20,
                    'ROWS'          => 1,
                    'REGEXP'        => '',
                    'MIN_LENGTH'    => 0,
                    'MAX_LENGTH'    => 0,
                    'DEFAULT_VALUE' => '',
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'ru' => 'Поле для нестандартных номеров заказов',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Поле для нестандартных номеров заказов',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Поле для нестандартных номеров заказов',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE'      =>
                [
                    'ru' => '',
                ],
        ]);
    }
    
    public function down()
    {
        $helper    = new HelperManager();
        $hlblockId = $helper->Hlblock()->getHlblockId('PushMessages');
        $entityId  = 'HLBLOCK_' . $hlblockId;
        
        $helper->UserTypeEntity()->deleteUserTypeEntityIfExists($entityId, 'UF_OTHER_EVENT_ID');
    }
}
