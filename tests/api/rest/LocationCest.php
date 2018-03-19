<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace api\rest;

use Codeception\Example;
use Codeception\Util\HttpCode;

class LocationCest
{
    /**
     * @param \ApiTester $apiTester
     * @param Example    $example
     *
     * @throws \Exception
     * @dataprovider goodMetroProvider
     */
    public function testMetro(\ApiTester $apiTester, Example $example): void
    {
        $token = $apiTester->createToken();
        $apiTester->wantTo('Check valid data');
        $apiTester->haveHttpHeader('Content-type', 'application/json');
        $apiTester->sendGET('/metro_stations/', [
            'token'   => $token,
            'city_id' => $example['city_id'],
        ]);
        $apiTester->seeResponseCodeIs(HttpCode::OK);
        $apiTester->seeResponseIsJson();
        $apiTester->seeResponseMatchesJsonType([
            'data'  => [
                'metro' => 'array:!empty',
            ],
            'error' => 'array:empty',
        ]);
        $apiTester->seeResponseMatchesJsonType([
            'id'    => 'integer:>0',
            'title' => 'string:!empty',
        ], '$.data.metro[0]');

        $apiTester->deleteToken($token);
    }

    /**
     * @return array
     */
    public function goodMetroProvider(): array
    {
        return [
            'Moscow' => [
                'city_id' => '0000073738',
            ],
        ];
    }

    /**
     * @param \ApiTester $apiTester
     * @param Example    $example
     *
     * @throws \Exception
     * @dataprovider badMetroProvider
     */
    public function testWrongMetro(\ApiTester $apiTester, Example $example): void
    {
        $token = $apiTester->createToken();
        $apiTester->wantTo('Check valid data');
        $apiTester->haveHttpHeader('Content-type', 'application/json');
        $apiTester->sendGET('/metro_stations/', [
            'token'   => $token,
            'city_id' => $example['city_id'],
        ]);
        $apiTester->seeResponseCodeIs($example['error'] === 1001 ? HttpCode::INTERNAL_SERVER_ERROR : HttpCode::OK);
        $apiTester->seeResponseIsJson();
        $apiTester->seeResponseMatchesJsonType([
            'data'  => 'array:empty',
            'error' => 'array:!empty',
        ]);
        $apiTester->seeResponseContainsJson([
            'error' => [
                [
                    'code' => $example['error'],
                ],
            ],
        ]);
    }

    public function badMetroProvider(): array
    {
        return [
            [
                'city_id' => '',
                'error'   => 3,
            ],
            [
                'city_id' => -123,
                'error'   => 3,
            ],
            [
                'city_id' => '123123123',
                'error'   => 44,
            ],
            [
                'city_id' => random_bytes(1024),
                'error'   => 1001,
            ],
        ];
    }
}
