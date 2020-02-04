<?php

namespace FourPaws\PersonalBundle\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Exception;
use FourPaws\App\Application;
use FourPaws\PersonalBundle\Service\Chance2Service;
use FourPaws\PersonalBundle\Service\OrderService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ChanceRecalculateCommand extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    protected const OPTION_USER = 'user';
    protected const OPTION_USER_SHORTCUT = 'u';

    protected const OPTION_OFFSET = 'offset';
    protected const OPTION_OFFSET_SHORTCUT = 'q';

    protected const LIMIT = 5000;

    /**
     * @var Chance2Service
     */
    protected $chance2Service;

    /**
     * @param Chance2Service $chance2Service
     */
    public function __construct(Chance2Service $chance2Service)
    {
        $this->chance2Service = $chance2Service;

        parent::__construct();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function configure(): void
    {
        $this->setName('articul:chance:recalculate')
            ->setDescription('')
            ->addOption(
                self::OPTION_USER,
                self::OPTION_USER_SHORTCUT,
                InputOption::VALUE_OPTIONAL,
                '',
                false
            )
            ->addOption(
                self::OPTION_OFFSET,
                self::OPTION_OFFSET_SHORTCUT,
                InputOption::VALUE_OPTIONAL,
                '',
                0
            );
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws Exception
     * @return bool
     */
    public function execute(InputInterface $input, OutputInterface $output): bool
    {
        global $USER;

        $userId = $input->getOption(self::OPTION_USER);
        $offset = (int)$input->getOption(self::OPTION_OFFSET);


        /** @var OrderService $orderService */
        $orderService = Application::getInstance()->getContainer()->get('order.service');

        /** @var UserService $userService */
        $userService = Application::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);

        if ($userId) {
            $allUserIds = [$userId];
        } else {
            $allUserIds = $this->chance2Service->getAllUserIds(self::LIMIT * $offset, self::LIMIT);

            if (empty($allUserIds)) {
                throw new RuntimeException('Нет пользователей в акции');
            }
        }

        $totalCount = count($allUserIds);
        $i = 1;

        foreach ($allUserIds as $userId) {
            try {
                $users = $userService->getUserRepository()->findBy(['=ID' => $userId]);

                if (count($users) === 0) {
                    continue;
                }

                $user = current($users);

                if ($userId > 0 && ($USER->GetID() !== $userId)) {
                    $USER->Authorize($userId, false, false);
                }
                $this->log()->info(sprintf('Offset: %s, Start recalculate chance for user: %s, %s/%s', $offset, $user->getId(), $i, $totalCount));
                $orderService->importOrdersFromManzana($user);
            } catch (Exception $e) {
                $this->log()->error(sprintf('Error recalculate chance for user #%s: %s. %s', $userId, $e->getMessage(), $e->getTraceAsString()));
            }

            $i++;
        }

        return true;
    }
}
