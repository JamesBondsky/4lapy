<?php

namespace FourPaws\PersonalBundle\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Exception;
use FourPaws\App\Application;
use FourPaws\External\ManzanaService;
use FourPaws\PersonalBundle\Exception\ManzanaCheque\ChequeItemNotActiveException;
use FourPaws\PersonalBundle\Exception\ManzanaCheque\ChequeItemNotExistsException;
use FourPaws\PersonalBundle\Service\Chance2Service;
use FourPaws\PersonalBundle\Service\ChanceService;
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

    protected const OPTION_MANZANA = 'manzana';
    protected const OPTION_MANZANA_SHORTCUT = 'm';

    protected const OPTION_TYPE = 'type';
    protected const OPTION_TYPE_SHORTCUT = 't';

    protected const OPTION_ERROR = 'error';
    protected const OPTION_ERROR_SHORTCUT = 'o';

    /**
     * @var ChanceService
     */
    protected $chanceService;

    /**
     * @var Chance2Service
     */
    protected $chance2Service;

    protected $existErrorUserIds;

    /**
     * @param ChanceService $chanceService
     * @param Chance2Service $chance2Service
     */
    public function __construct(ChanceService $chanceService, Chance2Service $chance2Service)
    {
        $this->chanceService = $chanceService;
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
                self::OPTION_TYPE,
                self::OPTION_TYPE_SHORTCUT,
                InputOption::VALUE_OPTIONAL,
                '',
                'j'
            )
            ->addOption(
                self::OPTION_MANZANA,
                self::OPTION_MANZANA_SHORTCUT,
                InputOption::VALUE_OPTIONAL,
                '',
                false
            )
            ->addOption(
                self::OPTION_ERROR,
                self::OPTION_ERROR_SHORTCUT,
                InputOption::VALUE_OPTIONAL,
                '',
                false
            );
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     * @throws Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): bool
    {
        global $USER;

        $userId = $input->getOption(self::OPTION_USER);
        $type = $input->getOption(self::OPTION_TYPE);
        $withManzana = $input->getOption(self::OPTION_MANZANA);
        $errorUsers = (bool)$input->getOption(self::OPTION_ERROR);

        if ($errorUsers) {
            $withManzana = true;
        }

        $currentChanceService = ($type === 'j') ? $this->chance2Service : $this->chanceService;

        $successUserIds = [];
        $errorUserIds = [];

        if ($withManzana) {
            /** @var OrderService $orderService */
            $orderService = Application::getInstance()->getContainer()->get('order.service');

            /** @var UserService $userService */
            $userService = Application::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);

            if ($errorUsers) {
                $arFilter = ['ID' => $this->getExistErrorUserIds()];
            } else if ($userId) {
                $arFilter = ['ID' => $userId];
            } else {
                $allUserIds = $currentChanceService->getAllUserIds();

                if (empty($allUserIds)) {
                    throw new RuntimeException('Нет пользователей в акции');
                }

                $arFilter = ['ID' => $allUserIds];
            }

            $users = $userService->getUserRepository()->findBy($arFilter, []);
            $totalCount = count($users);
            $i = 1;

            foreach ($users as $user) {
                $userId = $user->getId();
                try {
                    if ($userId > 0 && ($USER->GetID() !== $userId)) {
                        $USER->Authorize($userId, false, false);
                    }
                    $this->log()->info(sprintf('Start recalculate chance for user: %s, %s/%s', $user->getId(), $i, $totalCount));
                    $orderService->importOrdersFromManzana($user, ($type === 'j'));

                    $successUserIds[] = $userId;
                } catch (Exception $e) {
                    if ((strpos('Ошибка получения данных', $e->getMessage()) !== false) && !in_array($userId, $this->getExistErrorUserIds(), false)) {
                        $errorUserIds[] = $userId;
                    }
                    $this->log()->error(sprintf('Error importing orders for user #%s: %s. %s', $userId, $e->getMessage(), $e->getTraceAsString()));
                }

                $i++;
            }
        } else {
            if (!$userId) {
                $currentChanceService->updateAllUserChance();
                return true;
            }

            $currentChanceService->updateUserChance($userId);
        }

        $this->deleteSuccessUsersFromErrorList($successUserIds);
        $this->addErrorUsersToErrorList($errorUserIds);

        return true;
    }

    /**
     * @return array
     */
    protected function getExistErrorUserIds(): array
    {
        global $DB;

        if ($this->existErrorUserIds === null) {
            $dbRes = $DB->Query('select user_id from 4lapy_user_chance_error');

            $this->existErrorUserIds = [];
            while ($res = $dbRes->Fetch()) {
                $this->existErrorUserIds[] = $res['user_id'];
            }
        }

        return $this->existErrorUserIds;
    }

    protected function deleteSuccessUsersFromErrorList($successUserIds)
    {
        global $DB;

        if (!empty($successUserIds)) {
            $condition = implode(',', $successUserIds);

            $DB->Query("delete from 4lapy_user_chance_error where user_id in ($condition)");
        }
    }

    protected function addErrorUsersToErrorList($errorUserIds)
    {
        global $DB;

        if (!empty($errorUserIds)) {
            $values = implode(',', $errorUserIds);

            $DB->Query("insert into 4lapy_user_chance_error (user_id) values ($values)");
        }
    }
}
