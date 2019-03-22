<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\StoreBundle\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Application as BitrixApplication;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Helpers\TaggedCacheHelper;
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
            $date->modify('+1 day');
        }

        /** @noinspection TypeUnsafeComparisonInspection */
        if ($tc && (((int)$tc != $tc) || $tc < 0)) {
            throw new \RuntimeException('Transition count must be a positive integer value');
        }

        $startGlobal = microtime(true);

        /** Расчёты не сгенерируются, если для первого отправителя не будет расписаний */
        $senders = $this->storeService->getStores(StoreService::TYPE_ALL_WITH_SUPPLIERS);
        //$senders = [$this->storeService->getStoreByXmlId('0000100792')];


        /** @var Store $sender */
        foreach ($senders as $i => $sender) {
            try {
                $results = $this->scheduleResultService->calculateForSender($sender, $date, $tc);
                if(!file_put_contents($this->scheduleResultService->getFilename(), serialize($results))){
                    throw new \Exception('Cannot put results to file');
                }
            } catch (\Exception $e) {
                $this->log()->error(
                    sprintf('Failed to calculate schedule results: %s: %s', \get_class($e), $e->getMessage()),
                    ['sender' => $sender->getXmlId()]
                );
            }
        }

        $this->log()->info(
            sprintf(
                'Task finished, time: %smin.',
                round((microtime(true) - $startGlobal) / 60, 2)
            )
        );
    }
}
