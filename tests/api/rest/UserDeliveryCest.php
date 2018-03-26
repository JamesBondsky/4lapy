<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace api\rest;

use _support\LoggedApiUser;
use Codeception\Example;
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
            $I->assertEquals($dbFieldsAddress['UF_DETAILS'], $addresses[$i]['details']);
        }
    }

    /**
     * @param LoggedApiUser $I
     * @param Example       $example
     * @dataprovider createProvider
     */
    public function testCreate(LoggedApiUser $I, Example $example)
    {
        $I->wantTo('Test create address');
        $I->haveHttpHeader('Content-type', 'application/json');

        $I->sendPUT('/delivery_address/', [
            'token'   => $I->getToken(),
            'address' => $example['address'],
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'data'  => [
                'feedback_text' => 'string',
            ],
            'error' => 'array:empty',
        ]);
    }

    protected function createProvider()
    {
        return [
            [
                'address' => [
                    'title'       => md5(random_bytes(1024)),
                    'city'        => [
                        'id' => '0000073738',
                    ],
                    'street_name' => md5(random_bytes(1024)),
                    'house'       => md5(random_bytes(1024)),
                    'flat'        => md5(random_bytes(1024)),
                    'details'     => md5(random_bytes(1024)),
                ],
            ],
        ];
    }

    /**
     * @param LoggedApiUser $I
     * @throws \Codeception\Exception\ModuleException
     * @throws \Exception
     */
    public function testUpdate(LoggedApiUser $I): void
    {
        $address = $I->createAddress($I->getUserId());

        $newDetails = md5(random_bytes(1024));

        $I->wantTo('Test update address');
        $I->haveHttpHeader('Content-type', 'application/json');
        $I->sendPOST('/delivery_address/', [
            'token'   => $I->getToken(),
            'address' => [
                'id'          => $address['ID'],
                'title'       => $address['UF_NAME'],
                'city'        => [
                    'id' => $address['UF_CITY_LOCATION'],
                ],
                'street_name' => $address['UF_STREET'],
                'house'       => $address['UF_HOUSE'],
                'flat'        => $address['UF_FLAT'],
                'details'     => $newDetails,
            ],
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'data'  => [
                'feedback_text' => 'string',
            ],
            'error' => 'array:empty',
        ]);

        $I->seeInDatabase('adv_adress', [
            'ID'         => $address['ID'],
            'UF_DETAILS' => $newDetails,
        ]);
    }

    /**
     * @param LoggedApiUser $I
     * @throws \Codeception\Exception\ModuleException
     * @throws \Exception
     */
    public function testDelete(LoggedApiUser $I): void
    {
        $address = $I->createAddress($I->getUserId());

        $I->wantTo('Test delete address');
        $I->haveHttpHeader('Content-type', 'application/json');
        $I->sendDELETE('/delivery_address/', [
            'token' => $I->getToken(),
            'id'    => $address['ID'],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'data'  => [
                'feedback_text' => 'string',
            ],
            'error' => 'array:empty',
        ]);

        $I->dontSeeInDatabase('adv_adress', [
            'ID' => $address['ID'],
        ]);
    }

    /**
     * @param \ApiTester $I
     * @throws \Exception
     */
    public function testHackGet(\ApiTester $I): void
    {
        $I->wantTo('Test trying to get address without logged');
        $I->haveHttpHeader('Content-type', 'application/json');
        $I->sendGET('/delivery_address/', [
            'token' => $I->createToken(),
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'data'  => 'array:empty',
            'error' => 'array:!empty',
        ]);
        $I->assertContainsError(9);
    }

    /**
     * @param LoggedApiUser $I
     * @throws \Codeception\Exception\ModuleException
     * @throws \Exception
     */
    public function testHackUpdate(LoggedApiUser $I): void
    {
        $address = $I->createAddress(1);

        $I->wantTo('Test trying to update not user address');
        $I->haveHttpHeader('Content-type', 'application/json');
        $I->sendPOST('/delivery_address/', [
            'token'   => $I->getToken(),
            'address' => [
                'id'          => $address['ID'],
                'title'       => $address['UF_NAME'],
                'city'        => [
                    'id' => $address['UF_CITY_LOCATION'],
                ],
                'street_name' => $address['UF_STREET'],
                'house'       => $address['UF_HOUSE'],
                'flat'        => $address['UF_FLAT'],
                'details'     => md5(random_bytes(1024)),
            ],
        ]);

        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'data'  => 'array:empty',
            'error' => 'array:!empty',
        ]);
        $I->assertContainsError(401);
    }

    /**
     * @param LoggedApiUser $I
     * @throws \Codeception\Exception\ModuleException
     * @throws \Exception
     */
    public function testHackDelete(LoggedApiUser $I): void
    {
        $address = $I->createAddress(1);

        $I->wantTo('Test trying to delete not user address');
        $I->haveHttpHeader('Content-type', 'application/json');
        $I->sendDELETE('/delivery_address/', [
            'token' => $I->getToken(),
            'id'    => $address['ID'],
        ]);

        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'data'  => 'array:empty',
            'error' => 'array:!empty',
        ]);
        $I->assertContainsError(401);

        $I->canSeeInDatabase('adv_adress', [
            'ID' => $address['ID'],
        ]);
    }
}
