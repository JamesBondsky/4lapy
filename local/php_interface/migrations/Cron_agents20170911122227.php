<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use COption;

class Cron_agents20170911122227 extends SprintMigrationBase
{

    protected $description = "Агенты на кроне";

    public function up()
    {
        COption::SetOptionString('main', 'agents_use_crontab', 'Y');
        COption::SetOptionString('main', 'check_agents', 'Y');
        COption::SetOptionString('main', 'mail_event_bulk', '20');
    }

    public function down()
    {

    }

}
