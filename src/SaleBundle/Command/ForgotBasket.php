<?php

namespace FourPaws\SaleBundle\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\SystemException;
use FourPaws\External\Exception\ExpertsenderBasketEmptyException;
use FourPaws\External\Exception\ExpertsenderEmptyEmailException;
use FourPaws\SaleBundle\Entity\ForgotBasket as ForgotBasketEntity;
use FourPaws\SaleBundle\Exception\ForgotBasket\UnknownTypeException;
use FourPaws\SaleBundle\Service\ForgotBasketService;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ForgotBasket extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    protected const OPT_TYPE = 'type';

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
        $this->setName('fourpaws:sale:forgotbasket:execute')
             ->setDescription('Send "forgot basket" messages')
             ->addOption(
                static::OPT_TYPE,
                't',
                InputOption::VALUE_REQUIRED,
                'Type of message'
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws InvalidArgumentException
     * @throws SystemException
     * @throws UnknownTypeException
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $type = $input->getOption(static::OPT_TYPE);

        $tasks = $this->forgotBasketService->getActiveTasks($type, true);
        /** @var ForgotBasketEntity $task */
        foreach ($tasks as $task) {
            try {
                $this->forgotBasketService->executeTask($task);
                $this->log()->notice(
                    \sprintf(
                        'Sent "%s" forgot basket message to user #%s',
                        $task->getType(),
                        $task->getUserId()
                    )
                );
            } catch (ExpertsenderBasketEmptyException|ExpertsenderEmptyEmailException $e) {
                $this->log()->warning(
                    \sprintf(
                        'Failed to send "%s" forgot basket message to user #%s: %s',
                        $task->getType(),
                        $task->getUserId(),
                        $e->getMessage()
                    )
                );
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
