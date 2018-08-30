<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;

/**
 * Class FilterShoAsPicturePropertyAdd20180831010837
 *
 * @package Sprint\Migration
 */
class FilterShoAsPicturePropertyAdd20180831010837 extends SprintMigrationBase
{
    protected $description = 'Добавление нового типа отображения фильтра';

    /**
     * @return bool|void
     */
    public function up()
    {
        $this->getHelper()->UserTypeEntity()->addUserTypeEntityIfNotExists('HLBLOCK_28', 'UF_SHOW_WITH_PICTURE', array(
            'ENTITY_ID' => 'HLBLOCK_28',
            'FIELD_NAME' => 'UF_SHOW_WITH_PICTURE',
            'USER_TYPE_ID' => 'boolean',
            'XML_ID' => '',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                array(
                    'DEFAULT_VALUE' => '1',
                    'DISPLAY' => 'CHECKBOX',
                    'LABEL' =>
                        array(
                            0 => '',
                            1 => '',
                        ),
                    'LABEL_CHECKBOX' => '',
                ),
            'EDIT_FORM_LABEL' =>
                array(
                    'ru' => 'Варианты фильтра с изображением',
                ),
            'LIST_COLUMN_LABEL' =>
                array(
                    'ru' => 'Варианты фильтра с изображением',
                ),
            'LIST_FILTER_LABEL' =>
                array(
                    'ru' => 'Варианты фильтра с изображением',
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
    }

    /**
     * @return bool|void
     */
    public function down()
    {
        $this->getHelper()->UserTypeEntity()->deleteUserTypeEntityIfExists('HLBLOCK_28', 'UF_SHOW_WITH_PICTURE');
    }

}
