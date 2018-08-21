<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;

/**
 * Class AutoPasswordChangeEMailEvent20180816173225
 * @package Sprint\Migration
 */
class AutoPasswordChangeEMailEvent20180816173225 extends SprintMigrationBase
{

    protected $description = 'Почтовое событие на автоматической смене пароля';

    /**
     *
     *
     * @throws Exceptions\HelperException
     * @return bool
     */
    public function up(): bool
    {
        $helper = new HelperManager();
        $langId = 'ru';
        $siteId = 's1';
        $eventName = 'FRONT_OFFICE_PASSWORD_RESET';

        $description = '#NEW_PASSWORD# - Новый пароль' . PHP_EOL;
        $description .= '#USER_ID# - ID пользователя' . PHP_EOL;
        $description .= '#USER_NAME# - Имя пользователя' . PHP_EOL;
        $description .= '#USER_LAST_NAME# - Фамилия пользователя' . PHP_EOL;
        $description .= '#USER_FULL_NAME# - Полное имя пользователя' . PHP_EOL;
        $description .= '#USER_EMAIL# - E-mail пользователя' . PHP_EOL;
        $description .= '#USER_LOGIN# - Логин пользователя' . PHP_EOL;

        $id = $helper->Event()->addEventTypeIfNotExists(
            $eventName,
            [
                'LID' => $langId,
                'NAME' => 'Автоматическая смена пароля',
                'DESCRIPTION' => $description,
            ]
        );
        if ($id) {
            $message = '#USER_FULL_NAME#, ваш пароль был автоматически сменен.' . PHP_EOL;
            $message .= 'Логин: #USER_LOGIN#' . PHP_EOL;
            $message .= 'Новый пароль: #NEW_PASSWORD#' . PHP_EOL . PHP_EOL;
            $message .= 'Сообщение сгенерировано автоматически.' . PHP_EOL;

            $helper->Event()->addEventMessageIfNotExists(
                $eventName,
                [
                    'LID' => $siteId,
                    'EMAIL_TO' => '#USER_EMAIL#',
                    'SUBJECT' => 'Автоматическая смена пароля',
                    'BODY_TYPE' => 'text',
                    'MESSAGE' => $message,
                ]
            );
        }

        return $id ? true : false;
    }

    /**
     * @return bool|void
     */
    public function down()
    {
        //не требуется
    }

}
