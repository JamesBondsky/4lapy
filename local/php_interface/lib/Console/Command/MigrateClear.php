<?php

namespace FourPaws\Console\Command;

use FourPaws\Migrator\Entity\EntityTable;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MigrateClear
 *
 * @package FourPaws\Console\Command
 *
 * Очистка timestamp сущности
 */
class MigrateClear extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    
    const ARG_ENTITY = 'entity';
    
    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->setLogger(new Logger('Migrator', [new StreamHandler(STDOUT, Logger::DEBUG)]));
    }
    
    protected function configure()
    {
        /**
         * @todo переделать подсказку для addArgument на Reflection
         */
        $this->setName('migrate:clear')->setDescription('Migrate data via rest')->addArgument(self::ARG_ENTITY,
                                                                                              InputArgument::REQUIRED,
                                                                                              'Entity type, one or more of this: user, news, articles, shops, sale');
    }
    
    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return null
     *
     * @throws InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entity = $input->getArgument(self::ARG_ENTITY);
        
        $available = 'user, news, articles, catalog, shops, sale';
        
        if (!strpos($available, $entity)) {
            $this->logger->error(sprintf('Entity name must be one of it: %s.', $available));
            
            return null;
        }
        
        try {
            EntityTable::clearTimestampByEntity($entity);
            
            $this->logger->info(sprintf('Entity %s timestamp was clear.', $entity));
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Entity timestamp clear error: %s.', $e->getMessage()));
        }
        
        return null;
    }
}
