<?php

namespace Sprint\Migration;


class CatalogCategoryAddDeliveryTextField20190319155025 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    public const ENTITY_ID = 'IBLOCK_2_SECTION';
    protected $description = "Добавляет свойство для категории с текстом о запрете доставки ветаптеки";

    protected $field = [
        'FIELD_NAME'        => 'UF_SHOW_DEL_TEXT',
        'USER_TYPE_ID'      => 'boolean',
        'XML_ID'            => 'UF_SHOW_DEL_TEXT',
        'SORT'              => '100',
        'MULTIPLE'          => 'N',
        'MANDATORY'         => 'N',
        'SHOW_FILTER'       => 'N',
        'SHOW_IN_LIST'      => 'Y',
        'EDIT_IN_LIST'      => 'Y',
        'IS_SEARCHABLE'     => 'N',
        'EDIT_FORM_LABEL'   => [
            'ru' => 'Показывать фразу о запрете доставки',
        ],
        'LIST_COLUMN_LABEL' => [
            'ru' => 'Показывать фразу о запрете доставки',
        ],
        'LIST_FILTER_LABEL' => [
            'ru' => 'Показывать фразу о запрете доставки',
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
