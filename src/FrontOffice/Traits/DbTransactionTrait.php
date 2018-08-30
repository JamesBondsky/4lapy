<?php

namespace FourPaws\FrontOffice\Traits;

use Bitrix\Main\DB\Connection;
use Bitrix\Main\Db\SqlQueryException;

trait DbTransactionTrait
{
    /** @var Connection $dbConnection */
    private $dbConnection = false;

    /** @var bool $isTransactionStarted */
    private $isTransactionStarted = false;

    protected function getDbConnection()
    {
        if ($this->dbConnection === false) {
            $this->dbConnection = \Bitrix\Main\Application::getConnection();
        }

        return $this->dbConnection;
    }

    /**
     * @throws SqlQueryException
     */
    protected function startTransaction()
    {
        if (!$this->isTransactionStarted) {
            $connection = $this->getDbConnection();
            if ($connection) {
                $connection->startTransaction();
                $this->isTransactionStarted = true;
            }
        }
    }

    /**
     * @throws SqlQueryException
     */
    protected function rollbackTransaction()
    {
        if ($this->isTransactionStarted) {
            $connection = $this->getDbConnection();
            if ($connection) {
                $connection->rollbackTransaction();
                $this->isTransactionStarted = false;
            }
        }
    }

    /**
     * @throws SqlQueryException
     */
    protected function commitTransaction()
    {
        if ($this->isTransactionStarted) {
            $connection = $this->getDbConnection();
            if ($connection) {
                $connection->commitTransaction();
                $this->isTransactionStarted = false;
            }
        }
    }
}
