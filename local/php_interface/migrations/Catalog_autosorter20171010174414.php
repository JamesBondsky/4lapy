<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Application;

class Catalog_autosorter20171010174414 extends SprintMigrationBase
{

    protected $description = "Автосортировка товаров";

    public function up()
    {

        $query = <<<END
CREATE TABLE `4lp_elem_prop_cond` (
  `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `UF_ID` INT UNSIGNED NOT NULL COMMENT 'ID пользовательского свойства',
  `SECTION_ID` INT UNSIGNED NOT NULL COMMENT 'ID раздела, в котором используется кастомное свойство \"Условие для свойств элемента\"',
  `PROPERTY_ID` INT UNSIGNED NOT NULL COMMENT 'ID свойства элемента, которое надо проверить.',
  `PROPERTY_VALUE` VARCHAR(255) NULL COMMENT 'Значение свойства. Если null - символизирует незаполненное свойство.',
  PRIMARY KEY (`ID`),
  INDEX `main_working_index` USING BTREE (`UF_ID` ASC, `SECTION_ID` ASC, `PROPERTY_ID` ASC, `PROPERTY_VALUE` ASC ))
END;

        Application::getConnection()->queryExecute($query);

    }

}
