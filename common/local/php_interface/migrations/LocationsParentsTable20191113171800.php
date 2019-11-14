<?php

namespace Sprint\Migration;

use Bitrix\Main\Application;
use Bitrix\Main\Db\SqlQueryException;

class LocationsParentsTable20191113171800 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    protected $description = 'Таблица для получения новых номеров заказов';

    public function up()
    {
        //$helper = new HelperManager();
        try {
            Application::getConnection()->query('DROP TABLE IF EXISTS `4lapy_locations_parents`;');
            Application::getConnection()->query('create table `4lapy_locations_parents`
                (
                    ID int not null,
                    PARENTS json not null,
                    constraint `4lapy_locations_parents_pk`
                        primary key (ID)
                );'
            );

            return true;
        } catch (SqlQueryException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function down()
    {
        //$helper = new HelperManager();
        try {
            Application::getConnection()->query('DROP TABLE IF EXISTS `4lapy_locations_parents`;');
            return true;
        } catch (SqlQueryException $e) {
            echo $e->getMessage();
            return false;
        }
    }
}
