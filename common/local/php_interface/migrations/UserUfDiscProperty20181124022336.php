<?php

namespace Sprint\Migration;


class UserUfDiscProperty20181124022336 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Добавление свойства пользователя UF_DISC для бонусной карты клиента";
    
    const ENTITY_ID = 'USER';

    const FIELD_NAME = 'UF_DISC';

    public function up(){
        $helper = new HelperManager();

        $userTypeEntityHelper = $this->getHelper()->UserTypeEntity();

        if ($userTypeEntityHelper->addUserTypeEntityIfNotExists(
            static::ENTITY_ID,
            static::FIELD_NAME,
            [
                'USER_TYPE_ID'      => 'string',
                'XML_ID'            => static::FIELD_NAME,
                'SORT'              => 500,
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'N',
                'SHOW_FILTER'       => 'N',
                'SHOW_IN_LIST'      => 'Y',
                'EDIT_IN_LIST'      => 'Y',
                'IS_SEARCHABLE'     => 'N',
            ]
        )) {
            $this->log()->info('Пользовательское свойство ' . static::FIELD_NAME . ' создано');
        } else {
            $this->log()->error('Ошибка при создании пользовательского свойства ' . static::FIELD_NAME);
            return false;
        }

    }

    public function down(){
        $helper = new HelperManager();

        $userTypeEntityHelper = $this->getHelper()->UserTypeEntity();

        if ($userTypeEntityHelper->deleteUserTypeEntityIfExists(static::ENTITY_ID, static::FIELD_NAME)) {
            $this->log()->info('Пользовательское свойство ' . static::FIELD_NAME . ' удалено');
        } else {
            $this->log()->error('Ошибка при удалении пользовательского свойства ' . static::FIELD_NAME);

            return false;
        }

    }

}
