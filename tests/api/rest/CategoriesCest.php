<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace api\rest;

use Codeception\Example;
use Codeception\Util\HttpCode;

class CategoriesCest
{
    /**
     * @param \ApiTester $apiTester
     *
     * @param Example    $example
     *
     * @throws \Exception
     * @dataprovider goodCategoryProvider
     */
    public function testGoodCategory(\ApiTester $apiTester, Example $example)
    {
        $token = $apiTester->createToken();

        $apiTester->wantTo('Test good categories');
        $apiTester->haveHttpHeader('Content-type', 'application/json');

        $data = $example['callback']($apiTester);

        $category = $data[array_rand($data, 1)];

        $apiTester->sendGET('/categories/', [
            'token' => $token,
            'id'    => $category,
        ]);

        $apiTester->seeResponseCodeIs(HttpCode::OK);
        $apiTester->seeResponseIsJson();

        $apiTester->seeResponseMatchesJsonType([
            'data'  => [
                'categories' => 'array:!empty',
            ],
            'error' => 'array:empty',
        ]);

        $apiTester->seeResponseMatchesJsonType([
            'title'     => 'string:!empty',
            'picture'   => 'string',
            'child'     => 'array:!empty',
            'has_child' => 'boolean',
        ], '$.data.categories[0]');

        $apiTester->deleteToken($token);
    }

    public function goodCategoryProvider(): array
    {
        return [
            [
                'callback' => function (\ApiTester $apiTester) {
                    return [''];
                },
            ],
            [
                'callback' => function (\ApiTester $apiTester) {
                    return $apiTester->grabColumnFromDatabase('b_iblock_section', 'ID', [
                        'IBLOCK_ID'     => 2,
                        'DEPTH_LEVEL'   => 1,
                        'ACTIVE'        => 'Y',
                        'GLOBAL_ACTIVE' => 'Y',
                    ]);
                },
            ],
        ];
    }

    public function testBadCategory(\ApiTester $apiTester)
    {
        $token = $apiTester->createToken();

        $apiTester->wantTo('Test bad categories');
        $apiTester->haveHttpHeader('Content-type', 'application/json');

        $apiTester->sendGET('/categories/', [
            'token' => $token,
            'id'    => '123123',
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
                    'code' => 404,
                ],
            ],
        ]);

        $apiTester->deleteToken($token);
    }
}
