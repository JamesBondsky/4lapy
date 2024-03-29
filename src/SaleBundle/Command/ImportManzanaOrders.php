<?php

namespace FourPaws\SaleBundle\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Type\DateTime;
use Exception;
use FourPaws\App\Application;
use FourPaws\External\ManzanaService;
use FourPaws\PersonalBundle\Exception\ManzanaCheque\ChequeItemNotActiveException;
use FourPaws\PersonalBundle\Exception\ManzanaCheque\ChequeItemNotExistsException;
use FourPaws\PersonalBundle\Service\OrderService;
use FourPaws\UserBundle\EventController\Event;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function sprintf;

class ImportManzanaOrders extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    protected const OPT_PERIOD = 'period';
    protected const OPT_USER_ID = 'user';
    protected const MQ = 'mq';

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
            )
            ->addOption(
                static::OPT_USER_ID,
                'u',
                InputOption::VALUE_OPTIONAL,
                'User id'
            )
            ->addOption(
                static::MQ,
                'm',
                InputOption::VALUE_OPTIONAL,
                'Use Message Query'
            );
    }

    /**
     * Импортирует заказы пользователей из Manzana.
     * По умолчанию за последний месяц (период можно изменить).
     * Либо можно импортировать заказы конкретного пользователя.
     * По умолчанию идут синхронные запросы в Manzana, но можно переключить на работу через очередь сообщений
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        Event::disableEvents();

        global $USER;

        $period = $input->getOption(static::OPT_PERIOD) ?? '1 month';
        $userId = $input->getOption(static::OPT_USER_ID);
        $useMQ = (bool)($input->getOption(static::MQ));

        /** @var UserService $userService */
        $userService = Application::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);

        $periodStartDateTime = new DateTime();
        $periodStartDateTime->add('- ' . $period);

        $arFilter = [];
        if (!$userId) {
            $arFilter['>=LAST_LOGIN'] = $periodStartDateTime;
        }

        if ($userId) {
            $arFilter['ID'] = $userId;
        }

        $users = $userService->getUserRepository()->findBy($arFilter, []);

        if ($users) {
            /** @var OrderService $orderService */
            $orderService = Application::getInstance()->getContainer()->get('order.service');

            $manzanaService = null;
            if ($useMQ) {
                /** @var ManzanaService $manzanaService */
                $manzanaService = Application::getInstance()->getContainer()->get('manzana.service');
            }

            foreach ($users as $user) {
                if ($useMQ) {
                    try {
                        $userId = $user->getId();
                        $manzanaService->importUserOrdersAsync($user);
                    } catch (Exception $e) {
                        $this->log()->error(sprintf('Error queueing orders query for user #%s: %s. %s', $userId, $e->getMessage(), $e->getTraceAsString()));
                    }
                } else {
                    $userId = $user->getId();
                    try {
                        if ($userId > 0 && ($USER->GetID() !== $userId)) {
                            $USER->Authorize($userId, false, false);
                        }
                        $orderService->importOrdersFromManzana($user);
                    } catch (ChequeItemNotExistsException|ChequeItemNotActiveException $e) {
                        /** Не логируем */
                    } catch (Exception $e) {
                        $this->log()->error(sprintf('Error importing orders for user #%s: %s. %s', $userId, $e->getMessage(), $e->getTraceAsString()));
                    }
                }
            }
        }

        if ($useMQ) {
            $this->log()->info(sprintf('Queued orders import for %s users (part of them might be already in the queue)', count($users)));
        } else {
            $this->log()->info(sprintf('Updated orders for %s users', count($users)));
        }

        Event::enableEvents();
    }
}
