<?php

namespace Sprint\Migration;


class CatalogCategoryAddBanner2Field20190319183724 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Добавляет свойство для второго баннера";

    public const ENTITY_ID = 'IBLOCK_2_SECTION';

    protected $field = [
        'ENTITY_ID'         => self::ENTITY_ID,
        'FIELD_NAME'        => "UF_LANDING_BANNER2",
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
                'SIZE'          => 100,
                'ROWS'          => 8,
                'REGEXP'        => '',
                'MIN_LENGTH'    => 0,
                'MAX_LENGTH'    => 0,
                'DEFAULT_VALUE' => '',
            ],
        'EDIT_FORM_LABEL'   =>
            [
                'ru' => 'Баннер для лендинга 2',
            ],
        'LIST_COLUMN_LABEL' =>
            [
                'ru' => 'Баннер для лендинга 2',
            ],
        'LIST_FILTER_LABEL' =>
            [
                'ru' => 'Баннер для лендинга 2',
            ],
        'ERROR_MESSAGE'     =>
            [
                'ru' => '',
            ],
        'HELP_MESSAGE'      =>
            [
                'ru' => 'Баннер для лендинга 2',
            ],
    ];

    public function up(){
        $field = $this->field;

        if ($this->getHelper()->UserTypeEntity()->addUserTypeEntityIfNotExists(static::ENTITY_ID, $field['FIELD_NAME'], $field)) {
            $this->log()->info('Пользовательское свойство ' . $field['FIELD_NAME'] . ' создано');
        } else {
            $this->log()->error('Ошибка при создании пользовательского свойства ' . $field['FIELD_NAME']);
            return false;
        }

        return true;
    }

    public function down(){
        if ($this->getHelper()->UserTypeEntity()->deleteUserTypeEntityIfExists(static::ENTITY_ID, $this->field['FIELD_NAME'])) {
            $this->log()->info('Пользовательское свойство ' . $this->field['CODE'] . ' удалено');
        } else {
            $this->log()->error('Ошибка при удалении пользовательского свойства ' . $this->field['FIELD_NAME']);
            return false;
        }

        return true;

    }

}
