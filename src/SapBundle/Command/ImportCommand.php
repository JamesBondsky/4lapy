<?php

namespace FourPaws\SapBundle\Command;

use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SapCommand
 *
 * @package FourPaws\SapBundle\Command
 */
class ImportCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    
    const ARGUMENT_PIPELINE = 'pipeline';
    
    protected $debug = false;
    
    protected $hasError = false;
    
    /**
     * @param null $name
     *
     * @throws LogicException
     * @throws Exception
     * @throws \InvalidArgumentException
     */
    public function __construct($name = null) {
        parent::__construct($name);
        $this->setLogger(new Logger('Sap_exchange', [new StreamHandler(STDOUT, Logger::DEBUG)]));
    }
    
    /**
     * @throws InvalidArgumentException
     */
    public function configure() {
        /**
         * @todo get pipelines from configuration
         */
        $pipelines = ['catalog', 'order_status', 'delivery_schedule'];
        
        $this->setName('fourpaws:sap:import')
             ->setDescription('Sap exchange. Start exchange by type.')
             ->addArgument(self::ARGUMENT_PIPELINE, InputArgument::REQUIRED,
                           sprintf('Pipeline. %s', implode(', ', $pipelines)));
    }
    
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return null
     *
     * @throws InvalidArgumentException
     */
    public function execute(InputInterface $input, OutputInterface $output) {
        $pipeline = $input->getArgument(self::ARGUMENT_PIPELINE);
        
        /**
         * @todo check type; or factory
         */
        if (\in_array($pipeline, [], true)) {
            throw new InvalidArgumentException('Wrong pipeline');
        }
        
        try {
            /**
             * @todo implement command execute
             */
            $this->logger->info(sprintf('%s`s exchange is done.', $pipeline));
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Unknown error: %s', $e->getMessage()));
        }
        
        return null;
    }
}
