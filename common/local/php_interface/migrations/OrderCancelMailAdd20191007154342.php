<?php

namespace Sprint\Migration;

use CEventMessage;
use CEventType;

class OrderCancelMailAdd20191007154342 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    protected $description = 'Добавление нового почтового события на отмену заказа';

    protected const EVENT_NAME = 'ADMIN_EMAIL_AFTER_ORDER_CANCEL';

    /**
     * @return bool
     */
    public function up(): bool
    {
        CEventType::add([
            'EVENT_NAME' => self::EVENT_NAME,
            'NAME' => 'Письмо администратору при отмене заказа',
            'LID' => 'ru',
            'DESCRIPTION' => '
                #ORDER_NUMBER# - Номер заказа
            '
        ]);

        $arr = [];
        $arr['ACTIVE'] = 'Y';
        $arr['EVENT_NAME'] = self::EVENT_NAME;
        $arr['LID'] = ['s1'];
        $arr['EMAIL_FROM'] = '#DEFAULT_EMAIL_FROM#';
        $arr['EMAIL_TO'] = 'im@4lapy.ru';
        $arr['BCC'] = '';
        $arr['SUBJECT'] = 'Отмена заказа №#ORDER_NUMBER#';
        $arr['BODY_TYPE'] = 'text';
        $arr['MESSAGE'] = 'Заказ №#ORDER_NUMBER# был отменен';

        (new \CEventMessage)->Add($arr);

        return true;
    }

    /**
     * @return bool
     */
    public function down(): bool
    {
        $rsEvent = CEventType::GetList(['EVENT_NAME' => self::EVENT_NAME]);
        if ($arEvent = $rsEvent->Fetch()) {
            CEventType::Delete($arEvent['ID']);
        }

        return true;
    }
}
