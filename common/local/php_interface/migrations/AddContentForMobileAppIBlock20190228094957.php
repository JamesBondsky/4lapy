<?php

namespace Sprint\Migration;


use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class AddContentForMobileAppIBlock20190228094957 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    const SITE_ID = 's1';
    protected $description = "Создание инфоблока \"Статический контент\" для мобильного приложения";
    /** @var HelperManager */
    protected $helper;

    public function up(){
        $this->helper = new HelperManager();

        $iblockId = $this->addIblock();

        // доставка
        $this->addDelivery($iblockId);

        // инфа о бонусных картах
        $this->addBonusCardInfo($iblockId);

        // приобрести бонусную карту
        $this->addObtainBonusCard($iblockId);

        // контакты
        $this->addContacts($iblockId);

        // о компании
        $this->addAbout($iblockId);

    }

    public function down(){
        // sorry, no down
    }

    private function addIblock()
    {
        return $this->helper->Iblock()->addIblockIfNotExists([
            'NAME'           => 'Контент для моб. приложения',
            'CODE'           => IblockCode::MOBILE_APP_CONTENT,
            'IBLOCK_TYPE_ID' => IblockType::PUBLICATION,
            'SITE_ID'        => [static::SITE_ID],
        ]);
    }

    private function addDelivery($iblockId)
    {
        $this->helper->Iblock()->addElementIfNotExists(
            $iblockId,
            [
                'NAME'              => '+7 (4852) 67-36-00',
                'CODE'              => 'delivery',
                'XML_ID'            => '1920618',
                'DETAIL_TEXT'   => htmlspecialcharsback("&lt;b&gt;Для жителей г. Москва:&lt;/b&gt;\r\n&lt;br&gt;При оформлении заказа до 14:00, привезем товар после 16.00 того же дня.&lt;br&gt;При оформлении заказа после 14:00, доставим заказ на следующий день.&lt;br&gt;&lt;br&gt;\n"),
                'PREVIEW_TEXT'    => "Для жителей г. Москва:\r\nПри оформлении заказа до 14:00, привезем товар после 16.00 того же дня.При оформлении заказа после 14:00, доставим заказ на следующий день.",
                'PREVIEW_TEXT_TYPE' => 'html',
                'DETAIL_TEXT_TYPE' => 'html',
            ]
        );
    }

    private function addBonusCardInfo($iblockId)
    {
        $this->helper->Iblock()->addElementIfNotExists(
            $iblockId,
            [
                'NAME'              => 'Информация по бонусной карте',
                'CODE'              => 'bonus_card_info',
                'XML_ID'            => '1920618',
                'DETAIL_TEXT'   => htmlspecialcharsback("&lt;p style=&quot;text-align: center;&quot;&gt;\r\n &lt;b&gt;&lt;span style=&quot;color: #000000;&quot;&gt;Бонусная программа Четыре Лапы - просто и понятно!&lt;/span&gt;&lt;/b&gt;\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: center;&quot;&gt;\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: center;&quot;&gt;\r\n\t 1 бонус = 1 рубль\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: left;&quot;&gt;\r\n\t Размер начисления бонусов зависит от суммы накоплений на карте:\r\n&lt;/p&gt;\r\n От 500 рублей - 3% от стоимости покупки&lt;br&gt;\r\n От 9000 рублей - 4% от стоимости покупки&lt;br&gt;\r\n От 19000 рублей - 5% от стоимости покупки&lt;br&gt;\r\n От 39000 рублей - 6% от стоимости покупки&lt;br&gt;\r\n От 59000 рублей - 7% от стоимости покупки&lt;br&gt;&lt;br&gt;&lt;p&gt;\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: left;&quot;&gt;\r\n\t * Бонусы можно тратить на любые товары, включая акционные. \r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: left;&quot;&gt;\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: left;&quot;&gt;\r\n\t ** Бонусами можно оплатить до 90% покупки. \r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: left;&quot;&gt;\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: left;&quot;&gt;\r\n\t *** Персональные бонусы и скидки для участников программы.\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: center;&quot;&gt;\r\n &lt;br&gt;&lt;/p&gt;\r\n&lt;p&gt;\r\n&lt;/p&gt;\n"),
                'PREVIEW_TEXT_TYPE' => 'html',
                'DETAIL_TEXT_TYPE' => 'html',
            ]
        );
    }

    private function addObtainBonusCard($iblockId)
    {
        $this->helper->Iblock()->addElementIfNotExists(
            $iblockId,
            [
                'NAME'              => 'Как получить карту',
                'CODE'              => 'obtain_bonus_card',
                'XML_ID'            => '1920618',
                'DETAIL_TEXT'   => htmlspecialcharsback("&lt;div&gt;\r\n&lt;/div&gt;\r\n &lt;br&gt;&lt;p style=&quot;text-align: center;&quot;&gt;\r\n &lt;b&gt;&lt;span style=&quot;color: #000000;&quot;&gt;Больше покупок - больше бонусов!&lt;/span&gt;&lt;/b&gt;\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: center;&quot;&gt;\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: center;&quot;&gt;\r\n &lt;b&gt;&lt;span style=&quot;color: #f16522;&quot;&gt;1 бонус = 1 рубль&lt;/span&gt;&lt;/b&gt;\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: left;&quot;&gt;\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n &lt;span style=&quot;font-size: 24pt;&quot;&gt;Воспользуйтесь всеми преимуществами нашей бонусной программы. Для того, чтобы начать копить бонусы, достаточно совершить единовременную покупку в Четыре Лапы на сумму от &lt;b&gt;&lt;span style=&quot;color: #f16522;&quot;&gt;500 рублей&lt;/span&gt;&lt;/b&gt; и зарегистрировать карту в магазине или на нашем сайте.&lt;/span&gt;\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n &lt;span style=&quot;font-size: 24pt;&quot;&gt;Карта дает право на получение бонусов с любой покупки, за исключением акционных товаров. &lt;/span&gt;\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n &lt;span style=&quot;font-size: 24pt;&quot;&gt;Делайте заказы, копите бонусы и используйте их для оплаты до 90% следующих покупок.&lt;/span&gt;\r\n&lt;/p&gt;\r\n&lt;div&gt;\r\n &lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;/div&gt;\r\n&lt;p style=&quot;text-align: left;&quot;&gt;\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: center;&quot;&gt;\r\n &lt;br&gt;&lt;/p&gt;\r\n&lt;p&gt;\r\n&lt;/p&gt;\n"),
                'PREVIEW_TEXT_TYPE' => 'html',
                'DETAIL_TEXT_TYPE' => 'html',
            ]
        );
    }

    private function addContacts($iblockId)
    {
        $this->helper->Iblock()->addElementIfNotExists(
            $iblockId,
            [
                'NAME'              => 'Контакты',
                'CODE'              => 'contacts',
                'XML_ID'            => '1919979',
                'DETAIL_TEXT'       => htmlspecialcharsback("&lt;h3&gt;Маркетинг и реклама:&lt;/h3&gt;\r\n  &lt;p&gt;   E-mail: &lt;a href=&quot;mailto:marketing@4lapy.ru&quot;&gt;marketing@4lapy.ru &lt;/a&gt;  &lt;/p&gt;\r\n  &lt;br&gt;&lt;h3&gt;Поставщикам:&lt;/h3&gt;\r\n  &lt;p&gt; \tE-mail: &lt;a href=&quot;mailto:SPastukhov@4lapy.ru&quot;&gt;SPastukhov@4lapy.ru &lt;/a&gt;  &lt;/p&gt;\r\n  &lt;br&gt;&lt;h3&gt;Арендодателям:&lt;/h3&gt;\r\n  &lt;p&gt;E-mail: &lt;a href=&quot;mailto:rent@4lapy.ru&quot;&gt;rent@4lapy.ru&lt;/a&gt;&lt;/p&gt;\r\n  &lt;br&gt;&lt;h3&gt;ТЕЛЕФОН ЦЕНТРАЛЬНОГО ОФИСА:&lt;/h3&gt; \r\n  &lt;p&gt;+7 (495) 221-72-25&lt;/p&gt;\r\n  &lt;br&gt;&lt;h3&gt;ИНФОРМАЦИЯ О ЮР.ЛИЦЕ&lt;/h3&gt;\r\n  &lt;p&gt;ООО &quot;ЗУМ+&quot;  &lt;/p&gt;\r\n  &lt;p&gt;ОГРН: 1095040007370  &lt;/p&gt;\r\n  &lt;p&gt;Юридический адрес: 140180, Московская область, г. Жуковский, ул. Мичурина д.9 &lt;/p&gt;\n"),
                'PREVIEW_TEXT_TYPE' => 'html',
                'DETAIL_TEXT_TYPE' => 'html',
            ]
        );
    }

    private function addAbout($iblockId)
    {
        $this->helper->Iblock()->addElementIfNotExists(
            $iblockId,
            [
                'NAME'              => 'О компании',
                'CODE'              => 'about',
                'XML_ID'            => '1919980',
                'DETAIL_TEXT'       => htmlspecialcharsback("&lt;h1&gt;О компании&lt;/h1&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n\tКомпания &quot;Четыре Лапы&quot; более 20 лет заботится о домашних питомцах. Сегодня мы занимаем одно из лидирующих положений на рынке, создавая лучшее предложение для питомцев и их хозяев, в более чем 150 магазинах сети в 15 регионах России. Наша миссия - заботиться о полноценной жизни питомцев. Наш долг - поддерживать Вас и Ваших домашних любимцев, чтобы их жизнь была здоровой, долгой, радостной и гармоничной.\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n &lt;br&gt;&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n\t Мы сотрудничаем с поставщиками, контролируем качество товаров, чтобы наше предложение всегда поддерживалось на неизменно высоком уровне.\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n &lt;br&gt;&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n\t В магазинах &quot;Четыре Лапы&quot; работают профессионалы, любящие и знающие свое дело, которые всегда готовы помочь советом.\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n &lt;br&gt;&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n\t Приходите и задайте свой вопрос нашему ветеринарному врачу, получите консультацию по кормлению и профилактике здоровья питомца. Наши специалисты в магазине имеют высокую квалификацию, Вы всегда можете рассчитывать на их помощь в подборе того, что нужно Вашему любимцу. С нами каждый человек обретает уверенность, что его домашний питомец будет обеспечен всем необходимым для счастливой и здоровой жизни.\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n &lt;br&gt;&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n\t С нами удобно! Вы можете заказать товар удобным для Вас способом: в магазине, по телефону или через Интернет; получить его в магазине или с доставкой на дом. Для наших постоянных покупателей в магазинах действует бонусная программа, позволяющая совершать покупки более выгодно. Каждый месяц мы проводим акции: дарим скидки, бонусы, подарки.\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n &lt;br&gt;&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n\t Мы гордимся тем, что делаем. Наша работа помогает сделать этот мир лучше, потому что общение с домашними питомцами приносит радостные эмоции и ощущение полноты жизни.\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n &lt;br&gt;&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n\t Выберите свой зоомагазин «Четыре Лапы». Мы всегда рады видеть Вас!\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n&lt;/p&gt;\n"),
                'PREVIEW_TEXT_TYPE' => 'html',
                'DETAIL_TEXT_TYPE' => 'html',
            ]
        );
    }

}
