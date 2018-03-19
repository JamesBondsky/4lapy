<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

use _support\LoggedApiUser;
use Codeception\Example;
use Codeception\Util\HttpCode;

class SettingsCest
{
    protected $settings;

    protected $settingsToken;

    /**
     * @param LoggedApiUser $apiUser
     * @param Example $example
     * @throws \Exception
     * @dataprovider validSettingsProvider
     */
    public function testUpdateValid(LoggedApiUser $apiUser, Example $example): void
    {
        $currentSettings = $apiUser->getSettings($apiUser->getUserId());

        $apiUser->wantTo('Test settings update');
        $apiUser->haveHttpHeader('Content-type', 'application/json');
        $apiUser->sendPOST('/settings/', [
            'token'    => $apiUser->getToken(),
            'settings' => $example['settings'],
        ]);

        $apiUser->seeResponseCodeIs(HttpCode::OK);
        $apiUser->seeResponseIsJson();
        $apiUser->seeResponseMatchesJsonType([
            'data'  => [
                'feedback_text' => 'string:!empty',
            ],
            'error' => 'array:empty',
        ]);

        $newSettings = $apiUser->getSettings($apiUser->getUserId());

        $apiUser->assertEquals(
            array_diff_assoc($example['settings'], $currentSettings),
            array_diff_assoc($newSettings, $currentSettings)
        );
    }

    protected function validSettingsProvider(): array
    {
        return [
            'one setting'  => [
                'settings' => [
                    'interview_messaging_enabled' => true,
                ],
            ],
            'all settings' => [
                'settings' => [
                    'interview_messaging_enabled' => true,
                    'bonus_messaging_enabled'     => true,
                    'feedback_messaging_enabled'  => false,
                    'push_order_status'           => true,
                    'push_news'                   => false,
                    'push_account_change'         => true,
                    'sms_messaging_enabled'       => false,
                    'email_messaging_enabled'     => true,
                    'gps_messaging_enabled'       => false,
                ],
            ],
        ];
    }
}
