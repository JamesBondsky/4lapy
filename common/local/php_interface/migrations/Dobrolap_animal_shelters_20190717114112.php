<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Sale\Delivery\DeliveryLocationTable;
use Bitrix\Sale\Delivery\Services\Configurable;
use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\Delivery\Services\Table as ServicesTable;
use Bitrix\Sale\Internals\ServiceRestrictionTable;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\DeliveryBundle\Handler\InnerDeliveryHandler;
use FourPaws\DeliveryBundle\Handler\InnerPickupHandler;

class Dobrolap_animal_shelters_20190717114112 extends SprintMigrationBase
{
    private $sheltersTableName = '4lapy_animal_shelters';

    private $shelters = [
        [
            'NAME'        => 'Приют Последний шанс',
            'DESCRIPTION' => 'гг',
            'ADDRESS'     => 'вп',
            'CITY'        => 'Владимир',
            'LONG'        => '1223.123123',
            'LAT'         => '3214.456456'
        ],
        [
            'NAME'        => 'БФ "Дино"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'Волгоград',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'БФ "Право на жизнь"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'Воронеж',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Ветгоспиталь Друзья',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'Воронеж',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'БФ "Буду Рядом"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'Долгопрудный',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Приют "Егорка"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'Егорьевск',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'БФ "Просто Живи"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'Жуковский',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Приют "Проект Майский день"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'Иваново',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Приют Zoo 37',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'Иваново',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Приют "Верные друзья"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'Калуга',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Приют Душа Бродяги',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'Калуга',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Приют Право на жизнь',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'Кострома',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Приют "Территория спасения"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'Липецк',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'БФ "Husky Help"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'МО',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Кинологический центр помощи незрячим людям Собаки-поводыри',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'МО',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Приют "Лесной приют"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'МО',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Приют Шереметьевский',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'МО',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Общественная организация "Умка" (Дмитров)',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'МО, Дмитровский район',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Приют "Верный Друг"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'МО, Дубна',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Приют "Территория добра"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'МО, Клин',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Приют "Зоодом"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'МО, Королев',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Муниципальный Ногинский приют для собак',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'МО, Ногинск',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Приют "Умка"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'МО, Одинцово',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'БФ "ЮНА"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'МО, Подольск',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Приют "Джимми"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'МО, Подольский район',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Волонтерское движение "Потеряшки"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'МО, Сергиев-Посад',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Приют "Зоозащита+"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'МО, Серпухов',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'БФ "Собаки, которые любят"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'МО, Щербинка',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Волонтерская организация "Зоощит"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'МО,Подольск',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'БФ "АЙКА"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'МО,Рузский район',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Волонтерская группа "КОТтедж КОТлетка"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'Москва',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Волонтерское движение "Преданный друг"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'Москва',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Приют "Искра"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'Москва',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Приют "Муркоша"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'Москва',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Приют "Мурлыка"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'Москва',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Приют "Домашний"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'Москва',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Приют "Бескудниково"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'Москва, Лианозово',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Приют "Преданное сердце"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'Нижний Новгород',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Волонтерская группа "Добрые руки"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'Новомосковск',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Приют "Новый ковчег"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'Обнинск',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'МБУ "Городская служба по контролю за безнадзорными животными"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'Рязань',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'Приют "Континент +"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'Тула',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'БФ "Жизнь дана на добрые дела"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'Ярославль',
            'LONG'        => '',
            'LAT'         => ''
        ],
        [
            'NAME'        => 'БФ "Зоо Забота"',
            'DESCRIPTION' => '',
            'ADDRESS'     => '',
            'CITY'        => 'Ярославль',
            'LONG'        => '',
            'LAT'         => ''
        ],
    ];

    public function up()
    {
        try {
            Application::getConnection()->query('DROP TABLE IF EXISTS `' . $this->sheltersTableName . '`;');
            Application::getConnection()->query('
                CREATE TABLE `' . $this->sheltersTableName . '` (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `name` VARCHAR (255) NOT NULL,
                    `description` VARCHAR (255) NOT NULL,
                    `address` VARCHAR (255) NOT NULL,
                    `city` VARCHAR (255) NOT NULL,
                    `latitude` FLOAT NOT NULL,
                    `longitude` FLOAT NOT NULL,
                    PRIMARY KEY (`id`)
                );'
            );

            foreach ($this->shelters as $shelter) {
                Application::getConnection()->query('INSERT INTO `' . $this->sheltersTableName . '` (name, description, address, city, latitude, longitude) VALUES (\'' . implode('\',\'', $shelter) . '\');');
            }
        } catch (SqlQueryException $e) {
            echo $e->getMessage();
            return false;
        }

        return true;
    }

    public function down()
    {
        try {
            Application::getConnection()->query('DROP TABLE IF EXISTS `' . $this->sheltersTableName . '`;');
            return true;
        } catch (SqlQueryException $e) {
            echo $e->getMessage();
            return false;
        }

        return true;
    }
}
