<?php

namespace FourPaws\MobileApiBundle\Console;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Exception;
use FourPaws\App\Application;
use FourPaws\CatalogBundle\Console\FeedFactory;
use FourPaws\CatalogBundle\Exception\ArgumentException;
use FourPaws\MobileApiBundle\Services\PushEventService;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * Class ImportLatLonToLocationTable
 * @package FourPaws\MobileApiBundle\Console
 */
class ImportLatLonToLocationTable extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /** @var PushEventService */
    private $pushEventService;

    /**
     * @throws InvalidArgumentException
     */
    public function configure(): void
    {
        $this
            ->setName('bitrix:location:latlon:import')
            ->setDescription('Imports lat and lon into location table');
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     *
     * @throws ArgumentException
     * @throws IOException
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $locationsNew = \Bitrix\Sale\Location\LocationTable::query()->setSelect(['*'])->setFilter(['=LATITUDE' => 0])->exec()->fetchAll();
        $locationsOld = $this->loadLocations();
        foreach ($locationsNew as $locationNew) {
            if ($locationOld = $locationsOld[$locationNew['CODE']]) {
                \Bitrix\Sale\Location\LocationTable::update($locationNew['ID'], [
                    'LATITUDE' => $locationOld['LATITUDE'],
                    'LONGITUDE' => $locationOld['LONGITUDE']
                ]);
            }
        }
        return FeedFactory::EXIT_CODE_END;
    }

    private function loadLocations()
    {
        static $fp;
        if (null === $fp) {
            $filePath = Application::getAbsolutePath('/local/php_interface/migration_sources/location_geo_coords.csv');
            $fp = fopen($filePath, 'rb');

            if (false === $fp) {
                throw new \RuntimeException(
                    sprintf(
                        'Can not open file %s',
                        $filePath
                    )
                );
            }
        }

        $locations = [];
        while ($row = fgetcsv($fp, 0, ';')) {
            [$code, $lat, $lon] = $row;
            $locations[$code] = [
                'CODE' => $code,
                'LATITUDE' => $lat,
                'LONGITUDE' => $lon,
            ];
        }

        return $locations;
    }
}

