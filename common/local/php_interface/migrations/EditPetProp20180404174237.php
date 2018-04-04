<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use FourPaws\Helpers\HighloadHelper;

class EditPetProp20180404174237 extends SprintMigrationBase {

    protected $description = 'Изменение свойства в питомцах для отображения';

    public function up(){
        $helper = new HelperManager();

        $hlblockId = HighloadHelper::getIdByName('Pet');
        $entityId = 'HLBLOCK_' . $hlblockId;

        $hlReferenceId = HighloadHelper::getIdByName('ForWho');
        $hlField = $helper->UserTypeEntity()->getUserTypeEntity('HLBLOCK_' .$hlReferenceId, 'UF_NAME');
        $helper->UserTypeEntity()->updateUserTypeEntityIfExists($entityId, 'UF_TYPE', [
            'FIELD_NAME'        => 'UF_TYPE',
            'USER_TYPE_ID'      => 'hlblock',
            'XML_ID'            => '',
            'SORT'              => '300',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          =>
                [
                    'DISPLAY'       => 'LIST',
                    'LIST_HEIGHT'   => 1,
                    'HLBLOCK_ID'    => $hlReferenceId,
                    'HLFIELD_ID'    => $hlField['ID'],
                    'DEFAULT_VALUE' => 0,
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'ru' => 'Тип питомца',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Тип питомца',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Тип питомца',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE'      =>
                [
                    'ru' => 'Тип питомца',
                ],
        ]);

    }
}
