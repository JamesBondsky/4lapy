<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

class SessionCest
{
    public function createTest(ApiTester $I)
    {
        $I->wantTo('Create token');
        /** @noinspection PhpUnhandledExceptionInspection */
        $token = $I->createToken();
        $I->deleteToken($token);
    }
}
