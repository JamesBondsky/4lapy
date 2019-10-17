<?php

namespace Sprint\Migration;


class User_field_2nd_order_coupon20190620132509 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    protected const FIELD_NAME = 'UF_2ND_ORDER_COUPON';

    protected $description = 'Добавление юзерам свойства ' . self::FIELD_NAME;

    protected const ENTITY_ID = 'USER';

    public function up()
    {
        $helper = new HelperManager();

        if ($helper->UserTypeEntity()->addUserTypeEntityIfNotExists('USER', 'UF_2ND_ORDER_COUPON', [
                    'ENTITY_ID'         => static::ENTITY_ID,
                    'FIELD_NAME'        => static::FIELD_NAME,
                    'USER_TYPE_ID'      => 'boolean',
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
                            'DEFAULT_VALUE'  => 0,
                            'DISPLAY'        => 'CHECKBOX',
                            'LABEL'          =>
                                [
                                    0 => '',
                                    1 => '',
                                ],
                            'LABEL_CHECKBOX' => '',
                        ],
                    'EDIT_FORM_LABEL'   =>
                        [
                            'ru' => 'Получен купон на 2-ю покупку',
                        ],
                    'LIST_COLUMN_LABEL' =>
                        [
                            'ru' => 'Получен купон на 2-ю покупку',
                        ],
                    'LIST_FILTER_LABEL' =>
                        [
                            'ru' => 'Получен купон на 2-ю покупку',
                        ],
                    'ERROR_MESSAGE'     =>
                        [
                            'ru' => '',
                        ],
                    'HELP_MESSAGE'      =>
                        [
                            'ru' => '',
                        ],
                ])) {
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
