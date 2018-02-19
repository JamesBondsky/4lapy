<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\Test\MobileApi\Functional;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test generate and verify captcha for phone and email
 *
 * Class UserTest
 *
 * @package FourPaws\Tests\MobileApi\Functional
 */
class UserTest extends BaseTest
{
    /**
     * @var array
     */
    protected static $userData = [];

    /**
     * @var int
     */
    protected static $userId = 0;


    public static function setUpBeforeClass()
    {
        static::$userData = static::generateUserData();
        if (!static::$userData) {
            throw new \RuntimeException('Cant generate test data');
        }
        $cUser = new \CUser();
        static::$userId = $cUser->Add(static::$userData);
        if (!static::$userId) {
            throw new \RuntimeException(sprintf('Cant create user. %s', $cUser->LAST_ERROR));
        }
    }

    public static function tearDownAfterClass()
    {
        \CUser::Delete(static::$userId);
    }

    /**
     * @return array
     */
    private static function generateUserData(): array
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

    public function testAuth()
    {
        $client = static::createClient();
        $client->request(Request::METHOD_POST, '/mobile_app_v2/user_login/', [
            'token'           => $this->getToken(),
            'user_login_info' => [
                'login'    => static::$userData['LOGIN'],
                'password' => static::$userData['PASSWORD'],
            ],
        ]);

        $response = $client->getResponse();

        if ($response) {
            $content = $response->getContent();
            static::assertEquals(Response::HTTP_OK, $response->getStatusCode());
            static::assertJson($content);
            $data = json_decode($content, true);

            static::assertInternalType('array', $data);
            static::assertCount(0, $data['error']);
            static::assertArrayHasKey('data', $data);
            static::assertEquals(static::$userData['EMAIL'], $data['data']['email']);
            static::assertEquals(static::$userData['NAME'], $data['data']['firstname']);
            static::assertEquals(static::$userData['LAST_NAME'], $data['data']['lastname']);
        }
    }

//    public function testCreate()
//    {
//        $this->checkUserAuthInValid(randString(), randString());
//        $this->checkUserLogoutValid();
//    }

    /**
     * @param $login
     * @param $password
     *
     * @dataProvider wrongAuthDataProvider
     */
    public function testWrongAuth($login, $password)
    {
        $client = static::createClient();
        $client->request(Request::METHOD_POST, '/mobile_app_v2/user_login/', [
            'token'           => $this->getToken(),
            'user_login_info' => [
                'login'    => $login,
                'password' => $password,
            ],
        ]);

        $response = $client->getResponse();

        if ($response) {
            $content = $response->getContent();
            static::assertEquals(Response::HTTP_OK, $response->getStatusCode());
            static::assertJson($content);
            $data = json_decode($content, true);
            static::assertInternalType('array', $data);
            static::assertCount(1, $data['error']);
            static::assertArrayHasKey('data', $data);
            static::assertEmpty($data['data']);
        }
    }

    public function wrongAuthDataProvider(): array
    {
        return [
            'email, string'     => [
                static::$userData['EMAIL'],
                randString(12),
            ],
            'phone, string'     => [
                static::$userData['PHONE'],
                randString(12),
            ],
            'email, null'       => [
                static::$userData['EMAIL'],
                null,
            ],
            'phone, null'       => [
                static::$userData['PHONE'],
                null,
            ],
            'email, big_string' => [
                static::$userData['EMAIL'],
                random_bytes(1024),
            ],
            'phone, big_string' => [
                static::$userData['PHONE'],
                random_bytes(1024),
            ],
        ];
    }
}
