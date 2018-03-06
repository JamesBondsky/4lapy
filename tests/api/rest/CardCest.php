<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

use Codeception\Util\HttpCode;

class CardCest
{
    public function testActivatedCard(\ApiTester $apiTester)
    {
        $token = $apiTester->createToken();
        $user = $apiTester->createDummyUser();
        $card = $user['UF_DISCOUNT_CARD'] ?? null;
        if (!$card) {
            throw new RuntimeException('No card in user');
        }
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

        $apiTester->deleteToken($token);
        $apiTester->deleteDummyUser($user['ID']);
    }

    public function testNotActiveCard(ApiTester $apiTester)
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

        $apiTester->deleteToken($token);
    }
}
