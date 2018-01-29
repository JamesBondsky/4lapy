<?php

namespace FourPaws\SapBundle\Command;

use Exception;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\SapBundle\Pipeline\PipelineRegistry;
use FourPaws\SapBundle\Service\SapService;
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
    
    protected $debug    = false;
    
    protected $hasError = false;
    
    /**
     * @var PipelineRegistry
     */
    protected $pipelineRegistry;
    
    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected $container;
    
    /**
     * @param null $name
     *
     * @todo возможно ли вынести PipelineRegistry и SapService в конструктор?..
     *
     * @throws LogicException
     * @throws Exception
     * @throws \InvalidArgumentException
     * @throws ApplicationCreateException
     */
    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->setLogger(new Logger('Sap_exchange', [new StreamHandler(STDOUT, Logger::DEBUG)]));
    }

    /**
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws InvalidArgumentException
     */
    public function configure()
    {
        $this->container        = Application::getInstance()->getContainer();
        $this->pipelineRegistry = $this->container->get(PipelineRegistry::class);
        
        $this->setName('fourpaws:sap:import')
             ->setDescription('Sap exchange. Start exchange by type.')->addArgument(
                 self::ARGUMENT_PIPELINE,
                                                                                    InputArgument::REQUIRED,
                                                                                    sprintf(
                                                                                        'Pipeline. %s',
                                                                                            implode(
                                                                                                ', ',
                                                                                                    $this->pipelineRegistry->getCollection()
                                                                                                                           ->getKeys()
                                                                                            )
                                                                                    )
             );
    }
    
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws InvalidArgumentException
     * @return null
     *
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $available = $this->pipelineRegistry->getCollection()->getKeys();
        $pipeline  = $input->getArgument(self::ARGUMENT_PIPELINE);
        
        if (!\in_array($pipeline, $available, true)) {
            throw new InvalidArgumentException(sprintf(
                'Wrong pipeline %s, available: %s',
                                                       $pipeline,
                                                       implode(', ', $available)
            ));
        }
        
        try {
            $sapService = $this->container->get(SapService::class);
            $sapService->execute($pipeline);
            
            $this->logger->info(sprintf('%s`s exchange is done.', $pipeline));
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Unknown error: %s', $e->getMessage()));
        }
        
        return null;
    }
}
