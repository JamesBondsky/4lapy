<?php

namespace FourPaws\PersonalBundle\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Exception;
use FourPaws\PersonalBundle\Service\Chance2Service;
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

    protected const OPTION_TYPE = 'type';
    protected const OPTION_TYPE_SHORTCUT = 't';

    /**
     * @var ChanceService
     */
    protected $chanceService;

    /**
     * @var Chance2Service
     */
    protected $chance2Service;

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
        $type = $input->getOption(self::OPTION_TYPE);

        $currentChanceService = ($type === 'j') ? $this->chance2Service : $this->chanceService;

        if (!$userId) {
            $currentChanceService->updateAllUserChance();
            return true;
        }

        $currentChanceService->updateUserChance($userId);

        return true;
    }
}
