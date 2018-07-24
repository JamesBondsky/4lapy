<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Application;

class UserFieldIndex20180723130000 extends SprintMigrationBase
{
    protected $description = 'Изменение типа поля b_uts_user.UF_DISCOUNT_CARD и добавление индекса';
    
    public function up()
    {
        $connection = Application::getConnection();
        $connection->queryExecute('ALTER TABLE b_uts_user MODIFY UF_DISCOUNT_CARD VARCHAR(255)');
        $connection->queryExecute('CREATE INDEX ADV_IX_UF_DISCOUNT_CARD ON b_uts_user (UF_DISCOUNT_CARD)');

        return true;
    }
    
    public function down()
    {
        $connection = Application::getConnection();
        $connection->queryExecute('DROP INDEX ADV_IX_UF_DISCOUNT_CARD ON b_uts_user');
        $connection->queryExecute('ALTER TABLE b_uts_user MODIFY UF_DISCOUNT_CARD TEXT;');

        return true;
    }
}
