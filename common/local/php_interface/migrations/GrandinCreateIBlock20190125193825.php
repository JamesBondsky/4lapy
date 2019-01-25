<?php

namespace Sprint\Migration;


class GrandinCreateIBlock20190125193825 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "";

    public function up(){
        $helper = new HelperManager();

        $helper->Iblock()->addIblockTypeIfNotExists([

        ])

    }

    public function down(){
        $helper = new HelperManager();

        //your code ...

    }

}
