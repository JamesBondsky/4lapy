<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\StoreBundle\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Application as BitrixApplication;
use Bitrix\Main\DB\Exception;
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

class DeliveryScheduleUpdate extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var ScheduleResultService
     */
    protected $scheduleResultService;

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
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('fourpaws:store:updateschedules')
            ->setDescription('Update all delivery schedules');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startGlobal = microtime(true);

        BitrixApplication::getConnection()->startTransaction();

        try {
            $file = $this->scheduleResultService->getFilename();
            if (!file_exists($file)) {
                throw new \Exception('File not found');
            }
            $results = unserialize(file_get_contents($file));
            $this->scheduleResultService->deleteAllResults();
            [$created] = $this->scheduleResultService->updateResults($results);
            $isSuccess = true;
        } catch (\Throwable $e) {
            $this->log()->info(sprintf('Error getting results: %s', $e->getMessage()));
            $isSuccess = false;
        }

        if ($isSuccess) {
            BitrixApplication::getConnection()->commitTransaction();
            $this->log()->info(sprintf('Schedules updated. Total created: %s', $created));
        } else {
            BitrixApplication::getConnection()->rollbackTransaction();
            $this->log()->info('Failed to update schedules');
        }

        TaggedCacheHelper::clearManagedCache(['catalog:store:schedule:results']);

        $this->log()->info(
            sprintf(
                'Task finished, time: %smin.',
                round((microtime(true) - $startGlobal) / 60, 2)
            )
        );
    }
}
