<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace api\rest;

use Codeception\Example;
use Codeception\Util\HttpCode;

class CatalogCest
{
    /**
     * @dataprovider validCategoryProvider
     * @param \ApiTester $apiTester
     * @param Example    $example
     * @throws \Exception
     */
    public function testValidCategory(\ApiTester $apiTester, Example $example): void
    {
        $token = $apiTester->createToken();
        $apiTester->haveHttpHeader('Content-type', 'application/json');
        $apiTester->sendGET('/filter_list/', [
            'token' => $token,
            'id'    => $example['id'],
        ]);
        $apiTester->seeResponseCodeIs(HttpCode::OK);
        $apiTester->seeResponseIsJson();
        $apiTester->seeResponseMatchesJsonType([
            'data'  => 'array:!empty',
            'error' => 'array:empty',
        ]);
        $apiTester->seeResponseMatchesJsonType([
            'filter_list' => 'array:!empty',
        ], '$.data');

        $data = $apiTester->grabDataFromResponseByJsonPath('$.data.filter_list');
        $this->checkFilters(reset($data), $apiTester);
    }

    /**
     * @return array
     */
    protected function validCategoryProvider(): array
    {
        return [
            [
                'id' => 1,
            ],
            [
                'id' => 2,
            ],
        ];
    }

    protected function checkFilters(array $filters, \ApiTester $apiTester): void
    {
        foreach ($filters as $filter) {
            $apiTester->assertInternalType('string', $filter['id']);
            $apiTester->assertNotEmpty($filter['id']);

            $apiTester->assertInternalType('string', $filter['name']);
            $apiTester->assertNotEmpty($filter['name']);

            $apiTester->assertInternalType('array', $filter['values']);
            $apiTester->assertInternalType('int', $filter['min']);
            $apiTester->assertInternalType('int', $filter['max']);
            if ($filter['values']) {
                $this->checkValues($filter['values'], $apiTester);
                $apiTester->assertEquals(0, $filter['min']);
                $apiTester->assertEquals(0, $filter['max']);
            } else {
                $apiTester->assertGreaterOrEquals(0, $filter['min']);
                $apiTester->assertGreaterThan($filter['min'], $filter['max']);
            }
        }
    }

    protected function checkValues(array $values, \ApiTester $apiTester): void
    {
        foreach ($values as $value) {
            $apiTester->assertInternalType('string', $value['id']);
            $apiTester->assertNotEmpty($value['id']);

            $apiTester->assertInternalType('string', $value['name']);
            $apiTester->assertNotEmpty($value['name']);
        }
    }
}
