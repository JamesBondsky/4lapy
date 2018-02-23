<?php


class SessionCest
{
    /**
     * @param ApiTester $I
     *
     * @throws Exception
     */
    public function createTest(ApiTester $I)
    {
        $I->wantTo('Check session created');
        $I->haveHttpHeader('Content-type', 'application/json');
        $I->sendGET('/start/');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'data' => [
                'access_id' => 'string:!empty',
            ],
        ]);
        $data = $I->grabDataFromResponseByJsonPath('$.data.access_id');
        $I->seeInDatabase('api_user_session', [
            'TOKEN' => $data[0],
        ]);
        $I->removeToken($data[0]);
    }
}
