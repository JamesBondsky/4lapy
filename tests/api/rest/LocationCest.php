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
        $apiTester->wantTo('Test metro stations list');
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
        $apiTester->wantTo('Test invalid metro stations list');
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

    /**
     * @param \ApiTester $apiTester
     * @param Example    $example
     *
     * @dataprovider validCitySearchProvider
     * @throws \Exception
     */
    public function testCitySearch(\ApiTester $apiTester, Example $example): void
    {
        $token = $apiTester->createToken();
        $apiTester->wantTo('Test valid city search');
        $apiTester->haveHttpHeader('Content-type', 'application/json');
        $apiTester->sendGET('/city_search/', [
            'token'  => $token,
            'search' => $example['query'],
        ]);
        $apiTester->seeResponseCodeIs(HttpCode::OK);
        $apiTester->seeResponseIsJson();
        $apiTester->seeResponseMatchesJsonType([
            'data'  => 'array',
            'error' => 'array:empty',
        ]);

        $citiesData = $apiTester->grabDataFromResponseByJsonPath('$.data');
        $cities = reset($citiesData);

        foreach ($cities as $city) {
            $apiTester->assertInternalType('string', $city['id']);
            $apiTester->assertNotEmpty($city['id']);

            $apiTester->assertInternalType('string', $city['title']);
            $apiTester->assertNotEmpty($city['title']);

            $apiTester->assertInternalType('boolean', $city['has_metro']);
            $apiTester->assertInternalType('array', $city['path']);

            if ($city['path']) {
                foreach ((array)$city['path'] as $pathItem) {
                    $apiTester->assertInternalType('string', $pathItem);
                    $apiTester->assertNotEmpty($pathItem);
                }
            }

            $apiTester->isValidLocationType($city['id'], 3, 6);
        }
    }

    /**
     * @param \ApiTester $apiTester
     *
     * @throws \Exception
     */
    public function testEmptyCitySearch(\ApiTester $apiTester): void
    {
        $token = $apiTester->createToken();
        $apiTester->wantTo('Test empty city search');
        $apiTester->haveHttpHeader('Content-type', 'application/json');
        $apiTester->sendGET('/city_search/', [
            'token'  => $token,
            'search' => '',
        ]);
        $apiTester->seeResponseCodeIs(HttpCode::OK);
        $apiTester->seeResponseIsJson();
        $apiTester->seeResponseMatchesJsonType([
            'data'  => 'array:empty',
            'error' => 'array:!empty',
        ]);
        $apiTester->seeResponseContainsJson([
            'error' => [
                [
                    'code' => 3,
                ],
            ],
        ]);
    }

    /**
     * @param \ApiTester $apiTester
     *
     * @throws \Exception
     */
    public function testInvalidCitySearch(\ApiTester $apiTester): void
    {
        $token = $apiTester->createToken();
        $apiTester->wantTo('Test invalid city search');
        $apiTester->haveHttpHeader('Content-type', 'application/json');
        $apiTester->sendGET('/city_search/', [
            'token'  => $token,
            'search' => 'ДЦУАЛулджцаоУДЦЛАО',
        ]);
        $apiTester->seeResponseCodeIs(HttpCode::OK);
        $apiTester->seeResponseIsJson();
        $apiTester->seeResponseMatchesJsonType([
            'data'  => 'array:empty',
            'error' => 'array:empty',
        ]);
    }

    /**
     * @return array
     */
    protected function goodMetroProvider(): array
    {
        return [
            'Moscow' => [
                'city_id' => '0000073738',
            ],
        ];
    }

    protected function badMetroProvider(): array
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
        ];
    }

    protected function validCitySearchProvider(): array
    {
        return [
            [
                'query' => 'мос',
            ],
            [
                'query' => 'Москва',
            ],
            [
                'query' => 'Санкт',
            ],
            [
                'query' => 'Серпуховская',
            ],
        ];
    }
}
