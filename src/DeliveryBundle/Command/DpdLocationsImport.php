<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Location\ExternalServiceTable;
use Bitrix\Sale\Location\ExternalTable;
use FourPaws\DeliveryBundle\Dpd\Lib\User;
use FourPaws\DeliveryBundle\Entity\DpdLocation;
use FourPaws\DeliveryBundle\Exception\FileNotFoundException;
use FourPaws\DeliveryBundle\Service\DpdLocationService;
use FourPaws\LocationBundle\LocationService;
use FtpClient\FtpClient;
use FtpClient\FtpException;
use Ipolh\DPD\API\Service\Geography;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DpdLocationsImport extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    protected const COUNTRIES = ['RU'];

    protected const DEFAULT_STEP_SIZE = 1000;

    protected const OPT_FILE_PATH = 'file';

    protected const OPT_STEP_SIZE = 'size';
    /**
     * @var array
     */
    protected $ftpConfig;

    /**
     * @var DpdLocationService
     */
    protected $dpdLocationService;

    /**
     * @var LocationService
     */
    protected $locationService;

    /**
     * @var array
     */
    protected $citiesCashPay = [];

    /**
     * @var int
     */
    protected $created = 0;

    /**
     * @var int
     */
    protected $updated = 0;

    /**
     * @var array
     */
    protected $notFound = [];

    /**
     * DpdLocationsImport constructor.
     *
     * @param null|string        $name
     * @param array              $ftpConfig
     * @param DpdLocationService $dpdLocationService
     * @param LocationService    $locationService
     */
    public function __construct(
        ?string $name = null,
        array $ftpConfig,
        DpdLocationService $dpdLocationService,
        LocationService $locationService
    )
    {
        parent::__construct($name);

        $this->ftpConfig = $ftpConfig;
        $this->locationService = $locationService;
        $this->dpdLocationService = $dpdLocationService;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('fourpaws:delivery:dpdlocations:import')
            ->setDescription('Import locations from DPD')
            ->addOption(
                static::OPT_FILE_PATH,
                'f',
                InputOption::VALUE_OPTIONAL,
                'path to .csv file'
            )
            ->addOption(
                static::OPT_STEP_SIZE,
                's',
                InputOption::VALUE_OPTIONAL,
                sprintf('step size (default - %s)', static::DEFAULT_STEP_SIZE)
            );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);

        try {
            $filePath = $input->getOption(static::OPT_FILE_PATH);
            $needRemoveFile = false;
            $stepSize = ($input->getOption(static::OPT_STEP_SIZE) > 0)
                ? $input->getOption(static::OPT_STEP_SIZE)
                : static::DEFAULT_STEP_SIZE;

            /** Если не передан путь к файлу - качаем с ftp */
            if (!$filePath) {
                $needRemoveFile = true;
                try {
                    $ftp = new FtpClient();
                    $ftp->connect($this->ftpConfig['host'])
                        ->login($this->ftpConfig['login'], $this->ftpConfig['password']);

                    $ftp->pasv(true);
                    $ftp->chdir($this->ftpConfig['directory']);
                    $files = $ftp->nlist('.');

                    $neededFile = null;
                    foreach ($files as $file) {
                        if (preg_match('~' . $this->ftpConfig['filemask'] . '~', $file)) {
                            $neededFile = $file;
                            break;
                        }
                    }

                    if (!$neededFile) {
                        throw new FtpException('file not found');
                    }


                    $filePath = $this->ftpConfig['localpath'] . '/' . ltrim($neededFile, './');
                    if (!$ftp->get($filePath, $neededFile, FTP_BINARY)) {
                        throw new FtpException('failed to download file');
                    }
                } catch (FtpException $e) {
                    $this->log()->error(sprintf('ftp error : %s', $e->getMessage()));
                }
                $this->log()->info(sprintf('downloaded file to %s', $filePath));
            }

            if (!file_exists($filePath)) {
                throw new FileNotFoundException(sprintf('file %s not found', $filePath));
            }

            /** @var Geography $geography */
            $geography = User::getInstance()->getService('geography');
            $this->citiesCashPay = [];
            foreach ($geography->getCitiesCashPay() as $city) {
                $this->citiesCashPay[$city['CITY_ID']] = $city['CITY_ID'];
            }

            ini_set('auto_detect_line_endings', true);
            if (!$fp = fopen($filePath, 'rb')) {
                throw new FileNotFoundException(sprintf('failed to open file %s', $filePath));
            }

            $items = [];
            $i = 0;
            while ([$dpdId, $kladrCode, , $name, $areaWithRegion, $country] = fgetcsv($fp, 0, ';')) {
                $countryCode = substr($kladrCode, 0, 2);
                $kladrCode = substr($kladrCode, 2);
                if (!\in_array($countryCode, static::COUNTRIES, true)) {
                    continue;
                }
                if (!$dpdId || !$kladrCode) {
                    continue;
                }

                preg_match('~(.+),(.+)~', $areaWithRegion, $matches);
                [, , $region] = $matches;

                $items[$dpdId] = [
                    'kladrCode'   => $kladrCode,
                    'dpdId'       => $dpdId,
                    'name'        => iconv('windows-1251', 'UTF-8', $name),
                    'region'      => iconv('windows-1251', 'UTF-8', $region),
                    'country'     => iconv('windows-1251', 'UTF-8', $country),
                    'countryCode' => $countryCode,
                ];

                if (++$i >= $stepSize) {
                    $i = 0;
                    $this->processItems($items);
                    $items = [];
                }
            }
            $this->processItems($items);

            ini_set('auto_detect_line_endings', false);
        } catch (\Exception $e) {
            $this->log()->error(
                sprintf('failed to import locations: %s: %s', \get_class($e), $e->getMessage())
            );
        }

        if (!empty($this->notFound)) {
            $this->log()->warning(sprintf('%s locations was not found', \count($this->notFound)), [
                'kladrCodes' => $this->notFound,
            ]);
        }

        if ($needRemoveFile) {
            unlink($filePath);
        }

        $this->log()->info(
            sprintf(
                'Task finished, time: %ss. Created: %s, updated: %s',
                round(microtime(true) - $start, 2),
                $this->created,
                $this->updated
            )
        );
    }

    /**
     * @param array $items
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function processItems(array $items)
    {
        if (empty($items)) {
            return;
        }

        $kladrCodes = array_column($items, 'kladrCode');
        $locationIds = $this->getLocationsByKladrCodes($kladrCodes);

        foreach ($items as $item) {
            if (!$locationIds[$item['kladrCode']]) {
                $this->notFound[] = $item['kladrCode'];
                continue;
            }

            $currentLocationIds = $locationIds[$item['kladrCode']];
            $dpdLocations = $this->dpdLocationService->getByDpdId($item['dpdId']);

            /** @var DpdLocation $dpdLocation */
            foreach ($dpdLocations as $dpdLocation) {
                if (!isset($currentLocationIds[$dpdLocation->getLocationId()])) {
                    continue;
                }

                $dpdLocation
                    ->setKladr($item['kladrCode'])
                    ->setName(trim($item['name']))
                    ->setRegionName(trim($item['region']))
                    ->setCountryName(trim($item['country']))
                    ->setCountryCode($item['countryCode'])
                    ->setIsCashPay(isset($this->citiesCashPay[$item['dpdId']]));

                if (!$this->dpdLocationService->save($dpdLocation)) {
                    $this->log()->warning('failed to save location', [
                        'id'    => $dpdLocation->getId(),
                        'dpdId' => $dpdLocation->getDpdId(),
                    ]);
                } else {
                    $this->updated++;
                }

                unset($currentLocationIds[$dpdLocation->getLocationId()]);
            }

            if (empty($currentLocationIds)) {
                continue;
            }

            foreach ($currentLocationIds as $locationId) {
                $dpdLocation = (new DpdLocation())
                    ->setDpdId($item['dpdId'])
                    ->setKladr($item['kladrCode'])
                    ->setName(trim($item['name']))
                    ->setRegionName(trim($item['region']))
                    ->setCountryName(trim($item['country']))
                    ->setCountryCode($item['countryCode'])
                    ->setIsCashPay(isset($this->citiesCashPay[$item['dpdId']]))
                    ->setLocationId($locationId);

                if (!$this->dpdLocationService->save($dpdLocation)) {
                    $this->log()->warning('failed to save location', [
                        'id'    => $dpdLocation->getId(),
                        'dpdId' => $dpdLocation->getDpdId(),
                    ]);
                } else {
                    $this->created++;
                }
            }
        }
    }

    /**
     * @param string[] $codes
     * @return string[]
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function getLocationsByKladrCodes(array $codes): array
    {
        $results = ExternalTable::query()
            ->setSelect(['LOCATION_ID', 'XML_ID'])
            ->setFilter([
                'SERVICE.CODE' => LocationService::KLADR_SERVICE_CODE,
                '=XML_ID'      => $codes,
            ])
            ->registerRuntimeField(
                new ReferenceField(
                    'SERVICE',
                    ExternalServiceTable::getEntity(),
                    ['=this.SERVICE_ID' => 'ref.ID']
                )
            )
            ->exec()->fetchAll();

        $result = [];
        foreach ($results as $res) {
            $result[$res['XML_ID']][$res['LOCATION_ID']] = $res['LOCATION_ID'];
        }

        return $result;
    }
}
