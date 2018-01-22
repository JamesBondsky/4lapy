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
class SapCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    
    const ARGUMENT_TYPE = 'type';
    
    protected $debug    = false;
    
    protected $hasError = false;
    
    /**
     * @param null $name
     *
     * @throws LogicException
     * @throws Exception
     * @throws \InvalidArgumentException
     */
    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->setLogger(new Logger('Sap', [new StreamHandler(STDOUT, Logger::DEBUG)]));
    }
    
    /**
     * @throws InvalidArgumentException
     */
    public function configure()
    {
        $this->setName('fourpaws:sap:exchange')
            ->setDescription('Sap exchange. Start exchange by type.')
            ->addArgument(self::ARGUMENT_TYPE,
                          InputArgument::REQUIRED,
                          'Exchange type, one of this: offers, price');
    }
    
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return null
     *
     * @throws InvalidArgumentException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $type   = $input->getArgument(self::ARGUMENT_TYPE);
    
        /**
         * @todo check type; or factory
         */
        if (\in_array($type, [], true)) {
            throw new InvalidArgumentException('');
        }
        
        try {
            /**
             * @todo implement command execute
             */
            $this->logger->info(sprintf('%s`s exchange is done.', $type));
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Unknown error: %s', $e->getMessage()));
        }
        
        return null;
    }
}
