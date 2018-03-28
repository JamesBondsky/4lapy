<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use FourPaws\Migrator\Client\Store;
use FourPaws\Migrator\Entity\Store as StoreEntity;
use FourPaws\Migrator\Provider\StoreLocation;

class StoreImport20180326234344 extends SprintMigrationBase
{
    protected /** @noinspection ClassOverridesFieldOfSuperClassInspection */
        $description = 'Реимпорт складов';

    public function up()
    {
        $locationMigrator = new Store(
            new StoreLocation(new StoreEntity(Store::ENTITY_NAME)),
            ['limit' => 1000, 'force' => true]
        );
        $locationMigrator->save();
    }

    public function down()
    {
        /**
         * Нет необходимости
         */
    }

}
