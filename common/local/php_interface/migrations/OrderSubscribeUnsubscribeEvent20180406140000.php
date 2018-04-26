<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;

class OrderSubscribeUnsubscribeEvent20180406140000 extends SprintMigrationBase
{
    const EVENT_TYPE = '4PAWS_ORDER_SUBSCRIBE_AUTO_UNSUBSCRIBE';
    protected $description = 'Добавление почтового типа 4PAWS_ORDER_SUBSCRIBE_AUTO_UNSUBSCRIBE и почтового шаблона автоматической отписки на доставку';

    public function up()
    {
        $helper = new HelperManager();
        $langId = 'ru';
        $siteId = 's1';

        $nl = "\n";

        $description = '';
        $description .= '#ORDER_ID# - ID заказа'.$nl;
        $description .= '#ACCOUNT_NUMBER# - Номер заказа'.$nl;
        $description .= '#SUBSCRIBE_ID# - ID подписки'.$nl;
        $description .= '#SUBSCRIBE_DATE# - Дата подписки'.$nl;
        $description .= '#USER_ID# - ID пользователя'.$nl;
        $description .= '#USER_NAME# - Имя пользователя'.$nl;
        $description .= '#USER_FULL_NAME# - Полное имя пользователя'.$nl;
        $description .= '#USER_EMAIL# - Имя пользователя'.$nl;
        $id = $helper->Event()->addEventTypeIfNotExists(
            static::EVENT_TYPE,
            [
                'LID' => $langId,
                'NAME' => 'Автоматическая отмена подписки на доставку',
                'DESCRIPTION' => $description,
            ]
        );
        if ($id) {
            $message = '';
            $message .= 'Подписка на доставку отменена по причине отсутствия товаров в ассортименте магазина.'.$nl;
            $message .= 'Дата подписки: #SUBSCRIBE_DATE#.'.$nl;
            $message .= 'Номер подписанного заказа: #ACCOUNT_NUMBER#.'.$nl;
            $helper->Event()->addEventMessageIfNotExists(
                static::EVENT_TYPE,
                [
                    'LID' => $siteId,
                    'EMAIL_TO' => '#USER_EMAIL#',
                    'SUBJECT' => 'Автоматически отменена подписка на доставку',
                    'BODY_TYPE' => 'text',
                    'MESSAGE' => $message,
                ]
            );
        }

        return $id ? true : false;
    }
    
    public function down()
    {
    }
}
