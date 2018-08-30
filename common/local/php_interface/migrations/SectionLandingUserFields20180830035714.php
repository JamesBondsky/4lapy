<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;

/**
 * Class SectionLandingUserFields20180830035714
 *
 * @package Sprint\Migration
 */
class SectionLandingUserFields20180830035714 extends SprintMigrationBase
{

    protected $description = 'Создание пользовательского поля раздела каталога для лендинга - показывать блок примерки';

    /**
     * @return bool|void
     */
    public function up()
    {
        $this->getHelper()->UserTypeEntity()->addUserTypeEntityIfNotExists('IBLOCK_2_SECTION', 'UF_SHOW_FITTING', [
            'ENTITY_ID' => 'IBLOCK_2_SECTION',
            'FIELD_NAME' => 'UF_SHOW_FITTING',
            'USER_TYPE_ID' => 'boolean',
            'XML_ID' => '',
            'SORT' => '600',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                [
                    'DEFAULT_VALUE' => 0,
                    'DISPLAY' => 'CHECKBOX',
                    'LABEL' =>
                        [
                            0 => '',
                            1 => '',
                        ],
                    'LABEL_CHECKBOX' => '',
                ],
            'EDIT_FORM_LABEL' =>
                [
                    'ru' => 'Показывать блок примерки',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Показывать блок примерки',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Показывать блок примерки',
                ],
            'ERROR_MESSAGE' =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE' =>
                [
                    'ru' => 'Показывать блок примерки',
                ],
        ]);
    }

    /**
     * @return void
     */
    public function down()
    {
        $this->getHelper()->UserTypeEntity()->deleteUserTypeEntityIfExists('IBLOCK_2_SECTION', 'UF_SHOW_FITTING');
    }

}
