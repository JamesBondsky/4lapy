<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;

class User_field_discount20180314233221 extends SprintMigrationBase
{
    protected $description = 'Добавление юзерам свойства UF_DISCOUNT';

    const ENTITY_ID = 'USER';

    const FIELD_NAME = 'UF_DISCOUNT';

    public function up()
    {
        /** @var \Sprint\Migration\Helpers\UserTypeEntityHelper $userTypeEntityHelper */
        $userTypeEntityHelper = $this->getHelper()->UserTypeEntity();

        if ($userTypeEntityHelper->addUserTypeEntityIfNotExists(
            static::ENTITY_ID,
            static::FIELD_NAME,
            [
                'USER_TYPE_ID'      => 'double',
                'XML_ID'            => static::FIELD_NAME,
                'SORT'              => 9,
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'N',
                'SHOW_FILTER'       => 'N',
                'SHOW_IN_LIST'      => 'Y',
                'EDIT_IN_LIST'      => 'N',
                'IS_SEARCHABLE'     => 'N',
                "EDIT_FORM_LABEL" => array('ru' => 'Скидка'),
                "LIST_COLUMN_LABEL" => array('ru' => 'Скидка'),
                "LIST_FILTER_LABEL" => array('ru' => 'Скидка'),
                "ERROR_MESSAGE" => '',
                "HELP_MESSAGE" => 'Скидка',
            ]
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
