<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Application;

/**
 * Class BasketAutoIncrement20180404152308
 * @package Sprint\Migration
 */
class BasketAutoIncrement20180404152308 extends SprintMigrationBase
{

    protected $description =
        'Устанавливает автоинкремент b_sale_basket на 500, чтобы виртуальные элементы корзины не '
        . 'пересекались с настоящими при отправке корзины до её оформления в манзану.';

    public const NEW_AUTO_INCREMENT = 500;

    /**
     *
     *
     * @throws \Bitrix\Main\Db\SqlQueryException
     *
     * @return void
     */
    public function up(): void
    {
        $DBName = Application::getConnection()->getDatabase();
        $res = Application::getConnection()->query(
            'SHOW TABLE STATUS FROM `' . $DBName . '` WHERE `name` LIKE \'b_sale_basket\';'
        );
        if (
            (null !== $autoIncrement = (int)$res->fetch()['Auto_increment'])
            && $autoIncrement < self::NEW_AUTO_INCREMENT
        ) {
            /** @noinspection UnknownInspectionInspection */
            /** @noinspection SqlNoDataSourceInspection */
            Application::getConnection()->query(
                'ALTER TABLE `' . $DBName . '`.`b_sale_basket` AUTO_INCREMENT=' . self::NEW_AUTO_INCREMENT . ';'
            );
        }
    }

    /**
     *
     *
     * @return bool|void
     */
    public function down()
    {
        /**
         * не требуется
         */
    }

}
