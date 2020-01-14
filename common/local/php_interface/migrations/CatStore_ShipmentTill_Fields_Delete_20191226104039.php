<?php

namespace Sprint\Migration;


class CatStore_ShipmentTill_Fields_Delete_20191226104039 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = 'Удаляет у складов поля "Отгрузка до" (UF_SHIPMENT_TILL_11 и т.п.)';

    public function up()
    {
        $helper = new HelperManager();


        $helper->UserTypeEntity()->deleteUserTypeEntityIfExists('CAT_STORE', 'UF_SHIPMENT_TILL_11');
        $helper->UserTypeEntity()->deleteUserTypeEntityIfExists('CAT_STORE', 'UF_SHIPMENT_TILL_13');
        $helper->UserTypeEntity()->deleteUserTypeEntityIfExists('CAT_STORE', 'UF_SHIPMENT_TILL_18');
    }

    public function down()
    {
        $helper = new HelperManager();

        // Создание полей было в StoreShipmentFields20180310213227.php

    }

}
