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
                'firstname' => 'string',
                'lastname'  => 'string',
                'email'     => 'string:!empty',
            ],
            'error' => 'array:empty',
        ]);
        $I->seeResponseContainsJson([
            'data' => [
                'firstname' => $this->user['NAME'],
                'lastname'  => $this->user['LAST_NAME'],
                'email'     => $this->user['EMAIL'],
            ],
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
            'data'  => 'array',
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
}
