<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Application;

class Price20171208171026 extends SprintMigrationBase
{

    protected $description = "Создание своей таблицы под цены торговых предложений.";

    /**
     * @return bool|void
     * @throws \Bitrix\Main\Db\SqlQueryException
     */
    public function up()
    {
        $query = <<<END
CREATE TABLE IF NOT EXISTS `4lp_catalog_price` (
	`ID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`ELEMENT_ID` INT UNSIGNED NOT NULL COMMENT 'Битриксовый ID торгового предложения или любого другого элемента инфоблока',
	`REGION_ID` CHAR(6) NOT NULL COMMENT 'Символьный код региона: IM01 или IR01',
	`PRICE` DECIMAL(18,2) NOT NULL COMMENT 'Цена без скидки',
	`CURRENCY` char(3) NOT NULL DEFAULT 'RUB' COMMENT 'Валюта',
	PRIMARY KEY (`ID`),
	KEY `main_working_index` USING BTREE (`ELEMENT_ID`, `REGION_ID`, `PRICE`),
	UNIQUE INDEX `element-region` (`ELEMENT_ID`, `REGION_ID`))
END;
;
        Application::getConnection()->queryExecute($query);
    }

    public function down()
    {

    }

}
