<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

use Codeception\Util\HttpCode;

class ApiTester extends \Codeception\Actor
{
    use _generated\ApiTesterActions;

    /**
     * @throws Exception
     */
    public function createToken()
    {
        $this->haveHttpHeader('Content-type', 'application/json');
        $this->sendGET('/start/');
        $this->seeResponseCodeIs(HttpCode::OK);
        $this->seeResponseIsJson();
        $this->seeResponseMatchesJsonType([
            'data' => [
                'access_id' => 'string:!empty',
            ],
        ]);

        $data = $this->grabDataFromResponseByJsonPath('$.data.access_id');

        if (!$data[0]) {
            throw new RuntimeException('No token was provided');
        }

        $this->seeInDatabase('api_user_session', [
            'TOKEN' => $data[0],
        ]);

        return $data[0];
    }

    /**
     * @param string $token
     */
    public function deleteToken(string $token)
    {
        $this->sendDELETE(sprintf('/fake/session/%s/', $token));
        $this->seeResponseCodeIs(HttpCode::OK);
        $this->dontSeeInDatabase('api_user_session', [
            'TOKEN' => $token,
        ]);
    }

    public function createDummyUser()
    {
        $this->sendGET('/fake/user/dummy/');
        $this->seeResponseCodeIs(HttpCode::OK);
        $this->seeResponseMatchesJsonType([
            'ID'       => 'integer:>0',
            'EMAIL'    => 'string:email',
            'PASSWORD' => 'string:!empty',
        ], '$.user');
        $userData = $this->grabDataFromResponseByJsonPath('$.user');
        $user = reset($userData);

        $this->seeInDatabase('b_user', [
            'ID'             => $user['ID'],
            'EMAIL'          => $user['EMAIL'],
            'LOGIN'          => $user['LOGIN'],
            'PERSONAL_PHONE' => $user['PERSONAL_PHONE'],
        ]);
        return $user;
    }

    public function deleteDummyUser()
    {
        $this->sendDELETE('/fake/user/dummy/');
        $this->seeResponseCodeIs(HttpCode::OK);
        $this->dontSeeInDatabase('b_user', [
            'SECOND_NAME' => 'fixture',
        ]);
    }

    public function login(string $token, string $login, string $password)
    {
        $this->haveHttpHeader('Content-type', 'application/json');
        $this->sendPOST('/user_login/', [
            'token'           => $token,
            'user_login_info' => [
                'login'    => $login,
                'password' => $password,
            ],
        ]);

        $this->seeResponseCodeIs(HttpCode::OK);
        $this->seeResponseIsJson();
        $this->seeResponseMatchesJsonType([
            'data'  => 'array:!empty',
            'error' => 'array:empty',
        ]);
    }
}
