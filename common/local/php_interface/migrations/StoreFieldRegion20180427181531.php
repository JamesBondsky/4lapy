<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Catalog\StoreTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\LocationBundle\LocationService;

class StoreFieldRegion20180427181531 extends SprintMigrationBase
{

    protected /** @noinspection ClassOverridesFieldOfSuperClassInspection */
        $description = 'Создание свойства "Регион" для склада';

    /**
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @return bool
     */
    public function up(): bool
    {
        $helper = $this->getHelper()->UserTypeEntity();

        $helper->deleteUserTypeEntityIfExists('CAT_STORE', 'UF_REGION');
        if (!$helper->addUserTypeEntityIfNotExists('CAT_STORE', 'UF_REGION', array(
            'ENTITY_ID' => 'CAT_STORE',
            'FIELD_NAME' => 'UF_REGION',
            'USER_TYPE_ID' => 'sale_location',
            'XML_ID' => 'XML_LOCATION',
            'SORT' => '110',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'N',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                array(
                    'DEFAULT_VALUE' => '',
                ),
            'EDIT_FORM_LABEL' =>
                array(
                    'ru' => 'Местоположение (регион)',
                ),
            'LIST_COLUMN_LABEL' =>
                array(
                    'ru' => 'Местоположение (регион)',
                ),
            'LIST_FILTER_LABEL' =>
                array(
                    'ru' => 'Местоположение (регион)',
                ),
            'ERROR_MESSAGE' =>
                array(
                    'ru' => '',
                ),
            'HELP_MESSAGE' =>
                array(
                    'ru' => '',
                ),
        ))) {
            $this->log()->error('Не удалось создать свойство UF_REGION');
            return false;
        }

        /** @var LocationService $locationService */
        $locationService = Application::getInstance()->getContainer()->get('location.service');
        $stores = StoreTable::getList(['select' => ['ID', 'XML_ID', 'UF_LOCATION']]);
        $locations = [];
        while ($store = $stores->fetch()) {
            $locationCode = $store['UF_LOCATION'];

            if (!$locationCode) {
                $this->log()->warning(sprintf('Не задано местоположение для склада  %s', $store['XML_ID']));
                continue;
            }

            if (!array_key_exists($locationCode, $locations)) {
                $location = $locationService->findLocationByCode($store['UF_LOCATION']);
                $regionCode = '';
                foreach ($location['PATH'] as $pathItem) {
                    if ($pathItem['CODE'] === LocationService::LOCATION_CODE_MOSCOW) {
                        $regionCode = $pathItem['CODE'];
                        break;
                    }
                    if ($pathItem['TYPE'] === LocationService::TYPE_REGION) {
                        $regionCode = $pathItem['CODE'];
                        break;
                    }
                }

                $locations[$locationCode] = $regionCode;
            }

            $regionCode = $locations[$locationCode];
            $updateResult = StoreTable::update($store['ID'], ['UF_REGION' => $regionCode]);
            if (!$updateResult->isSuccess()) {
                $this->log()->warning(sprintf('Не удалось обновить склад %s', $store['XML_ID']));
            }
        }

        return true;
    }

    public function down()
    {
        /**
         * Нет необходимости
         */
    }

}
