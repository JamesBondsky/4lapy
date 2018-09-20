<?php

namespace FourPaws\SaleBundle\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ObjectException;
use FourPaws\SaleBundle\Entity\ForgotBasket as ForgotBasketEntity;
use FourPaws\SaleBundle\Exception\ForgotBasket\UnknownTypeException;
use FourPaws\SaleBundle\Service\ForgotBasketService;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ForgotBasket extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    protected const ARG_TYPE = 'type';

    /**
     * @var ForgotBasketService
     */
    protected $forgotBasketService;

    /**
     * ForgotBasket constructor.
     *
     * @param null                $name
     * @param ForgotBasketService $forgotBasketService
     *
     * @throws LogicException
     */
    public function __construct($name = null, ForgotBasketService $forgotBasketService)
    {
        parent::__construct($name);
        $this->forgotBasketService = $forgotBasketService;
    }

    /**
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    protected function configure(): void
    {
        $this->setName('fourpaws:sale:forgotbasket:send')
             ->setDescription('Send "forgot basket" messages')
             ->addArgument(
                 static::ARG_TYPE,
                 InputArgument::REQUIRED,
                 'Message type'
             );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws InvalidArgumentException
     * @throws UnknownTypeException
     * @throws \RuntimeException
     * @throws ObjectException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $type = $input->getArgument(static::ARG_TYPE);

        $tasks = $this->forgotBasketService->getActiveTasks($type, true);
        /** @var ForgotBasketEntity $task */
        foreach ($tasks as $task) {
            try {
                $this->forgotBasketService->executeTask($task);
            } catch (\Exception $e) {
                $this->log()->error(
                    \sprintf(
                        'Failed to send "%s" forgot basket message to user #%s: %s: %s',
                        $task->getType(),
                        $task->getUserId(),
                        \get_class($e),
                        $e->getMessage()
                    )
                );
            }
        }
    }
}
