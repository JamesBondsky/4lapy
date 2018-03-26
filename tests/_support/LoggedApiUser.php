<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace _support;

use _generated\ApiTesterActions;
use ApiTester;
use Codeception\Scenario;

class LoggedApiUser extends ApiTester
{
    use ApiTesterActions;

    protected $token = '';

    protected $user = [];

    /**
     * LoggedApiUser constructor.
     * @param Scenario $scenario
     * @throws \Exception
     */
    public function __construct(Scenario $scenario)
    {
        parent::__construct($scenario);
        $this->token = $this->createToken();
        $this->user = $this->createDummyUser();
        $this->login((int)$this->user['ID'], $this->token);
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return array|mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    public function getUserId(): int
    {
        return (int)$this->getField('ID');
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getField(string $name)
    {
        return $this->user[$name] ?? '';
    }
}
