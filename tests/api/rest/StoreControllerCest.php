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
     * @throws \_generated\Exception
     */
    public function testValidGet(ApiTester $I, \Codeception\Example $example)
    {
        $I->wantTo('Test valid store data');
        $I->haveHttpHeader('Content-type', 'application/json');
        $params = $example['params'] ?? [];
        $params['token'] = $I->getToken();

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
    }

    public function validDataProvider()
    {
        return [
            [
                'params' => ['city_id' => '0000230626'],
                'count'  => 2,
            ],
            [
                'params' => ['city_id' => ''],
            ],
            [
                'params' => [],
            ],
            [
                'params' => [
                    'city_id'       => '0000073738',
                    'metro_station' => [1, 16, 5],
                ],
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
     * @throws \_generated\Exception
     */
    public function testInvalidGet(ApiTester $I, \Codeception\Example $example)
    {
        $I->wantTo('Test invalid store data');
        $I->haveHttpHeader('Content-type', 'application/json');
        $params = $example['params'] ?? [];
        $params['token'] = $I->getToken();

        $I->sendGET('/shop_list/', $params);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType(['data' => 'array', 'error' => 'array']);
        $I->seeResponseMatchesJsonType([
            'code' => 'string:=44',
        ], '$.error[0]');
    }

    public function invalidDataProvider()
    {
        return [
            [
                'params' => [
                    'city_id'       => '0000230626',
                    'metro_station' => [1, 16, 5],
                ],
            ],
        ];
    }
}
