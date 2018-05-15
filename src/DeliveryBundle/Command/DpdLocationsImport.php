<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Application as BitrixApplication;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Service\DpdLocationsService;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Service\ScheduleResultService;
use FourPaws\StoreBundle\Service\StoreService;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DpdLocationsImport extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var array
     */
    protected $ftpConfig;

    /**
     * @var DpdLocationsService
     */
    protected $dpdLocationsService;

    /**
     * DpdLocationsImport constructor.
     *
     * @param null|string $name
     * @param array       $ftpConfig
     */
    public function __construct(?string $name = null, array $ftpConfig, DpdLocationsService $dpdLocationsService)
    {
        parent::__construct($name);

        $this->ftpConfig = $ftpConfig;
        $this->dpdLocationsService = $dpdLocationsService;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('fourpaws:delivery:dpdlocations:import')
            ->setDescription('Import locations from DPD');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->scheduleResultService->findResultsBySender();

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
