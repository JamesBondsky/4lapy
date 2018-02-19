<?php

/**  */

namespace FourPaws\Test\MobileApi\Functional;

use FourPaws\MobileApiBundle\Repository\ApiUserSessionRepository;
use FourPaws\MobileApiBundle\Services\Security\FakeTokenGenerator;
use FourPaws\MobileApiBundle\Tables\ApiUserSessionTable;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Test generate and verify captcha for phone and email
 *
 * Class BaseTest
 *
 * @package FourPaws\Tests\MobileApi\Functional
 */
abstract class BaseTest extends WebTestCase
{
    /**
     * @var bool
     */
    protected $backupGlobals = false;

    /**
     * @var string
     */
    protected $token = '';

    /**
     * @throws \Exception
     */
    public function setUp()
    {
        $this->clearToken();
        $this->createToken();
        parent::setUp();
    }

    /**
     * @return bool
     */
    public function createToken(): bool
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/mobile_app_v2/start/');

        $response = $client->getResponse();

        if ($response) {
            $content = $response->getContent();
            $data = json_decode($content, true);

            $this->token = $data['data']['access_id'];
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @throws \Exception
     */
    public function tearDown()
    {
        $this->clearToken();
        parent::tearDown();
    }

    /**
     * @throws \Exception
     */
    protected function clearToken()
    {
        $result = ApiUserSessionTable::query()
            ->addFilter(ApiUserSessionRepository::FIELD_TOKEN, (new FakeTokenGenerator())->generate())
            ->addSelect('ID')
            ->exec()
            ->fetch();

        /**
         * @var array $result
         */
        if (\is_array($result) && $result['ID']) {
            ApiUserSessionTable::delete($result['ID']);
        }
    }
}