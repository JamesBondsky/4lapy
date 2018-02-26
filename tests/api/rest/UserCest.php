<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

use Codeception\Example;
use Codeception\Util\HttpCode;

class UserCest
{
    private $token = '';
    private $user = [];

    public function _before(ApiTester $I)
    {
        $I->deleteDummyUser();
        $this->token = $I->createToken();
        $this->user = $I->createDummyUser();
    }

    public function _after(ApiTester $I)
    {
        $I->deleteToken($this->token);
        $this->token = '';
        $I->deleteDummyUser();
        $this->user = [];
    }

    /**
     * @param ApiTester $I
     * @param Example   $example
     *
     * @dataprovider goodAuthProvider
     * @throws \RuntimeException
     */
    public function testAuth(ApiTester $I, Example $example)
    {
        $I->wantTo('check valid auth');
        $I->haveHttpHeader('Content-type', 'application/json');
        $I->sendPOST('/user_login/', [
            'token'           => $this->token,
            'user_login_info' => $example['callback'](),
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
            'firstname' => $this->user['NAME'],
            'lastname'  => $this->user['LAST_NAME'],
            'email'     => $this->user['EMAIL'],
        ]);
        $fUserId = $I->grabFromDatabase('api_user_session', 'FUSER_ID', [
            'TOKEN' => $this->token,
        ]);
        $userId = $I->grabFromDatabase('b_sale_fuser', 'USER_ID', [
            'ID' => $fUserId,
        ]);
        if (!$userId) {
            throw new RuntimeException('No user for token ' . $this->token);
        }
    }

    public function goodAuthProvider()
    {
        return [
            [
                'callback' => function () {
                    return [
                        'login'    => $this->user['EMAIL'],
                        'password' => $this->user['PASSWORD'],
                    ];
                },
            ],
            [
                'callback' => function () {
                    return [
                        'login'    => $this->user['PERSONAL_PHONE'],
                        'password' => $this->user['PASSWORD'],
                    ];
                },
            ],
            [
                'callback' => function () {
                    return [
                        'login'    => $this->user['LOGIN'],
                        'password' => $this->user['PASSWORD'],
                    ];
                },
            ],
        ];
    }


    /**
     * @param ApiTester $I
     *
     * @param Example   $example
     *
     * @dataprovider wrongAuthProvider
     */
    public function testWrongAuth(ApiTester $I, Example $example)
    {
        $I->wantTo('check wrong auth');
        $I->haveHttpHeader('Content-type', 'application/json');
        $I->sendPOST('/user_login/', [
            'token'           => $this->token,
            'user_login_info' => $example['callback'](),
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'data'  => 'array:empty',
            'error' => 'array:!empty',
        ]);
    }


    public function wrongAuthProvider()
    {
        return [
            [
                'callback' => function () {
                    return [
                        'login'    => $this->user['LOGIN'],
                        'password' => md5(random_bytes(32)),
                    ];
                },
            ],
            [
                'callback' => function () {
                    return [
                        'login'    => $this->user['EMAIL'],
                        'password' => md5(random_bytes(32)),
                    ];
                },
            ],
            [
                'callback' => function () {
                    return [
                        'login'    => $this->user['PERSONAL_PHONE'],
                        'password' => md5(random_bytes(32)),
                    ];
                },
            ],
            [
                'callback' => function () {
                    return [
                        'login'    => $this->user['LOGIN'],
                        'password' => '',
                    ];
                },
            ],
            [
                'callback' => function () {
                    return [
                        'login'    => $this->user['EMAIL'],
                        'password' => '',
                    ];
                },
            ],
            [
                'callback' => function () {
                    return [
                        'login'    => $this->user['PERSONAL_PHONE'],
                        'password' => '',
                    ];
                },
            ],
            [
                'callback' => function () {
                    return [
                        'login'    => $this->user['LOGIN'],
                        'password' => random_bytes(1024),
                    ];
                },
            ],
            [
                'callback' => function () {
                    return [
                        'login'    => '',
                        'password' => random_bytes(1024),
                    ];
                },
            ],
        ];
    }

    public function testLogout(ApiTester $I)
    {
        $I->wantTo('Test logout');
        $fUserId = $I->grabFromDatabase('api_user_session', 'FUSER_ID', [
            'TOKEN' => $this->token,
        ]);
        $I->seeInDatabase('b_sale_fuser', [
            'ID'      => $fUserId,
            'USER_ID' => null,
        ]);

        $I->login($this->token, $this->user['LOGIN'], $this->user['PASSWORD']);

        $fUserId = $I->grabFromDatabase('api_user_session', 'FUSER_ID', [
            'TOKEN' => $this->token,
        ]);
        $userId = $I->grabFromDatabase('b_sale_fuser', 'USER_ID', [
            'ID' => $fUserId,
        ]);
        if (!$userId) {
            throw new RuntimeException('No user for token ' . $this->token);
        }

        $I->haveHttpHeader('Content-type', 'application/json');
        $I->sendGET('/logout/', [
            'token' => $this->token,
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
            'TOKEN' => $this->token,
        ]);
        $I->dontSeeInDatabase('api_user_session', [
            'TOKEN'    => $this->token,
            'FUSER_ID' => $fUserId,
        ]);
    }


    /**
     * @param ApiTester $I
     * @param Example   $example
     *
     * @dataprovider existLoginProvider
     */
    public function testExistLoginExist(ApiTester $I, Example $example)
    {
        $I->wantTo('Test exist user');

        $I->haveHttpHeader('Content-type', 'application/json');
        $I->sendGET('/login_exist/', [
            'token' => $this->token,
            'login' => $example['callback'](),
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

    public function existLoginProvider()
    {
        return [
            [
                'callback' => function () {
                    return $this->user['LOGIN'];
                },
            ],
            [
                'callback' => function () {
                    return $this->user['EMAIL'];
                },
            ],
            [
                'callback' => function () {
                    return $this->user['PERSONAL_PHONE'];
                },
            ],
        ];
    }

    public function testNonExistLoginExist(ApiTester $I)
    {
        $I->wantTo('Test exist user');

        $I->haveHttpHeader('Content-type', 'application/json');
        $I->sendGET('/login_exist/', [
            'token' => $this->token,
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
     * @param ApiTester $I
     * @param Example   $example
     *
     * @dataprovider validUpdateUserInfoProvider
     */
    public function testUpdateUserInfo(ApiTester $I, Example $example)
    {
        $I->wantTo('Test updating exist user');
        $I->login($this->token, $this->user['LOGIN'], $this->user['PASSWORD']);
        $I->haveHttpHeader('Content-type', 'application/json');
        $I->sendPOST('/user_info/', [
            'token' => $this->token,
            'user'  => $example['data'],
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'data'  => [
                'user' => 'array:!empty',
            ],
            'error' => 'array:empty',
        ]);
        $I->seeResponseContainsJson($example['data']);
    }

    public function validUpdateUserInfoProvider()
    {
        return [
            [
                'data' => [
                    'email'     => md5(random_bytes(1024)) . '@' . md5(random_bytes(1024)) . '.ru',
                    'firstname' => md5(random_bytes(1024)),
                    'lastname'  => md5(random_bytes(1024)),
                    'midname'   => md5(random_bytes(1024)),
                    'birthdate' => implode('.', [
                        str_pad(random_int(1, 26), 2, '0'),
                        str_pad(random_int(1, 12), 2, '0'),
                        random_int(1900, 2017),
                    ]),
                    'phone'     => random_int(70000000000, 79000000000),
                ],
            ],
            [
                'data' => [
                    'email'     => md5(random_bytes(1024)) . '@' . md5(random_bytes(1024)) . '.ru',
                    'firstname' => md5(random_bytes(1024)),
                    'lastname'  => md5(random_bytes(1024)),
                    'midname'   => md5(random_bytes(1024)),
                    'birthdate' => '',
                    'phone'     => random_int(70000000000, 79000000000),
                ],
            ],
        ];
    }
}
