<?php

namespace Sprint\Migration;


class HlBlockPushMessagesAdd20191009121450 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    
    protected $description = "";
    
    public function up()
    {
        $helper = new HelperManager();
        
        $hlblockId = $helper->Hlblock()->addHlblockIfNotExists([
            'NAME'       => 'PushMessages',
            'TABLE_NAME' => 'api_push_messages',
            'LANG'       =>
                [
                    'ru' =>
                        [
                            'NAME' => 'Push уведомления',
                        ],
                ],
        ]);
        $entityId  = 'HLBLOCK_' . $hlblockId;
    
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_TITLE', [
            'FIELD_NAME'        => 'UF_TITLE',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'UF_TITLE',
            'SORT'              => '15',
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
                    'REGEXP'        => null,
                    'MIN_LENGTH'    => 0,
                    'MAX_LENGTH'    => 0,
                    'DEFAULT_VALUE' => null,
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'en' => '',
                    'ru' => 'Заголовок сообщения',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'en' => '',
                    'ru' => 'Заголовок сообщения',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'en' => '',
                    'ru' => 'Заголовок сообщения',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'en' => null,
                    'ru' => null,
                ],
            'HELP_MESSAGE'      =>
                [
                    'en' => null,
                    'ru' => null,
                ],
        ]);
    
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_PHOTO', [
            'FIELD_NAME'        => 'UF_PHOTO',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'UF_PHOTO',
            'SORT'              => '50',
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
                    'REGEXP'        => null,
                    'MIN_LENGTH'    => 0,
                    'MAX_LENGTH'    => 0,
                    'DEFAULT_VALUE' => null,
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'en' => '',
                    'ru' => 'Ссылка на фото',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'en' => '',
                    'ru' => 'Ссылка на фото',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'en' => '',
                    'ru' => 'Ссылка на фото',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'en' => null,
                    'ru' => null,
                ],
            'HELP_MESSAGE'      =>
                [
                    'en' => null,
                    'ru' => null,
                ],
        ]);
    }
    
    public function down()
    {
        $helper = new HelperManager();
    
        $hlblockId = $helper->Hlblock()->getHlblockId('PushMessages');
        $entityId = 'HLBLOCK_' . $hlblockId;
        if ($entityId) {
            $helper->UserTypeEntity()->deleteUserTypeEntityIfExists($entityId, 'UF_TITLE');
            $helper->UserTypeEntity()->deleteUserTypeEntityIfExists($entityId, 'UF_PHOTO');
        } else {
            throw new Exception('Пустой $entityId');
        }
        
    }
    
}
