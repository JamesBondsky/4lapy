<?php

namespace Sprint\Migration;

use Bitrix\Main\Application;
use Bitrix\Main\Db\SqlQueryException;

/**
 * Class BasketRulesResave20180704143523
 * @package Sprint\Migration
 */
class SypexGeo20181204143523 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = 'Создание таблиц sypex geo';

    /**
     *
     * @return bool
     * @throws \Bitrix\Main\Db\SqlQueryException
     */
    public function up(): bool
    {
        Application::getConnection()->query('DROP TABLE IF EXISTS `4lapy_sxgeo_cities`;');
        Application::getConnection()->query('
            CREATE TABLE `4lapy_sxgeo_cities` (
                `id` mediumint(8) unsigned NOT NULL,
                `region_id` mediumint(8) unsigned NOT NULL,
                `name_ru` varchar(128) NOT NULL,
                `name_en` varchar(128) NOT NULL,
                `lat` decimal(10,5) NOT NULL,
                `lon` decimal(10,5) NOT NULL,
                `okato` varchar(20) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT=\'Информация о городах для SxGeo 2.2\';'
        );

        Application::getConnection()->query('DROP TABLE IF EXISTS `4lapy_sxgeo_regions`;');
        Application::getConnection()->query('
            CREATE TABLE `4lapy_sxgeo_regions` (
                `id` mediumint(8) unsigned NOT NULL,
                `iso` varchar(7) NOT NULL,
                `country` char(2) NOT NULL,
                `name_ru` varchar(128) NOT NULL,
                `name_en` varchar(128) NOT NULL,
                `timezone` varchar(30) NOT NULL,
                `okato` char(4) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT=\'Информация о регионах для SxGeo 2.2\';'
        );

        Application::getConnection()->query('DROP TABLE IF EXISTS `4lapy_sxgeo_country`;');
        Application::getConnection()->query('
          CREATE TABLE `4lapy_sxgeo_country` (
              `id` tinyint(3) unsigned NOT NULL,
              `iso` char(2) NOT NULL,
              `continent` char(2) NOT NULL,
              `name_ru` varchar(128) NOT NULL,
              `name_en` varchar(128) NOT NULL,
              `lat` decimal(6,2) NOT NULL,
              `lon` decimal(6,2) NOT NULL,
              `timezone` varchar(30) NOT NULL,
              PRIMARY KEY (`id`)
          ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT=\'Информация о странах для SxGeo 2.2\';'
        );

        Application::getConnection()->query('
          ALTER TABLE `4lapy_sxgeo_cities` ADD  INDEX `region_id` (`region_id`);'
        );
        Application::getConnection()->query('
          ALTER TABLE `4lapy_sxgeo_regions` ADD  INDEX `country` (`country`);'
        );
        Application::getConnection()->query('
          ALTER TABLE `4lapy_sxgeo_country` ADD  INDEX `iso` (`iso`);'
        );

        $dataDirectory = '/local/php_interface/migration_sources/import_sypexgeo_tables/';
        $quer = 'LOAD DATA LOCAL INFILE "' . \FourPaws\App\Application::getAbsolutePath($dataDirectory) . 'city.tsv" INTO TABLE 4lapy_sxgeo_cities;';
        Application::getConnection()->query($quer);

        $quer = 'LOAD DATA LOCAL INFILE "' . \FourPaws\App\Application::getAbsolutePath($dataDirectory) . 'region.tsv" INTO TABLE 4lapy_sxgeo_regions';
        Application::getConnection()->query($quer);

        $quer = 'LOAD DATA LOCAL INFILE "' . \FourPaws\App\Application::getAbsolutePath($dataDirectory) . 'country.tsv" INTO TABLE 4lapy_sxgeo_country';
        Application::getConnection()->query($quer);

        return true;
    }

    public function down()
    {

        try {
            Application::getConnection()->query('DROP TABLE IF EXISTS `4lapy_sxgeo_cities`;');
            Application::getConnection()->query('DROP TABLE IF EXISTS `4lapy_sxgeo_regions`;');
            Application::getConnection()->query('DROP TABLE IF EXISTS `4lapy_sxgeo_country`;');
        } catch (SqlQueryException $e) {
        }

        return true;
    }
}
