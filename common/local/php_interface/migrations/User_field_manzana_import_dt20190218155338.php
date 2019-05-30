<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;

class User_field_manzana_import_dt20190218155338 extends SprintMigrationBase
{

    protected $description = "Добавление юзерам свойства UF_MANZANA_IMPORT_DT";

    const ENTITY_ID = 'USER';

    const FIELD_NAME = 'UF_MANZANA_IMPORT_DT';

    public function up()
    {
        /** @var \Sprint\Migration\Helpers\UserTypeEntityHelper $userTypeEntityHelper */
        $userTypeEntityHelper = $this->getHelper()->UserTypeEntity();

        if ($userTypeEntityHelper->addUserTypeEntityIfNotExists(
            static::ENTITY_ID,
            static::FIELD_NAME,
            array(
                'USER_TYPE_ID' => 'datetime',
                'XML_ID' => static::FIELD_NAME,
                'SORT' => '100',
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' =>
                    array(
                        'DEFAULT_VALUE' =>
                            array(
                                'TYPE' => 'NONE',
                                'VALUE' => '',
                            ),
                        'USE_SECOND' => 'Y',
                    ),
                'EDIT_FORM_LABEL' =>
                    array(
                        'ru' => 'Дата и время последнего импорта заказов из Manzana',
                    ),
                'LIST_COLUMN_LABEL' =>
                    array(
                        'ru' => '',
                    ),
                'LIST_FILTER_LABEL' =>
                    array(
                        'ru' => '',
                    ),
                'ERROR_MESSAGE' =>
                    array(
                        'ru' => '',
                    ),
                'HELP_MESSAGE' =>
                    array(
                        'ru' => '',
                    ),
            )
        )) {
            $this->log()->info('Пользовательское свойство ' . static::FIELD_NAME . ' создано');
        } else {
            $this->log()->error('Ошибка при создании пользовательского свойства ' . static::FIELD_NAME);

            return false;
        }

        return true;
    }

    public function down()
    {
        /** @var \Sprint\Migration\Helpers\UserTypeEntityHelper $userTypeEntityHelper */
        $userTypeEntityHelper = $this->getHelper()->UserTypeEntity();

        if ($userTypeEntityHelper->deleteUserTypeEntityIfExists(static::ENTITY_ID, static::FIELD_NAME)) {
            $this->log()->info('Пользовательское свойство ' . static::FIELD_NAME . ' удалено');
        } else {
            $this->log()->error('Ошибка при удалении пользовательского свойства ' . static::FIELD_NAME);

            return false;
        }

        return true;
    }

}
