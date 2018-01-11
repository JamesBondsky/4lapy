<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use FourPaws\Migrator\Factory;

class ClearMigrateTimestamp20180111131442 extends SprintMigrationBase
{
    
    protected $description = 'Clear migrate timestamp to force migrate references update';
    
    public function up()
    {
        foreach (Factory::AVAILABLE_TYPES as $type) {
            exec(sprintf('cd %s; ./bin/console migrate:clear %s',
                         \dirname($_SERVER['DOCUMENT_ROOT']),
                         $type));
            sprintf('cd %s; ./bin/console migrate:clear %s', \dirname($_SERVER['DOCUMENT_ROOT']), $type);
        }
    }
    
    public function down()
    {
        /**
         * Нет необходимости откатывать
         */
    }
}
