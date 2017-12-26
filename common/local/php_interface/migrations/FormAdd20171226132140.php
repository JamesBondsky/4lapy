<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

use Bitrix\Main\Config\Option;

class FormAdd20171226132140 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    protected $description = 'Настройка форм';
    
    public function up()
    {
        $helper = new HelperManager();
        /** @todo create form */
        
        $helper->Event()->addEventTypeIfNotExists(
            'FORM_FILLING_feedback',
            [
                'LID'         => 'ru',
                'EVENT_NAME'  => 'FORM_FILLING_feedback',
                'NAME'        => 'Заполнена web-форма "feedback"',
                'DESCRIPTION' => '#RS_FORM_ID# - ID формы
#RS_FORM_NAME# - Имя формы
#RS_FORM_SID# - SID формы
#RS_RESULT_ID# - ID результата
#RS_DATE_CREATE# - Дата заполнения формы
#RS_USER_ID# - ID пользователя
#RS_USER_EMAIL# - EMail пользователя
#RS_USER_NAME# - Фамилия, имя пользователя
#RS_USER_AUTH# - Пользователь был авторизован?
#RS_STAT_GUEST_ID# - ID посетителя
#RS_STAT_SESSION_ID# - ID сессии
#name# - Имя
#name_RAW# - Имя (оригинальное значение)
#email# - Эл. почта
#email_RAW# - Эл. почта (оригинальное значение)
#phone# - Телефон
#phone_RAW# - Телефон (оригинальное значение)
#theme# - Тема
#theme_RAW# - Тема (оригинальное значение)
#message# - Сообщение
#message_RAW# - Сообщение (оригинальное значение)
#file# - Файл
#file_RAW# - Файл (оригинальное значение)
',
            ]
        );
        
        $helper->Event()->addEventMessageIfNotExists(
            'FORM_FILLING_feedback',
            [
                'ACTIVE'     => 'Y',
                'LID'        => SITE_ID,
                'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
                'EMAIL_TO'   => '#DEFAULT_EMAIL_FROM#',
                'BCC'        => '',
                'SUBJECT'    => '#SERVER_NAME#: заполнена web-форма [#RS_FORM_ID#] #RS_FORM_NAME#',
                'BODY_TYPE'  => 'text',
                'MESSAGE'    => '#SERVER_NAME#

Заполнена web-форма: [#RS_FORM_ID#] #RS_FORM_NAME#
-------------------------------------------------------

Дата - #RS_DATE_CREATE#
Результат - #RS_RESULT_ID#
Пользователь - [#RS_USER_ID#] #RS_USER_NAME# #RS_USER_AUTH#
Посетитель - #RS_STAT_GUEST_ID#
Сессия - #RS_STAT_SESSION_ID#


Имя
*******************************
#name#

Эл. почта
*******************************
#email#

Телефон
*******************************
#phone#

Тема
*******************************
#theme#

Сообщение
*******************************
#message#

Файл
*******************************
#file#


Для просмотра воспользуйтесь ссылкой:
http://#SERVER_NAME#/bitrix/admin/form_result_view.php?lang=ru&WEB_FORM_ID=#RS_FORM_ID#&RESULT_ID=#RS_RESULT_ID#

-------------------------------------------------------
Письмо сгенерировано автоматически.',
            ]
        );
        
        $helper->Event()->addEventTypeIfNotExists(
            'FORM_FILLING_callback',
            [
                'LID'         => 'ru',
                'EVENT_NAME'  => 'FORM_FILLING_callback',
                'NAME'        => 'Заполнена web-форма "callback"',
                'DESCRIPTION' => '#RS_FORM_ID# - ID формы
#RS_FORM_NAME# - Имя формы
#RS_FORM_SID# - SID формы
#RS_RESULT_ID# - ID результата
#RS_DATE_CREATE# - Дата заполнения формы
#RS_USER_ID# - ID пользователя
#RS_USER_EMAIL# - EMail пользователя
#RS_USER_NAME# - Фамилия, имя пользователя
#RS_USER_AUTH# - Пользователь был авторизован?
#RS_STAT_GUEST_ID# - ID посетителя
#RS_STAT_SESSION_ID# - ID сессии
#name# - Имя
#name_RAW# - Имя (оригинальное значение)
#phone# - Телефон
#phone_RAW# - Телефон (оригинальное значение)
#time_call# - Время звонка
#time_call_RAW# - Время звонка (оригинальное значение)
',
            ]
        );
        
        $helper->Event()->addEventMessageIfNotExists(
            'FORM_FILLING_callback',
            [
                'ACTIVE'     => 'Y',
                'LID'        => SITE_ID,
                'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
                'EMAIL_TO'   => '#DEFAULT_EMAIL_FROM#',
                'BCC'        => '',
                'SUBJECT'    => '#SERVER_NAME#: заполнена web-форма [#RS_FORM_ID#] #RS_FORM_NAME#',
                'BODY_TYPE'  => 'text',
                'MESSAGE'    => '#SERVER_NAME#

Заполнена web-форма: [#RS_FORM_ID#] #RS_FORM_NAME#
-------------------------------------------------------

Дата - #RS_DATE_CREATE#
Результат - #RS_RESULT_ID#
Пользователь - [#RS_USER_ID#] #RS_USER_NAME# #RS_USER_AUTH#
Посетитель - #RS_STAT_GUEST_ID#
Сессия - #RS_STAT_SESSION_ID#


Имя
*******************************
#name#

Телефон
*******************************
#phone#

Время звонка
*******************************
#time_call#


Для просмотра воспользуйтесь ссылкой:
http://#SERVER_NAME#/bitrix/admin/form_result_view.php?lang=ru&WEB_FORM_ID=#RS_FORM_ID#&RESULT_ID=#RS_RESULT_ID#

-------------------------------------------------------
Письмо сгенерировано автоматически.',
            ]
        );
        
        /** @noinspection PhpUnhandledExceptionInspection */
        Option::set('form', 'SIMPLE', 'N');
    }
    
    public function down()
    {
    }
}
