<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;

class ForgotBasketAgentsDelete20180920130421 extends SprintMigrationBase
{
    protected $description = 'Удаление агентов забытой корзины';

    public function up()
    {
        $agents = \CAgent::GetList([], ['NAME' => '%ForgotBasket%']);
        while ($agent = $agents->Fetch()) {
            \CAgent::Delete($agent['ID']);
        }
    }

    public function down()
    {

    }
}
