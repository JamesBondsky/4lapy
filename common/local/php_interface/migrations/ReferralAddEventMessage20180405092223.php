<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;

class ReferralAddEventMessage20180405092223 extends SprintMigrationBase {

    protected $description = 'Добавление письма по отмене модерации рефералом';

    /**
     * @return bool|void
     * @throws Exceptions\HelperException
     */
    public function up(){
        $helper = new HelperManager();

        $siteId = 's1';
        $lang = 'ru';

        $helper->Event()->addEventTypeIfNotExists(
            'ReferralModeratedCancel',
            [
                'LID'         => $lang,
                'NAME'        => 'Отмена модерации реферала',
                'DESCRIPTION' => "#CARD# - номер карты 
#EMAIL# - email реферала",
            ]
        );

        $helper->Event()->addEventMessageIfNotExists(
            'ReferralModeratedCancel',
            [
                'LID'      => $siteId,
                'LANGUAGE_ID'      => $lang,
                'EMAIL_TO' => '#EMAIL#',
                'SUBJECT'  => 'Отмена модерации реферала',
                'MESSAGE'  => 'Результат модерации реферала с номером карты #CARD# отрицательный. Вы можете уточнить подробности по телефону 8 (800) 770-00-22',
            ]
        );

        /** пересоздание письма */
        $by = '';
        $order = '';
        $eventId = (int)\CEventMessage::GetList($by, $order, ['TYPE_ID'=>'ReferralAdd'])->Fetch()['ID'];
        if($eventId > 0) {
            \CEventMessage::Delete($eventId);
        }
        \CEventType::Delete('ReferralAdd');

        $helper->Event()->addEventTypeIfNotExists(
            'ReferralAdd',
            [
                'LID'         => $lang,
                'NAME'        => 'Добавление реферала',
                'DESCRIPTION' => '#CARD# - номер карты',
            ]
        );

        $helper->Event()->addEventMessageIfNotExists(
            'ReferralAdd',
            [
                'LID'      => $siteId,
                'LANGUAGE_ID'      => $lang,
                'EMAIL_TO' => '#DEFAULT_EMAIL_FROM#',
                'SUBJECT'  => 'Новый реферал',
                'MESSAGE'  => 'Добавлен новый реферал с номером карты #CARD#. Необходимо произвести модерацию',
            ]
        );

    }
}
