<?php

namespace Sprint\Migration;

class HLBlockPersonalCouponUsersFUserFieldAdd20191219111237 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = 'Добавляет в HL-блок "Купоны персональных предложений (привязка к пользователям)" поле UF_FUSER_ID и делает поле UF_USER_ID необязательным';

    public function up()
    {
        $helper = new HelperManager();

        $hlblockId = $helper->Hlblock()->getHlblockId('PersonalCouponUsers');
        $entityId = 'HLBLOCK_' . $hlblockId;

        $helper->UserTypeEntity()->updateUserTypeEntityIfExists($entityId, 'UF_USER_ID', [
            'MANDATORY'         => 'N',
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_FUSER_ID', [
            'FIELD_NAME'        => 'UF_FUSER_ID',
            'USER_TYPE_ID'      => 'double',
            'XML_ID'            => '',
            'SORT'              => '1200',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'I',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          =>
                [
                    'PRECISION'     => 4,
                    'SIZE'          => 20,
                    'MIN_VALUE'     => 0.0,
                    'MAX_VALUE'     => 0.0,
                    'DEFAULT_VALUE' => '',
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'ru' => 'FUSER_ID',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'FUSER_ID',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'FUSER_ID',
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
        $helper = new HelperManager();

        $hlblockId = $helper->Hlblock()->getHlblockId('PersonalCouponUsers');
        $entityId = 'HLBLOCK_' . $hlblockId;

        $helper->UserTypeEntity()->updateUserTypeEntityIfExists($entityId, 'UF_USER_ID', [
            'MANDATORY'         => 'Y',
        ]);
        $helper->UserTypeEntity()->deleteUserTypeEntityIfExists($entityId, 'UF_FUSER_ID');

    }

}
