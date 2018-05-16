<?php

namespace Sprint\Migration;


use Bitrix\Main\Mail\Internal\EventMessageTable;
use Bitrix\Main\ModuleManager;

class DeleteUnusedMailTemplate20180511151918 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = 'удаление неиспользуемых почтовых шаблонов';

    public function up()
    {
        if (ModuleManager::isModuleInstalled('support')) {
            ModuleManager::delete('support');
        }
        if (ModuleManager::isModuleInstalled('conversion')) {
            ModuleManager::delete('conversion');
        }
        if (ModuleManager::isModuleInstalled('compression')) {
            ModuleManager::delete('compression');
        }
        if (ModuleManager::isModuleInstalled('calendar')) {
            ModuleManager::delete('calendar');
        }
        if (ModuleManager::isModuleInstalled('report')) {
            ModuleManager::delete('report');
        }

        $eventTypes = [
            'SONET_LOG_NEW_ENTRY',
            'SONET_LOG_NEW_COMMENT',
            'NEW_LEARNING_TEXT_ANSWER',
            'CALENDAR_INVITATION',
            'BLOG_SONET_POST_SHARE',
            'BLOG_SONET_NEW_POST',
            'BLOG_SONET_NEW_COMMENT',
            'BLOG_POST_BROADCAST',
            'TICKET_OVERDUE_REMINDER',
            'TICKET_NEW_FOR_TECHSUPPORT',
            'TICKET_NEW_FOR_AUTHOR',
            'TICKET_GENERATE_SUPERCOUPON',
            'TICKET_CHANGE_FOR_TECHSUPPORT',
            'TICKET_CHANGE_BY_SUPPORT_FOR_AUTHOR',
            'TICKET_CHANGE_BY_AUTHOR_FOR_AUTHOR',
            'SALE_ORDER_TRACKING_NUMBER',
            'SALE_ORDER_SHIPMENT_STATUS_CHANGED',
            'SALE_CHECK_PRINT',
            'FEEDBACK_FORM',
            'FORUM_NEW_MESSAGE_MAIL',

            'SALE_ORDER_PAID',
            'SALE_ORDER_DELIVERY',
            'SALE_ORDER_CANCEL',
            'SALE_NEW_ORDER_RECURRING',
            'SALE_NEW_ORDER',
            'SALE_ORDER_REMIND_PAYMENT',
            'SALE_RECURRING_CANCEL',
            'SALE_STATUS_CHANGED_N',
            'SALE_STATUS_CHANGED_F',
            'SALE_SUBSCRIBE_PRODUCT',

            'MAIN_MAIL_CONFIRM_CODE',

            'CATALOG_PRODUCT_SUBSCRIBE_NOTIFY_REPEATED',
            'CATALOG_PRODUCT_SUBSCRIBE_NOTIFY',
            'CATALOG_PRODUCT_SUBSCRIBE_LIST_CONFIRM',

            'USER_INVITE',
            'USER_INFO',
            'NEW_USER_CONFIRM',
        ];

        $deactivateEventTypes = [
            'USER_PASS_CHANGED',
            'USER_PASS_REQUEST',
            'NEW_USER',
        ];

        foreach ($eventTypes as $eventType) {
            $res = EventMessageTable::query()->setSelect(['ID'])->where('EVENT_NAME', $eventType)->exec();
            while ($eventMessageItem = $res->fetch()) {
                \CEventMessage::Delete($eventMessageItem['ID']);
            }
            \CEventType::Delete($eventType);
        }

        $res = EventMessageTable::query()->setSelect(['ID'])->whereIn('EVENT_NAME', $deactivateEventTypes)->exec();
        $eventMessage = new \CEventMessage();
        while ($eventMessageItem = $res->fetch()) {
            $eventMessage->Update($eventMessageItem['ID'], ['ACTIVE' => 'N']);
        }
    }

}
