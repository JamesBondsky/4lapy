<?php

namespace Sprint\Migration;


class MailTemplateSystemError20190808121620 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Добавляет почтовый шаблон для уведомлений об ошибке генерации / импорта расписаний доставки";

    public function up(){
        $helper = new HelperManager();

        $event = [
            "LID" => "ru",
            "EVENT_NAME" => "SystemErrorMessage",
            "NAME" => "Сообщение об ошибке",
            "DESCRIPTION" => "",
            "SORT" => "150",
        ];

        $helper->Event()->addEventTypeIfNotExists($event['EVENT_NAME'], $event);

        $template = [
            "EVENT_NAME" => "SystemErrorMessage",
            "LID" => "s1",
            "ACTIVE" => "Y",
            "EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
            "EMAIL_TO" => "monitoring@articul.ru",
            "SUBJECT" => "#THEME#",
            "MESSAGE" => "#MESSAGE#",
            "BODY_TYPE" => "html",
            "BCC" => "m.masterkov@articul.ru",
            "REPLY_TO" => "",
            "CC" => "m.balezin@articul.ru",
            "IN_REPLY_TO" => "",
            "PRIORITY" => "",
            "FIELD1_NAME" => null,
            "FIELD1_VALUE" => null,
            "FIELD2_NAME" => null,
            "FIELD2_VALUE" => null,
            "SITE_TEMPLATE_ID" => "",
            "ADDITIONAL_FIELD" => [],
            "LANGUAGE_ID" => "",
            "EVENT_MESSAGE_TYPE_NAME" => "Сообщение об ошибке",
            "EVENT_MESSAGE_TYPE_EVENT_NAME" => "SystemErrorMessage",
            "SITE_ID" => "s1"
        ];

        $helper->Event()->addEventMessageIfNotExists($template['EVENT_NAME'], $template);

    }

    public function down(){
        $helper = new HelperManager();
        return true;
    }

}
