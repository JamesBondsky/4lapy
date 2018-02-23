<?php

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Module;
use Codeception\Step;

class Api extends Module
{
    public function _beforeStep(Step $step)
    {
        parent::_beforeStep($step);
        $phpBrowser = $this->getPhpBrowser();
        $phpBrowser->resetCookie('PHPSESSID');
        $phpBrowser->client->getCookieJar()->clear();
        $phpBrowser->setCookie('DEV', 'test');
    }

    /**
     * @return Module\PhpBrowser
     * @throws \Codeception\Exception\ModuleException
     */
    protected function getPhpBrowser()
    {
        return $this->getModule('PhpBrowser');
    }
}
