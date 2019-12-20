<?php

namespace Sprint\Migration;


use Bitrix\Main\Application;

class BasketsDiscountOfferTable20191218101902 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Создает таблицу с кастомными номерами пользовательских корзин для акции";

    public function up(){
        $helper = new HelperManager();

        Application::getConnection()->query('create table `4lapy_baskets_discount_offer`
            (
                id int not null auto_increment,
                fUserId int null,
                userId int null,
                date_insert datetime not null,
                date_update datetime not null,
                order_created bool default 0 not null,
                promoCode varchar(30) null,
                isFromMobile bool null,
                primary key (id)
            );
        ');
        Application::getConnection()->query('create unique index `4lapy_baskets_discount_offer_promoCode_uindex`
	        on `4lapy_baskets_discount_offer` (promoCode);');
    }

    public function down(){
        $helper = new HelperManager();

        Application::getConnection()->query('drop table if exists `4lapy_baskets_discount_offer`;');
    }

}
