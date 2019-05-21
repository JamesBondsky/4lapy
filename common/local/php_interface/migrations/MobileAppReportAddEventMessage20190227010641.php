<?php

namespace Sprint\Migration;


class MobileAppReportAddEventMessage20190227010641 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Добавление почтового шаблона для отправки сообщений об ошибках в мобильном приложении";

    public function up(){
        $helper = new HelperManager();

        $siteId = 's1';
        $lang = 'ru';

        $helper->Event()->addEventTypeIfNotExists(
            'MobileAppReportBug',
            [
                'LID'         => $lang,
                'NAME'        => 'Сообщение об ошибке в моб.приложении',
                'DESCRIPTION' => "#USER_EMAIL# - email пользователя
#USER_PHONE# - телефон пользователя
#USER_FIRST_NAME# - имя пользователя
#USER_LAST_NAME# - фамилия пользователя
#TEXT_REPORT# - Описание ошибки
#DEVICE_INFO# - Информация об устройстве",
            ]
        );

        $helper->Event()->addEventMessageIfNotExists(
            'MobileAppReportBug',
            [
                'LID'         => $siteId,
                'LANGUAGE_ID' => $lang,
                'EMAIL_TO'    => 'welcome@4lapy.ru',
                'BCC'         => 'mporotikov@4lapy.ru',
                'SUBJECT'     => 'МП. Сообщить об ошибке.',
                'MESSAGE'     => 'Описание: #TEXT_REPORT#

Характеристики устройства: #DEVICE_INFO#

Email: #USER_EMAIL#
Телефон: 7#USER_PHONE#
Имя: #USER_FIRST_NAME#
Фамилия: #USER_LAST_NAME#',
            ]
        );

    }

    public function down(){
        // no down
    }

}
