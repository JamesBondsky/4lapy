<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

use Codeception\Util\HttpCode;

class CardCest
{
    /**
     * @param ApiTester $apiTester
     * @throws Exception
     * @throws \Codeception\Exception\ModuleException
     */
    public function testActivatedCard(\ApiTester $apiTester): void
    {
        $token = $apiTester->createToken();
        $user = $apiTester->createDummyUser();
        $card = $apiTester->getCard($user['ID']);
        $apiTester->haveHttpHeader('Content-type', 'application/json');
        $apiTester->sendGET('/card_activated/', [
            'token'  => $token,
            'number' => $card,
        ]);
        $apiTester->seeResponseCodeIs(HttpCode::OK);
        $apiTester->seeResponseIsJson();
        $apiTester->seeResponseMatchesJsonType([
            'data'  => 'array:!empty',
            'error' => 'array:!empty',
        ]);

        $apiTester->seeResponseContainsJson([
            'data'  => [
                'activated' => true,
            ],
            'error' => [
                [
                    'code' => 42,
                ],
            ],
        ]);
    }

    /**
     * @param ApiTester $apiTester
     * @throws Exception
     */
    public function testNotActiveCard(ApiTester $apiTester): void
    {
        $token = $apiTester->createToken();

        $apiTester->haveHttpHeader('Content-type', 'application/json');
        $apiTester->sendGET('/card_activated/', [
            'token'  => $token,
            'number' => '2600011122233',
        ]);
        $apiTester->seeResponseCodeIs(HttpCode::OK);
        $apiTester->seeResponseIsJson();
        $apiTester->seeResponseMatchesJsonType([
            'data'  => 'array:!empty',
            'error' => 'array:empty',
        ]);

        $apiTester->seeResponseContainsJson([
            'data' => [
                'activated' => false,
            ],
        ]);
    }
}
