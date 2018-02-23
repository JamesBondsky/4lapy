<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Module;
use Codeception\Step;
use Codeception\TestInterface;
use Codeception\Util\HttpCode;
use Exception;
use RuntimeException;

class Api extends Module
{
    /**
     * @var string
     */
    private $token = '';

    /**
     * {@inheritDoc}
     * @throws \Codeception\Exception\ModuleException
     */
    public function _beforeStep(Step $step)
    {
        parent::_beforeStep($step);
        $this->setTestsCookies();
    }

    /**
     * {@inheritDoc}
     * @throws \Exception
     */
    public function _afterStep(Step $step)
    {
        parent::_afterStep($step);
        $this->setTestsCookies();
    }

    /**
     * {@inheritDoc}
     * @throws \Exception
     */
    public function _after(TestInterface $test)
    {
        parent::_after($test);
        if ($this->token) {
            $this->removeToken();
        }
    }

    /**
     * @throws \Codeception\Exception\ModuleException
     */
    protected function setTestsCookies()
    {
        $phpBrowser = $this->getPhpBrowser();
        $phpBrowser->resetCookie('PHPSESSID');
        $phpBrowser->client->getCookieJar()->clear();
        $phpBrowser->setCookie('DEV', 'test');
    }

    /**
     * @throws Exception
     * @return string
     */
    public function getToken()
    {
        if (!$this->token) {
            $this->createToken();
        }
        return $this->token;
    }

    /**
     * @throws \Codeception\Exception\ModuleException
     * @return Module|Module\PhpBrowser
     */
    protected function getPhpBrowser()
    {
        return $this->getModule('PhpBrowser');
    }

    /**
     * @throws \Codeception\Exception\ModuleException
     * @return Module|Module\REST
     */
    protected function getRestModule()
    {
        return $this->getModule('REST');
    }

    /**
     * @throws \Codeception\Exception\ModuleException
     * @return Module|Module\Db
     */
    protected function getDbModule()
    {
        return $this->getModule('Db');
    }

    /**
     * @throws Exception
     */
    protected function createToken()
    {
        $rest = $this->getRestModule();
        $rest->haveHttpHeader('Content-type', 'application/json');
        $rest->sendGET('/start/');
        $rest->seeResponseCodeIs(HttpCode::OK);
        $rest->seeResponseIsJson();
        $rest->seeResponseMatchesJsonType([
            'data' => [
                'access_id' => 'string:!empty',
            ],
        ]);

        $data = $rest->grabDataFromResponseByJsonPath('$.data.access_id');

        if (!$data[0]) {
            throw new RuntimeException('No token was provided');
        }

        $this->getDbModule()->seeInDatabase('api_user_session', [
            'TOKEN' => $data[0],
        ]);

        $this->token = $data[0];
    }

    /**
     * @throws Exception
     */
    protected function removeToken()
    {
        $rest = $this->getRestModule();
        $rest->sendDELETE(sprintf('/fake/session/%s/', $this->getToken()));
        $rest->seeResponseCodeIs(HttpCode::OK);
        $this->getDbModule()->dontSeeInDatabase('api_user_session', [
            'TOKEN' => $this->getToken(),
        ]);
        $this->token = '';
    }
}
