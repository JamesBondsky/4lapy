<?php

namespace Sprint\Migration;


class MailTemplatesOrderSubscribeExpired20190617115606 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Добавляет почтовое событие и шаблон для уведомления об истечение срока поставки заказа по подписке.";

    public function up(){
        $helper = new HelperManager();

        $event = [
          "LID" => "ru",
          "EVENT_NAME" => "ORDER_SUBSCRIBE_EXPIRED",
          "NAME" => "Истекла дата создания заказа по подписке",
          "DESCRIPTION" => "",
          "SORT" => "150",
        ];

        $helper->Event()->addEventTypeIfNotExists($event['EVENT_NAME'], $event);

        $template = [
            "EVENT_NAME" => "ORDER_SUBSCRIBE_EXPIRED",
            "LID" => "s1",
            "ACTIVE" => "Y",
            "EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
            "EMAIL_TO" => "im@4lapy.ru",
            "SUBJECT" => "Подписка на доставку: не удалось сформировать заказ вовремя",
            "MESSAGE" => "При оформлении заказа по подписке возникли проблемы и заказа не может быть оформлен. \r\n
            \r\n
            ID: #ID#\r\n
            Пользователь: <a href=\"#USER_LINK#\">#USER_NAME#</a>\r\n
            Ожидаемая дата доставки: #DATE_DELIVERY#\r\n
            Способ доставки: #DELIVERY_TYPE#\r\n
            Место доставки: #DELIVERY_PLACE#
            ",
            "BODY_TYPE" => "html",
            "BCC" => "m.masterkov@articul.ru",
            "REPLY_TO" => "",
            "CC" => "",
            "IN_REPLY_TO" => "",
            "PRIORITY" => "",
            "FIELD1_NAME" => null,
            "FIELD1_VALUE" => null,
            "FIELD2_NAME" => null,
            "FIELD2_VALUE" => null,
            "SITE_TEMPLATE_ID" => "",
            "ADDITIONAL_FIELD" => [],
            "LANGUAGE_ID" => "ru",
            "EVENT_MESSAGE_TYPE_NAME" => "Истекла дата создания заказа по подписке",
            "EVENT_MESSAGE_TYPE_EVENT_NAME" => "ORDER_SUBSCRIBE_EXPIRED",
            "SITE_ID" => "s1",
        ];

        $helper->Event()->addEventMessageIfNotExists($template['EVENT_NAME'], $template);
    }

    public function down(){
        $helper = new HelperManager();
        return true;
    }

}
