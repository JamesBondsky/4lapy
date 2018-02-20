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
        if (!static::getUserData()) {
            throw new \RuntimeException('Cant generate test data');
        }
        $cUser = new \CUser();
        static::$userId = $cUser->Add(static::getUserData());
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
    private static function getUserData(): array
    {
        if (!count(static::$userData)) {
            $pass = randString();
            static::$userData = [
                'NAME'             => \randString(),
                'LAST_NAME'        => \randString(),
                'LOGIN'            => \randString(),
                'EMAIL'            => \randString(5) . '@' . randString(5) . '.ru',
                'GROUP_ID'         => [6],
                'ACTIVE'           => 'Y',
                'PASSWORD'         => $pass,
                'CONFIRM_PASSWORD' => $pass,
                'PERSONAL_PHONE'   => \randString(10, '0123456789'),
            ];
        }

        return static::$userData;
    }

    public function testAuth()
    {
        $client = static::createClient();
        $client->request(Request::METHOD_POST, '/mobile_app_v2/user_login/', [
            'token'           => $this->getToken(),
            'user_login_info' => [
                'login'    => static::getUserData()['LOGIN'],
                'password' => static::getUserData()['PASSWORD'],
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
            static::assertEquals(static::getUserData()['EMAIL'], $data['data']['email']);
            static::assertEquals(static::getUserData()['NAME'], $data['data']['firstname']);
            static::assertEquals(static::getUserData()['LAST_NAME'], $data['data']['lastname']);
        }
    }

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
            static::assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
            static::assertJson($content);
            $data = json_decode($content, true);
            static::assertInternalType('array', $data);
            static::assertCount(1, $data['error']);
            static::assertArrayHasKey('data', $data);
            static::assertEmpty($data['data']);
        }
    }

    /**
     * @param $login
     * @param $password
     *
     * @dataProvider wrongParametersDataProvider
     */
    public function testWrongParameters($login, $password)
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
            static::assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
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
                static::getUserData()['EMAIL'],
                randString(12),
            ],
            'phone, string'     => [
                static::getUserData()['PERSONAL_PHONE'],
                randString(12),
            ],
            'email, big_string' => [
                static::getUserData()['EMAIL'],
                random_bytes(1024),
            ],
            'phone, big_string' => [
                static::getUserData()['PERSONAL_PHONE'],
                random_bytes(1024),
            ],
        ];
    }

    public function wrongParametersDataProvider(): array
    {
        return [
            'email, null'      => [
                static::getUserData()['EMAIL'],
                null,
            ],
            'phone, null'      => [
                static::getUserData()['PERSONAL_PHONE'],
                null,
            ],
            'null, null'       => [
                null,
                null,
            ],
            'null, string'     => [
                null,
                randString(12),
            ],
            'null, big_string' => [
                null,
                random_bytes(1024),
            ],
        ];
    }

    /**
     * @param $login
     * @param $password
     *
     * @dataProvider logoutDataProvider
     */
    public function testWrongLogout($login, $password)
    {
        $client = static::createClient();
        $client->request(Request::METHOD_POST, '/mobile_app_v2/logout/', [
            'token'           => $this->getToken(),
            'user_login_info' => [
                'login'    => $login,
                'password' => $password,
            ],
        ]);

        $response = $client->getResponse();

        if ($response) {
            $content = $response->getContent();
            static::assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
            static::assertJson($content);
            $data = json_decode($content, true);
            static::assertInternalType('array', $data);
            static::assertCount(1, $data['error']);
            static::assertArrayHasKey('data', $data);
            static::assertEmpty($data['data']);
        }
    }

    public function logoutDataProvider(): array
    {
        return [
            'email, string'    => [
                static::getUserData()['EMAIL'],
                static::getUserData()['PASSWORD'],
            ],
            'phone, string'    => [
                static::getUserData()['PERSONAL_PHONE'],
                static::getUserData()['PASSWORD'],
            ],
            'email, null'      => [
                static::getUserData()['EMAIL'],
                null,
            ],
            'phone, null'      => [
                static::getUserData()['PERSONAL_PHONE'],
                null,
            ],
            'null, null'       => [
                null,
                null,
            ],
            'null, string'     => [
                null,
                randString(12),
            ],
            'null, big_string' => [
                null,
                random_bytes(1024),
            ],
        ];
    }

    public function testLogout()
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/mobile_app_v2/logout/', [
            'token' => $this->getToken(),
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
            static::assertArrayHasKey('feedback_text', $data['data']);
        }
    }
}