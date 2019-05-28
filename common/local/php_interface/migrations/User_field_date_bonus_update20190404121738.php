<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;

class User_field_date_bonus_update20190404121738 extends SprintMigrationBase
{
    protected const FIELD_NAME = 'UF_DATE_BONUS_UPDATE';

    protected $description = 'Добавление юзерам свойства ' . self::FIELD_NAME;

    protected const ENTITY_ID = 'USER';


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
                'SORT' => '70',
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
                        'ru' => 'Дата и время последнего обновления информации о бонусах из Manzana',
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
