<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\StoreBundle\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Application as BitrixApplication;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Service\ScheduleResultService;
use FourPaws\StoreBundle\Service\StoreService;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeliveryScheduleCalculate extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    protected const DATE_FORMAT = 'Y-m-d';

    protected const OPT_DATE = 'date';

    protected const OPT_TRANSITION_COUNT = 'transition-count';

    /**
     * @var ScheduleResultService
     */
    protected $scheduleResultService;

    /**
     * @var StoreService
     */
    protected $storeService;

    /**
     * DeliveryScheduleCalculate constructor.
     *
     * @param null|string $name
     *
     * @throws ApplicationCreateException
     */
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->scheduleResultService = Application::getInstance()->getContainer()
            ->get(ScheduleResultService::class);
        $this->storeService = Application::getInstance()->getContainer()
            ->get('store.service');
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('fourpaws:store:schedulescalculate')
            ->setDescription('Recalculate all delivery schedules')
            ->addOption(
                static::OPT_DATE,
                'd',
                InputOption::VALUE_OPTIONAL,
                'Start date (' . static::DATE_FORMAT . ')'
            )
            ->addOption(
                static::OPT_TRANSITION_COUNT,
                'tc',
                InputOption::VALUE_OPTIONAL,
                'Max transition count between stores'
            );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $d = $input->getOption(static::OPT_DATE);
        $tc = $input->getOption(static::OPT_TRANSITION_COUNT);

        if ($d) {
            if (!$date = \DateTime::createFromFormat(static::DATE_FORMAT, $d)) {
                throw new \RuntimeException(sprintf('Date must be in %s format', static::DATE_FORMAT));
            }
        } else {
            $date = new \DateTime();
        }

        /** @noinspection TypeUnsafeComparisonInspection */
        if ($tc && (((int)$tc != $tc) || $tc < 0)) {
            throw new \RuntimeException('Transition count must be a positive integer value');
        }

        BitrixApplication::getConnection()->startTransaction();

        $isSuccess = true;
        $senders = $this->storeService->getStores(StoreService::TYPE_ALL_WITH_SUPPLIERS);
        /** @var Store $sender */
        $start = microtime(true);
        $totalCreated = 0;
        $totalDeleted = 0;
        foreach ($senders as $sender) {
            try {
                $totalDeleted += $this->scheduleResultService->deleteResultsForSender($sender);
                $results = $this->scheduleResultService->calculateForSender($sender, $date, $tc);
                [$created] = $this->scheduleResultService->updateResults($results);
                $totalCreated += $created;
            } catch (\Exception $e) {
                $this->log()->error(
                    sprintf('Failed to calculate schedule results: %s: %s', \get_class($e), $e->getMessage()),
                    ['sender' => $sender->getXmlId()]
                );

                $isSuccess = false;
                break;
            }
        }

        if ($isSuccess) {
            BitrixApplication::getConnection()->commitTransaction();
        } else {
            BitrixApplication::getConnection()->rollbackTransaction();
        }

        $this->log()->info(
            sprintf(
                'Task finished, time: %ss. Created: %s, deleted: %s',
                round(microtime(true) - $start, 2),
                $totalCreated,
                $totalDeleted
            )
        );
    }
}
