<?php

namespace FourPaws\Test\MobileApi\Functional;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StoreControllerTest extends BaseTest
{
    public function testOnlyCity()
    {
        //Тверь - 2 магазина
        $cityId = '0000230626';
        $params = [
            'token'   => $this->getToken(),
            'city_id' => $cityId,
        ];

        $this->startTest($params);
    }

    public function testEmptyCity()
    {
        /** должен вернуть все магазины */
        $params = [
            'token'   => $this->getToken(),
            'city_id' => '',
        ];

        $this->startTest($params);
    }

    public function testEmpty()
    {
        /** должен вернуть все магазины */
        $params = [
            'token' => $this->getToken(),
        ];

        $this->startTest($params);
    }

    public function testWrongCity()
    {
        $cityId = '000023062624324';
        $params = [
            'token'   => $this->getToken(),
            'city_id' => $cityId,
        ];

        $this->startTest($params);
    }

    public function testMetro()
    {
        //Москва - 50 магазинов
        $cityId = '0000073738';
        //выбираем 3 метро
        $metro = [1, 16, 5];
        $params = [
            'token'         => $this->getToken(),
            'city_id'       => $cityId,
            'metro_station' => $metro,
        ];

        $this->startTest($params);
    }

    public function testMetroByNoneMetroCity()
    {
        /** должно выбить error 44 */
        //Тверь - 2 магазина
        $cityId = '0000230626';
        //выбираем 3 метро
        $metro = [1, 16, 5];
        $params = [
            'token'         => $this->getToken(),
            'city_id'       => $cityId,
            'metro_station' => $metro,
        ];

        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/mobile_app_v2/shop_list/', $params);

        $response = $client->getResponse();

        if ($response) {
            $content = $response->getContent();
            static::assertEquals(Response::HTTP_OK, $response->getStatusCode());
            static::assertJson($content);
            $data = json_decode($content, true);
            static::assertInternalType('array', $data);
            static::assertTrue($response->isClientError());
            static::assertInternalType('array', $data['error']);
            static::assertArrayHasKey('error', $data);
            if (\is_array($data['error']) && !empty($data['error'])) {
                foreach ($data['error'] as $error) {
                    static::assertArrayHasKey('code', $error);
                    static::assertArrayHasKey('title', $error);
                    static::assertEquals(44, (int)$error['code']);
                }
            }
        }
    }

    public function testDistance()
    {
        //Тверь - 2 магазина
        $cityId = '0000230626';
        //Координаты площади Терешковой в Твери
        $params = [
            'token'   => $this->getToken(),
            'city_id' => $cityId,
            'lat'     => 56.839198,
            'lon'     => 35.931686,
        ];

        $this->startTest($params);
    }

    private function startTest(array $params)
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/mobile_app_v2/shop_list/', $params);

        $response = $client->getResponse();

        $this->doTestResponse($response);
    }

    private function doTestShopsStructure(array $shops)
    {
        static::assertInternalType('array', $shops);
        if (\is_array($shops) && !empty($shops)) {
            foreach ($shops as $shop) {
                static::assertInternalType('array', $shop);
                static::assertNotEmpty($shop);
                $this->doTestShopStructure($shop);
            }
        }
    }

    private function doTestShopStructure(array $shop)
    {
        static::assertArrayHasKey('city_id', $shop);
        static::assertNotEmpty($shop['city_id']);
        static::assertArrayHasKey('title', $shop);
        static::assertArrayHasKey('picture', $shop);
        static::assertArrayHasKey('details', $shop);
        static::assertArrayHasKey('lat', $shop);
        static::assertNotEmpty($shop['lat']);
        static::assertArrayHasKey('lon', $shop);
        static::assertNotEmpty($shop['lon']);
        static::assertArrayHasKey('metro_name', $shop);
        static::assertArrayHasKey('metro_color', $shop);
        static::assertArrayHasKey('worktime', $shop);
        static::assertArrayHasKey('address', $shop);
        static::assertNotEmpty($shop['address']);
        static::assertArrayHasKey('phone', $shop);
        static::assertArrayHasKey('phone_ext', $shop);
        static::assertArrayHasKey('url', $shop);
        static::assertArrayHasKey('availability_status', $shop);
        static::assertArrayHasKey('service', $shop);
        static::assertInternalType('array', $shop['service']);
        $countShopService = \count($shop['service']);
        if($countShopService > 0) {
            $this->doTestServicesStructure($shop['service']);
        }
    }

    private function doTestServicesStructure(array $services)
    {
        if (\is_array($services) && !empty($services)) {
            foreach ($services as $service) {
                static::assertInternalType('array', $service);
                static::assertNotEmpty($service);
                $this->doTestServiceStructure($service);
            }
        }
    }

    private function doTestServiceStructure(array $service)
    {
        static::assertArrayHasKey('image', $service);
        static::assertArrayHasKey('title', $service);
        static::assertNotEmpty($service['title']);
    }

    private function doTestResponse(Response $response)
    {
        if ($response) {
            $content = $response->getContent();
            static::assertEquals(Response::HTTP_OK, $response->getStatusCode());
            static::assertJson($content);
            $data = json_decode($content, true);
            static::assertInternalType('array', $data);
            static::assertCount(0, $data['error']);
            static::assertArrayHasKey('data', $data);
            static::assertInternalType('array', $data['data']);
            static::assertArrayHasKey('shops', $data['data']);
            static::assertInternalType('array', $data['data']['shops']);
            $shopsCount = \count($data['data']['shops']);
            if($shopsCount > 0) {
                $this->doTestShopsStructure($data['data']['shops']);
            }
        }
    }
}
