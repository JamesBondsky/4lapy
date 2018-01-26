<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;

class Store_field_is_base_shop20171214145943 extends SprintMigrationBase
{
    protected $description = 'Добавление складам свойства UF_IS_BASE_SHOP - флага базового магазина';

    const ENTITY_ID = 'CAT_STORE';

    const FIELD_NAME = 'UF_IS_BASE_SHOP';

    public function up()
    {
        /** @var \Sprint\Migration\Helpers\UserTypeEntityHelper $userTypeEntityHelper */
        $userTypeEntityHelper = $this->getHelper()->UserTypeEntity();

        if ($userTypeEntityHelper->addUserTypeEntityIfNotExists(
            static::ENTITY_ID,
            static::FIELD_NAME,
            [
                'USER_TYPE_ID'      => 'boolean',
                'XML_ID'            => 'XML_IS_BASE_SHOP',
                'SORT'              => 500,
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'N',
                'SHOW_FILTER'       => 'N',
                'SHOW_IN_LIST'      => 'Y',
                'EDIT_IN_LIST'      => 'Y',
                'IS_SEARCHABLE'     => 'N',
                'EDIT_FORM_LABEL'   => [
                    'ru' => 'Является базовым магазином',
                    'en' => 'Is base shop',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Является базовым магазином',
                    'en' => 'Is base shop',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Является базовым магазином',
                    'en' => 'Is base shop',
                ],
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
