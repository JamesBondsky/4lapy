<?php

namespace Sprint\Migration;

class PriceTypesDel20180109190000 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {
    protected $description = 'Удаление неиспользуемых типов цен';

    public function up()
    {
        //$obHelper = new \Sprint\Migration\HelperManager();
        if (!\Bitrix\Main\Loader::includeModule('catalog')) {
            return false;
        }
        $dbItems = \CCatalogGroup::GetList(
            [],
            [
                'BASE' => 'N',
                //'!XML_ID' => 'IR77',
            ],
            false,
            false,
            [
                'ID'
            ]
        );
        while ($arItem = $dbItems->fetch()) {
            \CCatalogGroup::Delete($arItem['ID']);
        }
    }

    public function down()
    {
        //
    }
}
