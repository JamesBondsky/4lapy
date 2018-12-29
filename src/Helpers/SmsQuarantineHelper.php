<?php

namespace FourPaws\Helpers;


use FourPaws\Entity\Sms\LogTable;
use FourPaws\Entity\Sms\QuarantineTable;

class SmsQuarantineHelper
{

    CONST SMS_MINUTE = 1;

    /**
     * @param int $number
     * @return bool
     * @throws \Exception
     */
    public static function canSend(int $phone) {

        if (QuarantineTable::isInQuarantine($phone)) {
            return false;
        }

        if (LogTable::getCurrentDayCount() >= 15000) {
            mail(implode(', ', [
                's.mamontov@articul.ru',
                'e.bagro@articul.ru',
                'm.balezin@articul.ru',
                'pvlasov@4lapy.ru',
                'edushenko@4lapy.ru'
            ]), 'Превышени суточный лимит в 15000 смс на 4lapy.ru', '');
            return false;
        }

        if (LogTable::getCurrentHourCount() >= 1000) {
            mail(implode(', ', [
                's.mamontov@articul.ru',
                'e.bagro@articul.ru',
            ]), 'Превышени часовой лимит в 1000 смс на 4lapy.ru', '');
            return false;
        }

        if (LogTable::getByPhoneCount($phone, (new \DateTime())->modify('-1 day')) >=10) {
            QuarantineTable::addToQuarantine($phone, (new \DateTime())->modify('+1 day'));
        }

        if (LogTable::getByPhoneCount($phone) >= static::SMS_MINUTE) {

            $dayCount = LogTable::getByPhoneCount($phone, (new \DateTime())->modify('-1 day'));
            $quarantineTime = static::getQuarantineTime($dayCount);
            QuarantineTable::addToQuarantine($phone, $quarantineTime);

        }

        LogTable::addByPhone($phone);

        return true;
    }

    public static function getRanges() {
        return [
            2 => (new \DateTime())->modify('+5 minute'),
            5 => (new \DateTime())->modify('+1 hour'),
            10 => (new \DateTime())->modify('+1 day'),
        ];
    }

    public static function getQuarantineTime($smsCount) {
        $count = null;
        foreach(array_keys(static::getRanges()) as $value) {
            if ($value <= $smsCount) {
                $count = $value;
            } else {
                break;
            }
        }

        return static::getRanges()[$count];
    }

}