<?php

namespace FourPaws\Migrator;

use Bitrix\Main\Application;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

final class Installer implements LoggerAwareInterface
{
    private $connection;
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    
    /**
     * Installer constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->connection = Application::getConnection();
        $this->setLogger($logger);
    }
    
    /**
     * Install tables
     */
    public function doInstall()
    {
        try {
            $this->createTables();
        } catch (InstallerException $e) {
            $this->getLogger()->error($e->getMessage(), $e->getTrace());
        }
    }

    /**
     * Uninstall tables
     */
    public function doUninstall()
    {
        try {
            $this->dropTables();
        } catch (InstallerException $e) {
            $this->getLogger()->error($e->getMessage(), $e->getTrace());
        }
    }
    
    /**
     * Install tables
     *
     * @throws \FourPaws\Migrator\InstallerException
     */
    public function createTables()
    {
        $query = <<<query
CREATE TABLE IF NOT EXISTS
  `adv_migrator_entity`
(
  `ID` INT NOT NULL AUTO_INCREMENT,
  `ENTITY` VARCHAR(32) NOT NULL,
  `TIMESTAMP` DATETIME NULL,
  `BROKEN` LONGTEXT
);

CREATE TABLE IF NOT EXISTS
  `adv_migrator_entity`
(
  `ID` INT NOT NULL AUTO_INCREMENT,
  `ENTITY_ID` INT NOT NULL,
  `INTERNAL_ID` INT,
  `EXTERNAL_ID` INT NOT NULL,
  `ACTIVE` CHAR(1) NOT NULL DEFAULT 'Y'
);

query;
        
        try {
            $this->connection->query($query);
        } catch (\Throwable $e) {
            throw new InstallerException($e->getMessage(), $e->getCode(), $e);
        }
    }
    
    /**
     * Drop installed tables
     *
     * @throws \FourPaws\Migrator\InstallerException
     */
    public function dropTables()
    {
        $query = <<<query
DROP TABLE IF EXISTS
  adv_migrator_entity,
  adv_migrator_map
query;
        
        try {
            $this->connection->query($query);
        } catch (\Throwable $e) {
            throw new InstallerException($e->getMessage(), $e->getCode(), $e);
        }
    }
    
    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger() : LoggerInterface
    {
        return $this->logger;
    }
    
    /**
     * @return bool
     */
    public function isInstalled() : bool {
        return $this->connection->query('SHOW TABLES LIKE `adv_migrator_%`')->getCount() === 2;
    }
}