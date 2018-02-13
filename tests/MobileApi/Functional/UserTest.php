<?php

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
    public function testCreate()
    {
        $userData = $this->generateUserData();
        $userId = $this->createUser($userData);
        $this->checkUserAuthValid($userData['LOGIN'], $userData['PASSWORD'], $userData);
        $this->checkUserAuthInValid(randString(), randString());
        $this->checkUserLogoutValid();

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
     * @param int $id
     */
    private function deleteUser(int $id)
    {
        \CUser::Delete($id);
    }

    /**
     * @param string $login
     * @param string $password
     * @param array  $userData
     */
    private function checkUserAuthValid(string $login, string $password, array $userData)
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

            static::assertTrue(\is_array($data));
            static::assertCount(0, $data['error']);
            static::assertArrayHasKey('data', $data);
            static::assertEquals($userData['EMAIL'], $data['data']['email']);
            static::assertEquals($userData['NAME'], $data['data']['firstname']);
            static::assertEquals($userData['LAST_NAME'], $data['data']['lastname']);
        }
    }

    /**
     * @param string $login
     * @param string $password
     */
    private function checkUserAuthInValid(string $login, string $password)
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
            static::assertTrue(\is_array($data));
            static::assertCount(1, $data['error']);
            static::assertArrayHasKey('data', $data);
            static::assertEmpty($data['data']);
        }
    }


    private function checkUserLogoutValid()
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
            static::assertTrue(\is_array($data));
            static::assertCount(0, $data['error']);
            static::assertArrayHasKey('data', $data);
            static::assertArrayHasKey('feedback_text', $data['data']);
            static::assertNotEmpty($data['data']['feedback_text']);
        }
    }
}
