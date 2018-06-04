<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;

class ForgotBasketAgentAdd20180603154653 extends SprintMigrationBase
{

    protected $description = 'Добавление агента забытой корзины';

    public function up()
    {
        /** запуск агента каждый час */
        \CAgent::AddAgent('\FourPaws\SaleBundle\AgentController\ForgotBasketController::sendEmailByOldBasketAfter3Days();',
            '', 'Y', 60 * 60);
    }
}
