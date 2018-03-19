<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

use Codeception\Util\HttpCode;

class StoreControllerCest
{
    /**
     * @param ApiTester            $I
     * @param \Codeception\Example $example
     *
     * @dataprovider validDataProvider
     * @throws Exception
     */
    public function testValidGet(ApiTester $I, \Codeception\Example $example): void
    {
        $I->wantTo('Test valid store data');
        $I->haveHttpHeader('Content-type', 'application/json');
        $params = $example['params'] ?? [];
        $params['token'] = $I->createToken();

        $I->sendGET('/shop_list/', $params);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType(['data' => 'array', 'error' => 'array']);
        $I->seeResponseMatchesJsonType(['shops' => 'array'], '$.data');
        $I->seeResponseMatchesJsonType([
            'city_id'             => 'string:!empty',
            'title'               => 'string:!empty',
            'picture'             => 'string',
            'details'             => 'string',
            'lat'                 => 'string:!empty',
            'lon'                 => 'string:!empty',
            'metro_name'          => 'string',
            'metro_color'         => 'string',
            'worktime'            => 'string',
            'address'             => 'string:!empty',
            'phone'               => 'string',
            'phone_ext'           => 'string',
            'url'                 => 'string:url',
            'service'             => 'array',
            'availability_status' => 'string',
        ], '$.data.shops[0]');
        $I->seeResponseMatchesJsonType([
            'image' => 'string',
            'title' => 'string:!empty',
        ], '$.data.shops[0].service[0]');
        if (null !== $example['count'] ?? null) {
            $I->assertCount($example['count'], $I->grabDataFromResponseByJsonPath('$.data.shops[*]'));
        }
    }

    /**
     * @return array
     */
    protected function validDataProvider(): array
    {
        return [
            [
                'params' => ['city_id' => '0000230626'],
                'count'  => 2,
            ],
            [
                'params' => ['city_id' => ''],
                'count'  => null,
            ],
            [
                'params' => [],
                'count'  => null,
            ],
            [
                'params' => [
                    'city_id'       => '0000073738',
                    'metro_station' => [1, 16, 5],
                ],
                'count'  => null,
            ],
            [
                'params' => [
                    'city_id' => '0000230626',
                    'lat'     => 56.839198,
                    'lon'     => 35.931686,
                ],
                'count'  => 2,
            ],
        ];
    }

    /**
     * @param ApiTester            $I
     * @param \Codeception\Example $example
     *
     * @dataprovider invalidDataProvider
     * @throws Exception
     */
    public function testInvalidGet(ApiTester $I, \Codeception\Example $example): void
    {
        $I->wantTo('Test invalid store data');
        $I->haveHttpHeader('Content-type', 'application/json');
        $params = $example['params'] ?? [];
        $params['token'] = $I->createToken();

        $I->sendGET('/shop_list/', $params);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'data'  => [
                'shops' => 'array:!empty',
            ],
            'error' => 'array:empty',
        ]);
        if (null !== $example['count'] ?? null) {
            $I->assertCount($example['count'], $I->grabDataFromResponseByJsonPath('$.data.shops[*]'));
        }
    }

    /**
     * @return array
     */
    protected function invalidDataProvider(): array
    {
        return [
            [
                'params' => [
                    'city_id'       => '0000230626',
                    'metro_station' => [1, 16, 5],
                ],
                'count'  => 2,
            ],
        ];
    }
}
