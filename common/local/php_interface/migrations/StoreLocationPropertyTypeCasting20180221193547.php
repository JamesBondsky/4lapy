<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use FourPaws\Migrator\Client\Store;
use FourPaws\Migrator\Entity\Store as StoreEntity;
use FourPaws\Migrator\Provider\StoreLocation;

class StoreLocationPropertyTypeCasting20180221193547 extends SprintMigrationBase
{

    protected /** @noinspection ClassOverridesFieldOfSuperClassInspection */
        $description = 'Изменение типа свойства "Регион" для склада';

    public function up()
    {
        $helper = $this->getHelper()->UserTypeEntity();

        $helper->deleteUserTypeEntityIfExists('CAT_STORE', 'UF_LOCATION');
        $helper->addUserTypeEntityIfNotExists('CAT_STORE', 'UF_LOCATION', array(
            'ENTITY_ID' => 'CAT_STORE',
            'FIELD_NAME' => 'UF_LOCATION',
            'USER_TYPE_ID' => 'sale_location',
            'XML_ID' => 'XML_LOCATION',
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
                    'ru' => 'Местоположение (город)',
                ),
            'LIST_COLUMN_LABEL' =>
                array(
                    'ru' => 'Местоположение (город)',
                ),
            'LIST_FILTER_LABEL' =>
                array(
                    'ru' => 'Местоположение (город)',
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

        $locationMigrator = new Store(new StoreLocation(new StoreEntity(Store::ENTITY_NAME)), ['limit' => 1000, 'force' => true]);
        $locationMigrator->save();
    }

    public function down()
    {
        /**
         * Нет необходимости
         */
    }

}
