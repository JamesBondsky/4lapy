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
    protected $description = "Таблица с питомниками";

    private $sheltersTableName = '4lapy_animal_shelters';

    private $shelters = [
        [
            'NAME'        => 'Приют Последний шанс',
            'DESCRIPTION' => '',
            'CITY'        => 'Владимир'
        ],
        [
            'NAME'        => 'БФ "Дино"',
            'DESCRIPTION' => '',
            'CITY'        => 'Волгоград'
        ],
        [
            'NAME'        => 'БФ "Право на жизнь"',
            'DESCRIPTION' => '',
            'CITY'        => 'Воронеж'
        ],
        [
            'NAME'        => 'Ветгоспиталь Друзья',
            'DESCRIPTION' => '',
            'CITY'        => 'Воронеж'
        ],
        [
            'NAME'        => 'БФ "Буду Рядом"',
            'DESCRIPTION' => '',
            'CITY'        => 'Долгопрудный'
        ],
        [
            'NAME'        => 'Приют "Егорка"',
            'DESCRIPTION' => '',
            'CITY'        => 'Егорьевск'
        ],
        [
            'NAME'        => 'БФ "Просто Живи"',
            'DESCRIPTION' => '',
            'CITY'        => 'Жуковский'
        ],
        [
            'NAME'        => 'Приют "Проект Майский день"',
            'DESCRIPTION' => '',
            'CITY'        => 'Иваново'
        ],
        [
            'NAME'        => 'Приют Zoo 37',
            'DESCRIPTION' => '',
            'CITY'        => 'Иваново'
        ],
        [
            'NAME'        => 'Приют "Верные друзья"',
            'DESCRIPTION' => '',
            'CITY'        => 'Калуга'
        ],
        [
            'NAME'        => 'Приют Душа Бродяги',
            'DESCRIPTION' => '',
            'CITY'        => 'Калуга'
        ],
        [
            'NAME'        => 'Приют Право на жизнь',
            'DESCRIPTION' => '',
            'CITY'        => 'Кострома'
        ],
        [
            'NAME'        => 'Приют "Территория спасения"',
            'DESCRIPTION' => '',
            'CITY'        => 'Липецк'
        ],
        [
            'NAME'        => 'БФ "Husky Help"',
            'DESCRIPTION' => '',
            'CITY'        => 'Московская область'
        ],
        [
            'NAME'        => 'Кинологический центр помощи незрячим людям Собаки-поводыри',
            'DESCRIPTION' => '',
            'CITY'        => 'Московская область'
        ],
        [
            'NAME'        => 'Приют "Лесной приют"',
            'DESCRIPTION' => '',
            'CITY'        => 'Московская область'
        ],
        [
            'NAME'        => 'Приют Шереметьевский',
            'DESCRIPTION' => '',
            'CITY'        => 'Московская область'
        ],
        [
            'NAME'        => 'Общественная организация "Умка" (Дмитров)',
            'DESCRIPTION' => '',
            'CITY'        => 'Московская область, Дмитровский район'
        ],
        [
            'NAME'        => 'Приют "Верный Друг"',
            'DESCRIPTION' => '',
            'CITY'        => 'Московская область, Дубна'
        ],
        [
            'NAME'        => 'Приют "Территория добра"',
            'DESCRIPTION' => '',
            'CITY'        => 'Московская область, Клин'
        ],
        [
            'NAME'        => 'Приют "Зоодом"',
            'DESCRIPTION' => '',
            'CITY'        => 'Московская область, Королев'
        ],
        [
            'NAME'        => 'Муниципальный Ногинский приют для собак',
            'DESCRIPTION' => '',
            'CITY'        => 'Московская область, Ногинск'
        ],
        [
            'NAME'        => 'Приют "Умка"',
            'DESCRIPTION' => '',
            'CITY'        => 'Московская область, Одинцово'
        ],
        [
            'NAME'        => 'БФ "ЮНА"',
            'DESCRIPTION' => '',
            'CITY'        => 'Московская область, Подольск'
        ],
        [
            'NAME'        => 'Приют "Джимми"',
            'DESCRIPTION' => '',
            'CITY'        => 'Московская область, Подольский район'
        ],
        [
            'NAME'        => 'Волонтерское движение "Потеряшки"',
            'DESCRIPTION' => '',
            'CITY'        => 'Московская область, Сергиев-Посад'
        ],
        [
            'NAME'        => 'Приют "Зоозащита+"',
            'DESCRIPTION' => '',
            'CITY'        => 'Московская область, Серпухов'
        ],
        [
            'NAME'        => 'БФ "Собаки, которые любят"',
            'DESCRIPTION' => '',
            'CITY'        => 'Московская область, Щербинка'
        ],
        [
            'NAME'        => 'Волонтерская организация "Зоощит"',
            'DESCRIPTION' => '',
            'CITY'        => 'Московская область, Подольск'
        ],
        [
            'NAME'        => 'БФ "АЙКА"',
            'DESCRIPTION' => '',
            'CITY'        => 'Московская область, Рузский район'
        ],
        [
            'NAME'        => 'Волонтерская группа "КОТтедж КОТлетка"',
            'DESCRIPTION' => '',
            'CITY'        => 'Москва'
        ],
        [
            'NAME'        => 'Волонтерское движение "Преданный друг"',
            'DESCRIPTION' => '',
            'CITY'        => 'Москва'
        ],
        [
            'NAME'        => 'Приют "Искра"',
            'DESCRIPTION' => '',
            'CITY'        => 'Москва'
        ],
        [
            'NAME'        => 'Приют "Муркоша"',
            'DESCRIPTION' => '',
            'CITY'        => 'Москва'
        ],
        [
            'NAME'        => 'Приют "Мурлыка"',
            'DESCRIPTION' => '',
            'CITY'        => 'Москва'
        ],
        [
            'NAME'        => 'Приют "Домашний"',
            'DESCRIPTION' => '',
            'CITY'        => 'Москва'
        ],
        [
            'NAME'        => 'Приют "Бескудниково"',
            'DESCRIPTION' => '',
            'CITY'        => 'Москва, Лианозово'
        ],
        [
            'NAME'        => 'Приют "Преданное сердце"',
            'DESCRIPTION' => '',
            'CITY'        => 'Нижний Новгород'
        ],
        [
            'NAME'        => 'Волонтерская группа "Добрые руки"',
            'DESCRIPTION' => '',
            'CITY'        => 'Новомосковск'
        ],
        [
            'NAME'        => 'Приют "Новый ковчег"',
            'DESCRIPTION' => '',
            'CITY'        => 'Обнинск'
        ],
        [
            'NAME'        => 'МБУ "Городская служба по контролю за безнадзорными животными"',
            'DESCRIPTION' => '',
            'CITY'        => 'Рязань'
        ],
        [
            'NAME'        => 'Приют "Континент +"',
            'DESCRIPTION' => '',
            'CITY'        => 'Тула'
        ],
        [
            'NAME'        => 'БФ "Жизнь дана на добрые дела"',
            'DESCRIPTION' => '',
            'CITY'        => 'Ярославль'
        ],
        [
            'NAME'        => 'БФ "Зоо Забота"',
            'DESCRIPTION' => '',
            'CITY'        => 'Ярославль'
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
                    `city` VARCHAR (255) NOT NULL,
                    PRIMARY KEY (`id`)
                );'
            );

            foreach ($this->shelters as $shelter) {
                Application::getConnection()->query('INSERT INTO `' . $this->sheltersTableName . '` (name, description, city) VALUES (\'' . implode('\',\'', $shelter) . '\');');
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
