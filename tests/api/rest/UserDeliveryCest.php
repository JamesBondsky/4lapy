<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace api\rest;

use _support\LoggedApiUser;
use Codeception\Util\HttpCode;

class UserDeliveryCest
{
    /**
     * @param LoggedApiUser $I
     * @throws \Codeception\Exception\ModuleException
     * @throws \Exception
     */
    public function testGet(LoggedApiUser $I): void
    {
        $firstAddress = $I->createAddress($I->getUserId());
        $secondAddress = $I->createAddress($I->getUserId());

        $I->wantTo('Test get list of addresses');
        $I->haveHttpHeader('Content-type', 'application/json');
        $I->sendGET('/delivery_address/', [
            'token' => $I->getToken(),
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'data'  => [
                'address' => 'array:!empty',
            ],
            'error' => 'array:empty',
        ]);

        $addressesData = $I->grabDataFromResponseByJsonPath('$.data.address');
        $addresses = reset($addressesData);

        $I->assertCount(2, $addresses);

        foreach ($addresses as $address) {
            $I->assertInternalType('int', $address['id']);
            $I->assertGreaterThan(0, $address['id']);

            $I->assertInternalType('string', $address['title']);
            $I->assertNotEmpty($address['title']);

            $I->assertInternalType('array', $address['city']);
            $I->assertInternalType('string', $address['street_name']);
            $I->assertInternalType('string', $address['house']);
            $I->assertInternalType('string', $address['flat']);
            $I->assertInternalType('string', $address['details']);
        }

        foreach ([$firstAddress, $secondAddress] as $i => $dbFieldsAddress) {
            $I->assertEquals($dbFieldsAddress['ID'], $addresses[$i]['id']);
            $I->assertEquals($dbFieldsAddress['UF_NAME'], $addresses[$i]['title']);
            $I->assertEquals($dbFieldsAddress['UF_STREET'], $addresses[$i]['street_name']);
            $I->assertEquals($dbFieldsAddress['UF_HOUSE'], $addresses[$i]['house']);
            $I->assertEquals($dbFieldsAddress['UF_FLAT'], $addresses[$i]['flat']);
        }
    }
}
