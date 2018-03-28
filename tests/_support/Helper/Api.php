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
     * @param string $table
     * @param array  $criteria
     *
     * @throws \Codeception\Exception\ModuleException
     * @throws \Exception
     * @return array
     */
    public function grabColumnsFromDatabase(string $table, array $criteria = []): array
    {
        $query = $this->getDb()->driver->select('*', $table, $criteria);
        $parameters = array_values($criteria);
        $this->debugSection('Query', $query);
        if (!empty($parameters)) {
            $this->debugSection('Parameters', $parameters);
        }
        $sth = $this->getDb()->driver->executeQuery($query, $parameters);

        return $sth->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @param string $code
     * @param int    $minTypeId
     * @param int    $maxTypeId
     *
     * @throws \Codeception\Exception\ModuleException
     */
    public function isValidLocationType(string $code, int $minTypeId, int $maxTypeId): void
    {
        $this->getDb()->seeNumRecords(
            1,
            'b_sale_location',
            [
                'CODE'       => $code,
                'TYPE_ID >=' => $minTypeId,
                'TYPE_ID <=' => $maxTypeId,
            ]
        );
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

    /**
     * @throws \Codeception\Exception\ModuleException
     * @return Module|Module\Db
     */
    protected function getDb()
    {
        return $this->getModule('Db');
    }
}
