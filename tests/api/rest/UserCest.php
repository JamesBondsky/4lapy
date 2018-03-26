<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

use _support\LoggedApiUser;
use Codeception\Example;
use Codeception\Util\HttpCode;

class UserCest
{
    /**
     * @param ApiTester $I
     * @param Example $example
     *
     * @dataprovider goodRegistrationProvider
     * @throws Exception
     */
    public function testRegister(ApiTester $I, Example $example): void
    {
        $token = $I->createToken();
        $I->wantTo('Test registration process');
        $I->haveHttpHeader('Content-type', 'application/json');
        $data = $example['callback']();
        $I->sendPOST('/user_login/', [
            'token'           => $token,
            'user_login_info' => $data,
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'data'  => [
                'user' => [
                    'email'     => 'string:empty',
                    'firstname' => 'string:empty',
                    'lastname'  => 'string:empty',
                    'phone'     => 'string:!empty',
                ],
            ],
            'error' => 'array:empty',
        ]);
        $I->seeResponseContainsJson([
            'data' => [
                'user' => [
                    'phone' => $data['login'],
                ],
            ],
        ]);
        $fUserId = $I->grabFromDatabase('api_user_session', 'FUSER_ID', [
            'TOKEN' => $token,
        ]);
        $userId = (int)$I->grabFromDatabase('b_sale_fuser', 'USER_ID', [
            'ID' => $fUserId,
        ]);
        if (!$userId) {
            throw new RuntimeException('No user for token ' . $token);
        }
        $I->seeInDatabase('b_user', [
            'ID'             => $userId,
            'LOGIN'          => $data['login'],
            'PERSONAL_PHONE' => $data['login'],
        ]);
    }

    /**
     * @return array
     */
    protected function goodRegistrationProvider(): array
    {
        return [
            [
                'callback' => function () {
                    return [
                        'login'    => random_int(9160000000, 9179999999),
                        'password' => md5(random_bytes(1024)),
                    ];
                },
            ],
        ];
    }

    /**
     * @param ApiTester $I
     *
     * @throws \RuntimeException
     * @throws Exception
     */
    public function testAuth(ApiTester $I): void
    {
        $token = $I->createToken();
        $user = $I->createDummyUser();

        $I->wantTo('check valid auth');
        $I->haveHttpHeader('Content-type', 'application/json');
        $I->sendPOST('/user_login/', [
            'token'           => $token,
            'user_login_info' => [
                'login'    => $user['PERSONAL_PHONE'],
                'password' => $user['PASSWORD'],
            ],
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'data'  => [
                'user' => [
                    'firstname' => 'string',
                    'lastname'  => 'string',
                    'email'     => 'string:!empty',
                ],
            ],
            'error' => 'array:empty',
        ]);
        $I->seeResponseContainsJson([
            'firstname' => $user['NAME'],
            'lastname'  => $user['LAST_NAME'],
            'email'     => $user['EMAIL'],
        ]);
        $fUserId = $I->grabFromDatabase('api_user_session', 'FUSER_ID', [
            'TOKEN' => $token,
        ]);
        $userId = $I->grabFromDatabase('b_sale_fuser', 'USER_ID', [
            'ID' => $fUserId,
        ]);
        if (!$userId) {
            throw new RuntimeException('No user for token ' . $token);
        }
    }


    /**
     * @param ApiTester $I
     *
     * @param Example $example
     *
     * @dataprovider wrongAuthProvider
     * @throws Exception
     */
    public function testWrongAuth(ApiTester $I, Example $example): void
    {
        $token = $I->createToken();
        $user = $I->createDummyUser();

        $I->wantTo('check wrong auth');
        $I->haveHttpHeader('Content-type', 'application/json');
        $I->sendPOST('/user_login/', [
            'token'           => $token,
            'user_login_info' => $example['callback']($user),
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'data'  => 'array:empty',
            'error' => 'array:!empty',
        ]);
    }

    protected function wrongAuthProvider(): array
    {
        return [
            [
                'callback' => function ($user) {
                    return [
                        'login'    => $user['EMAIL'],
                        'password' => $user['PASSWORD'],
                    ];
                },
            ],
            [
                'callback' => function ($user) {
                    return [
                        'login'    => $user['PERSONAL_PHONE'],
                        'password' => md5(random_bytes(32)),
                    ];
                },
            ],
            [
                'callback' => function ($user) {
                    return [
                        'login'    => $user['LOGIN'],
                        'password' => '',
                    ];
                },
            ],
            [
                'callback' => function ($user) {
                    return [
                        'login'    => $user['EMAIL'],
                        'password' => '',
                    ];
                },
            ],
            [
                'callback' => function ($user) {
                    return [
                        'login'    => $user['PERSONAL_PHONE'],
                        'password' => '',
                    ];
                },
            ],
            [
                'callback' => function ($user) {
                    return [
                        'login'    => $user['LOGIN'],
                        'password' => random_bytes(1024),
                    ];
                },
            ],
            [
                'callback' => function ($user) {
                    return [
                        'login'    => '',
                        'password' => random_bytes(1024),
                    ];
                },
            ],
        ];
    }


    /**
     * @param ApiTester $I
     * @throws Exception
     */
    public function testLogout(ApiTester $I): void
    {
        $token = $I->createToken();
        $user = $I->createDummyUser();
        $I->login($user['ID'], $token);


        $I->haveHttpHeader('Content-type', 'application/json');
        $I->sendGET('/logout/', [
            'token' => $token,
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'data'  => [
                'feedback_text' => 'string:!empty',
            ],
            'error' => 'array:empty',
        ]);
        $I->seeInDatabase('api_user_session', [
            'TOKEN' => $token,
        ]);
        $I->dontSeeInDatabase('api_user_session', [
            'TOKEN'    => $token,
            'FUSER_ID' => $user['FUSER_ID'],
        ]);
    }

    /**
     * @param ApiTester $I
     * @param Example $example
     *
     * @dataprovider existLoginProvider
     * @throws Exception
     */
    public function testExistLoginExist(ApiTester $I, Example $example): void
    {
        $I->wantTo('Test exist user');

        $token = $I->createToken();
        $user = $I->createDummyUser();

        $I->haveHttpHeader('Content-type', 'application/json');
        $I->sendGET('/login_exist/', [
            'token' => $token,
            'login' => $example['callback']($user),
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'data'  => [
                'exist' => 'boolean',
            ],
            'error' => 'array:empty',
        ]);
        $I->seeResponseContainsJson(['exist' => true]);
    }

    /**
     * @return array
     */
    protected function existLoginProvider(): array
    {
        return [
            [
                'callback' => function ($user) {
                    return $user['LOGIN'];
                },
            ],
            [
                'callback' => function ($user) {
                    return $user['EMAIL'];
                },
            ],
            [
                'callback' => function ($user) {
                    return $user['PERSONAL_PHONE'];
                },
            ],
        ];
    }

    /**
     * @param ApiTester $I
     * @throws Exception
     */
    public function testNonExistLoginExist(ApiTester $I): void
    {
        $I->wantTo('Test exist user');

        $I->haveHttpHeader('Content-type', 'application/json');
        $I->sendGET('/login_exist/', [
            'token' => $I->createToken(),
            'login' => md5(random_bytes(1024)),
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'data'  => [
                'exist'         => 'boolean',
                'feedback_text' => 'string:!empty',
            ],
            'error' => 'array:empty',
        ]);
        $I->seeResponseContainsJson(['exist' => false]);
    }

    /**
     * @param LoggedApiUser $I
     * @param Example $example
     *
     * @dataprovider validUpdateUserInfoProvider
     */
    public function testUpdateUserInfo(LoggedApiUser $I, Example $example): void
    {
        $I->wantTo('Test updating exist user');

        $I->haveHttpHeader('Content-type', 'application/json');
        $data = $example['data'];

        $I->sendPOST('/user_info/', [
            'token' => $I->getToken(),
            'user'  => $data,
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'data'  => [
                'user' => 'array:!empty',
            ],
            'error' => 'array:empty',
        ]);


        $I->seeResponseContainsJson(['data' => ['user' => $data]]);
        $I->seeInDatabase('b_user', [
            'EMAIL'          => $data['email'] ?? $I->getField('EMAIL'),
            'NAME'           => $data['firstname'] ?? $I->getField('NAME'),
            'LAST_NAME'      => $data['lastname'] ?? $I->getField('LAST_NAME'),
            'SECOND_NAME'    => $data['midname'] ?? $I->getField('SECOND_NAME'),
            'PERSONAL_PHONE' => $data['phone'] ?? $I->getField('PERSONAL_PHONE'),
        ]);
    }

    protected function validUpdateUserInfoProvider(): array
    {
        return [
            [
                'data' => [
                    'email'     => md5(random_bytes(1024)) . '@' . md5(random_bytes(1024)) . '.ru',
                    'firstname' => md5(random_bytes(1024)),
                    'lastname'  => md5(random_bytes(1024)),
                    'midname'   => md5(random_bytes(1024)),
                    'birthdate' => implode('.', [
                        str_pad(random_int(1, 26), 2, '0', STR_PAD_LEFT),
                        str_pad(random_int(1, 12), 2, '0', STR_PAD_LEFT),
                        random_int(1900, 2017),
                    ]),
                    'phone'     => random_int(9160000000, 9169999999),
                ],
            ],
            [
                'data' => [
                    'email'     => md5(random_bytes(1024)) . '@' . md5(random_bytes(1024)) . '.ru',
                    'firstname' => md5(random_bytes(1024)),
                    'lastname'  => md5(random_bytes(1024)),
                    'midname'   => md5(random_bytes(1024)),
                    'birthdate' => implode('.', [
                        str_pad(random_int(1, 26), 2, '0', STR_PAD_LEFT),
                        str_pad(random_int(1, 12), 2, '0', STR_PAD_LEFT),
                        random_int(1900, 2017),
                    ]),
                    'phone'     => random_int(9160000000, 9169999999),
                ],
            ],
            [
                'data' => [
                    'email'     => md5(random_bytes(1024)) . '@' . md5(random_bytes(1024)) . '.ru',
                    'firstname' => md5(random_bytes(1024)),
                    'lastname'  => md5(random_bytes(1024)),
                    'midname'   => md5(random_bytes(1024)),
                    'birthdate' => implode('.', [
                        str_pad(random_int(1, 26), 2, '0', STR_PAD_LEFT),
                        str_pad(random_int(1, 12), 2, '0', STR_PAD_LEFT),
                        random_int(1900, 2017),
                    ]),
                    'phone'     => random_int(9160000000, 9169999999),
                ],
            ],
            [
                'data' => [
                    'email' => md5(random_bytes(1024)) . '@' . md5(random_bytes(1024)) . '.ru',
                    'phone' => random_int(9160000000, 9169999999),
                ],
            ],
            [
                'data' => [
                    'phone' => random_int(9160000000, 9169999999),
                ],
            ],
        ];
    }

    public function testUserInfoGet(LoggedApiUser $I): void
    {
        $I->wantTo('Test get user info');
        $I->haveHttpHeader('Content-type', 'application/json');
        $I->sendGET('/user_info/', [
            'token' => $I->getToken(),
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'data'  => [
                'user' => 'array:!empty',
            ],
            'error' => 'array:empty',
        ]);
        $I->seeResponseContainsJson([
            'data' => [
                'user' => [
                    'email'     => (string)$I->getField('EMAIL'),
                    'firstname' => (string)$I->getField('NAME'),
                    'lastname'  => (string)$I->getField('LAST_NAME'),
                    'midname'   => (string)$I->getField('SECOND_NAME'),
                    'phone'     => (string)$I->getField('PERSONAL_PHONE'),
                    'birthdate' => (string)$I->getField('PERSONAL_BIRTHDAY'),
                ],
            ],
        ]);
    }

    /**
     * @param ApiTester $I
     * @throws Exception
     */
    public function testUserInfoGetUnauthorized(ApiTester $I): void
    {
        $I->wantTo('Test get user info unauthorized');
        $I->haveHttpHeader('Content-type', 'application/json');
        $I->sendGET('/user_info/', [
            'token' => $I->createToken(),
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'data'  => 'array:empty',
            'error' => 'array:!empty',
        ]);
        $I->seeResponseContainsJson([
            'error' => [
                [
                    'code' => 9,
                ],
            ],
        ]);
    }
}
