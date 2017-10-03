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
        $queries = [
            'CREATE TABLE IF NOT EXISTS adv_migrator_map
(
  ID INT NOT NULL AUTO_INCREMENT,
  ENTITY VARCHAR(32) NOT NULL,
  INTERNAL_ID INT,
  EXTERNAL_ID INT NOT NULL,
  PRIMARY KEY (ID),
  INDEX internal_entity (ENTITY),
  INDEX internal_entity_index (ENTITY, EXTERNAL_ID),
  INDEX internal_index (INTERNAL_ID),
  INDEX external_index (EXTERNAL_ID)
);',
            'CREATE TABLE IF NOT EXISTS adv_migrator_entity
(
  ENTITY VARCHAR(32) NOT NULL,
  TIMESTAMP DATETIME NULL,
  BROKEN LONGTEXT,
  PRIMARY KEY (ENTITY)
);',
            'CREATE TABLE IF NOT EXISTS adv_migrator_lazy
(
  ENTITY_FROM VARCHAR(32) NOT NULL,
  ENTITY_TO VARCHAR(32) NOT NULL,
  FIELD VARCHAR(64) NOT NULL,
  INTERNAL_ID INT,
  EXTERNAL_ID INT NOT NULL,
  PRIMARY KEY (ENTITY_FROM, ENTITY_TO, FIELD, INTERNAL_ID),
  INDEX internal_index (ENTITY_FROM, INTERNAL_ID),
  INDEX external_index (ENTITY_TO, EXTERNAL_ID)
);',
        ];
        
        try {
            foreach ($queries as $query) {
                $this->connection->query($query);
            }
        } catch (\Throwable $e) {
            $this->dropTables();
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
  adv_migrator_map,
  adv_migrator_lazy
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
    public function isInstalled() : bool
    {
        return $this->connection->query('SHOW TABLES LIKE \'adv_migrator_%\'')->getSelectedRowsCount() === 3;
    }
}