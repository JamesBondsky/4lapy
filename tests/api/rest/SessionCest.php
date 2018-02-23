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
        $I->getToken();
    }
}
