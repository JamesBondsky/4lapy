<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;

class UserShopProperty20180212194850 extends SprintMigrationBase
{
    protected /** @noinspection ClassOverridesFieldOfSuperClassInspection */
        $description = 'Добавление привязки к магазину';

    public function up()
    {
        $helper = new HelperManager();

        $helper->UserTypeEntity()->deleteUserTypeEntityIfExists('USER', 'UF_SHOP');
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists('USER', 'UF_SHOP', array(
            'ENTITY_ID' => 'USER',
            'FIELD_NAME' => 'UF_SHOP',
            'USER_TYPE_ID' => 'catalog_store_list',
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
                    'DEFAULT_VALUE' => '',
                ),
            'EDIT_FORM_LABEL' =>
                array(
                    'ru' => 'Магазин',
                ),
            'LIST_COLUMN_LABEL' =>
                array(
                    'ru' => 'Магазин',
                ),
            'LIST_FILTER_LABEL' =>
                array(
                    'ru' => 'Магазин',
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

    public function down()
    {
        /**
         * Нет необходимости.
         */
    }

}
