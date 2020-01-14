<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;

class Version20201401110650 extends SprintMigrationBase
{
    protected $description = 'Добавляет таблицу для ошибок при импорте шансов';

    public function up()
    {
        global $DB;

        $DB->Query('create table 4lapy_user_chance_error (
    `id` int not null auto_increment primary key,
    `user_id` int not null
);
');
    }

    public function down()
    {

    }
}
