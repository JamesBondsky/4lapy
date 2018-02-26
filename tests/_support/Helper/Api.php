<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Module;
use Codeception\Step;

class Api extends Module
{
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
     * @throws \Codeception\Exception\ModuleException
     * @return Module|Module\PhpBrowser
     */
    protected function getPhpBrowser()
    {
        return $this->getModule('PhpBrowser');
    }
}
