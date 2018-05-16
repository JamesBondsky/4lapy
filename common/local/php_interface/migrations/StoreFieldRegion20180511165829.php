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

class StoreFieldRegion20180511165829 extends SprintMigrationBase
{

    protected /** @noinspection ClassOverridesFieldOfSuperClassInspection */
        $description = 'Обновление свойства "Регион" для склада и создание свойства "Район"';


    protected $fields = [
        'UF_REGION'    => [
            'ENTITY_ID'         => 'CAT_STORE',
            'FIELD_NAME'        => 'UF_REGION',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'XML_REGION',
            'SORT'              => '110',
            'MULTIPLE'          => 'Y',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'N',
            'EDIT_IN_LIST'      => 'N',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          =>
                [
                    'DEFAULT_VALUE' => '',
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'ru' => 'Местоположение (регион)',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Местоположение (регион)',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Местоположение (регион)',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE'      =>
                [
                    'ru' => '',
                ],
        ],
        'UF_SUBREGION' => [
            'ENTITY_ID'         => 'CAT_STORE',
            'FIELD_NAME'        => 'UF_SUBREGION',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'XML_SUBREGION',
            'SORT'              => '110',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'N',
            'EDIT_IN_LIST'      => 'N',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          =>
                [
                    'DEFAULT_VALUE' => '',
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'ru' => 'Местоположение (район)',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Местоположение (район)',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Местоположение (район)',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE'      =>
                [
                    'ru' => '',
                ],
        ],
    ];

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
        foreach ($this->fields as $code => $field) {
            $helper->deleteUserTypeEntityIfExists('CAT_STORE', $code);
            if (!$helper->addUserTypeEntityIfNotExists('CAT_STORE', $code, $field)) {
                $this->log()->error('Не удалось создать свойство ' . $code);
                return false;
            }
        }

        /** @var LocationService $locationService */
        $locationService = Application::getInstance()->getContainer()->get('location.service');
        $stores = StoreTable::getList(['select' => ['ID', 'XML_ID', 'UF_LOCATION']]);
        while ($store = $stores->fetch()) {
            $locationCode = $store['UF_LOCATION'];

            if (!$locationCode) {
                $this->log()->warning(sprintf('Не задано местоположение для склада  %s', $store['XML_ID']));
                continue;
            }

            $subregion = $locationService->findLocationSubRegion($locationCode)['CODE'];
            $region = $locationService->findLocationRegion($locationCode)['CODE'];
            if ($region === LocationService::LOCATION_CODE_MOSCOW) {
                $region = [$region, LocationService::LOCATION_CODE_MOSCOW_REGION];
            }

            $updateResult = StoreTable::update(
                $store['ID'],
                [
                    'UF_SUBREGION' => $subregion,
                    'UF_REGION' => (array)($region ?? '')
                ]
            );
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
