<?php

namespace FourPaws\PersonalBundle\Command;


use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Exception;
use FourPaws\PersonalBundle\Service\ChanceService;
use Psr\Log\LoggerAwareInterface;
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

    protected const OPTION_PERIOD = 'period';
    protected const OPTION_PERIOD_SHORTCUT = 'p';

    /**
     * @var ChanceService
     */
    protected $chanceService;

    /**
     * @param ChanceService $chanceService
     */
    public function __construct(ChanceService $chanceService)
    {
        $this->chanceService = $chanceService;

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
                self::OPTION_PERIOD,
                self::OPTION_PERIOD_SHORTCUT,
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
        $userId = $input->getOption(self::OPTION_USER);
        $period = $input->getOption(self::OPTION_PERIOD);

        if (!$userId) {
            $this->chanceService->updateAllUserChance($period);
            return true;
        }

        $this->chanceService->updateUserChance($userId, $period);

        return true;
    }
}
