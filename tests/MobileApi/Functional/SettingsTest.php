<?php

namespace FourPaws\Test\MobileApi\Functional;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test generate and verify captcha for phone and email
 *
 * Class SettingsTest
 *
 * @package FourPaws\Tests\MobileApi\Functional
 */
class SettingsTest extends BaseTest
{
    private $settings = [
        'interview_messaging_enabled',
        'bonus_messaging_enabled'    => 1,
        'feedback_messaging_enabled' => 0,
        'push_order_status'          => 1,
        'push_news'                  => 0,
        'push_account_change'        => 1,
        'sms_messaging_enabled'      => 0,
        'email_messaging_enabled'    => 1,
        'gps_messaging_enabled'      => 0,
    ];

    public function testCreate()
    {
        $userData = $this->generateUserData();
        $userId = $this->createUser($userData);

        $this->loginUser($userData['LOGIN'], $userData['PASSWORD']);

        $this->testValid($this->settings);
        $this->testInValid();

        $this->deleteUser($userId);
    }

    /**
     * @return array
     */
    private function generateUserData(): array
    {
        $pass = randString();
        return [
            'NAME'             => \randString(),
            'LAST_NAME'        => \randString(),
            'LOGIN'            => \randString(),
            'EMAIL'            => \randString(5) . '@' . randString(5) . '.ru',
            'GROUP_ID'         => [6],
            'ACTIVE'           => 'Y',
            'PASSWORD'         => $pass,
            'CONFIRM_PASSWORD' => $pass,
        ];
    }

    /**
     * @param array $userData
     *
     * @return bool|int|string
     */
    private function createUser(array $userData)
    {
        $user = new \CUser;
        return $user->Add($userData);
    }

    /**
     * @param string $login
     * @param string $password
     */
    private function loginUser(string $login, string $password)
    {
        $client = static::createClient();
        $client->request(Request::METHOD_POST, '/mobile_app_v2/user_login/', [
            'token'           => $this->getToken(),
            'user_login_info' => [
                'login'    => $login,
                'password' => $password,
            ],
        ]);
    }

    /**
     * @param int $id
     */
    private function deleteUser(int $id)
    {
        \CUser::Delete($id);
    }

    public function testValid($settings)
    {
        $client = static::createClient();

        // Set settings
        $client->request(Request::METHOD_POST, '/mobile_app_v2/settings/', [
            'token'    => $this->getToken(),
            'settings' => $settings,
        ]);

        $response = $client->getResponse();

        if ($response) {
            $content = $response->getContent();
            static::assertEquals(Response::HTTP_OK, $response->getStatusCode());
            static::assertJson($content);
            $data = json_decode($content, true);
            static::assertTrue(\is_array($data));
            static::assertCount(0, $data['error']);
            static::assertArrayHasKey('data', $data);
            static::assertArrayHasKey('feedback_text', $data['data']);
            static::assertNotEmpty($data['data']['feedback_text']);
        }

        // Get settings
        $client->request(Request::METHOD_GET, '/mobile_app_v2/settings/', [
            'token'    => $this->getToken(),
            'settings' => $settings,
        ]);

        $response = $client->getResponse();

        if ($response) {
            $content = $response->getContent();
            static::assertEquals(Response::HTTP_OK, $response->getStatusCode());
            static::assertJson($content);
            $data = json_decode($content, true);
            static::assertTrue(\is_array($data));
            static::assertCount(0, $data['error']);
            static::assertArrayHasKey('data', $data);
            static::assertArrayHasKey('settings', $data['data']);

            foreach($settings as $field => $value){
                static::assertArrayHasKey($field, $data['data']['settings']);
                static::assertEquals($value, $data['data']['settings'][$field]);
            }
        }

    }

    public function testInValid()
    {
        $client = static::createClient();
        $client->request(Request::METHOD_POST, '/mobile_app_v2/settings/', [
            'token'           => $this->getToken(),
        ]);

        $response = $client->getResponse();

        if ($response) {
            $content = $response->getContent();
            static::assertEquals(Response::HTTP_OK, $response->getStatusCode());
            static::assertJson($content);
            $data = json_decode($content, true);
            static::assertTrue(\is_array($data));
            static::assertCount(1, $data['error']);
            static::assertArrayHasKey('data', $data);
            static::assertEmpty($data['data']);
        }
    }
}
