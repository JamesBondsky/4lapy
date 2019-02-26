<?php

namespace FourPaws\SaleBundle\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Type\DateTime;
use FourPaws\App\Application;
use FourPaws\PersonalBundle\Exception\ManzanaCheque\ChequeItemNotActiveException;
use FourPaws\PersonalBundle\Exception\ManzanaCheque\ChequeItemNotExistsException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ImportManzanaOrders
 *
 * @package FourPaws\SaleBundle\Command
 */
class ImportManzanaOrders extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    protected const OPT_PERIOD = 'period';

    /**
     * @var int
     */
    protected $deleteCount = 0;

    /**
     * ImportManzanaOrders constructor.
     *
     * @param null $name
     *
     * @throws LogicException
     */
    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function configure(): void
    {
        $this->setName('fourpaws:sale:order:manzana:import')
            ->setDescription('Import orders from manzana')
            ->addOption(
                static::OPT_PERIOD,
                'p',
                InputOption::VALUE_OPTIONAL,
                'Time period'
            );
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $period = $input->getOption(static::OPT_PERIOD) ?? '1 month';

        /** @var \FourPaws\UserBundle\Service\UserService $userService */
        $userService = Application::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);

        $periodStartDateTime = new DateTime();
        $periodStartDateTime->add('- ' . $period);

        $users = $userService->getUserRepository()->findBy([
            '>=LAST_LOGIN' => $periodStartDateTime,
        ], []);

        if ($users)
        {
            $orderService = Application::getInstance()->getContainer()->get('order.service');

            foreach ($users as $user)
            {
                try
                {
                    $orderService->importOrdersFromManzana($user);
                } catch (ChequeItemNotExistsException|ChequeItemNotActiveException $e)
                {
                    /** Не логируем */
                } catch (\Exception $e)
                {
                    $this->log()->error(
                        \sprintf(
                            'Error importing orders for user #%s: %s. %s',
                            $user->getId(),
                            $e->getMessage(),
                            $e->getTraceAsString()
                        )
                    );
                }
            }
        }

        $this->log()->info(\sprintf('Updated orders for %s users', count($users)));
    }
}
