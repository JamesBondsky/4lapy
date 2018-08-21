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

class StoreFieldBaseShopLocation20180820195003 extends SprintMigrationBase
{

    protected /** @noinspection ClassOverridesFieldOfSuperClassInspection */
        $description = 'Создание свойства склада "Является базовым для"';

    protected $fields = [
        'UF_BASE_SHOP_LOC' => [
            'ENTITY_ID'         => 'CAT_STORE',
            'FIELD_NAME'        => 'UF_BASE_SHOP_LOC',
            'USER_TYPE_ID'      => 'sale_location',
            'XML_ID'            => 'XML_BASE_SHOP_LOC',
            'SORT'              => '120',
            'MULTIPLE'          => 'Y',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'N',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          =>
                [
                    'DEFAULT_VALUE' => '',
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'ru' => 'Является базовым для',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Является базовым для',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Является базовым для',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE'      =>
                [
                    'ru' => 'Магазин считается базовым для данного списка местоположений',
                ],
        ],
    ];

    /**
     * @throws \Exception
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

        return true;
    }

    public function down()
    {
        /**
         * Нет необходимости
         */
    }

}
