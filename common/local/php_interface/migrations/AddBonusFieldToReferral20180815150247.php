<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;

class AddBonusFieldToReferral20180815150247 extends SprintMigrationBase
{

    protected $description = 'Добавление поля бонусов к рефералам';

    public function up()
    {
        $helper = new HelperManager();

        $entityId = 'HLBLOCK_' . $helper->Hlblock()->getHlblockId('Referral');

        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_BONUS', [
            'FIELD_NAME'        => 'UF_BONUS',
            'USER_TYPE_ID'      => 'double',
            'XML_ID'            => '',
            'SORT'              => '700',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'N',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          =>
                [
                    'PRECISION'     => 4,
                    'SIZE'          => 20,
                    'MIN_VALUE'     => 0.0,
                    'MAX_VALUE'     => 0.0,
                    'DEFAULT_VALUE' => 0.0,
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'ru' => 'Бонусы',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Бонусы',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Бонусы',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE'      =>
                [
                    'ru' => 'Бонусы',
                ],
        ]);
    }
}
