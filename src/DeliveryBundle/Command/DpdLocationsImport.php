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
use FourPaws\DeliveryBundle\Exception\NotFoundException;
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

    protected const OPT_FILE_PATH = 'file';
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

            /** Если не передан путь к файлу - качаем с ftp */
            if (!$filePath) {
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
                    $kladrCode,
                    $dpdId,
                    $name,
                    $region,
                    $country,
                    $countryCode,
                ];

                if (++$i > 500) {
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

        $kladrCodes = array_column($items, 0);
        $locations = $this->getLocationsByKladrCodes($kladrCodes);

        foreach ($items as $item) {
            [
                $kladrCode,
                $dpdId,
                $name,
                $region,
                $country,
                $countryCode,
            ] = $item;

            if (!$locations[$kladrCode]) {
                $this->notFound[] = $kladrCode;
                continue;
            }

            try {
                $dpdLocation = $this->dpdLocationService->getOneByDpdId($dpdId);
            } catch (NotFoundException $e) {
                $dpdLocation = (new DpdLocation())->setDpdId($dpdId);
            }

            $dpdLocation
                ->setKladr($kladrCode)
                ->setName(trim($name))
                ->setRegionName(trim($region))
                ->setCountryName(trim($country))
                ->setCountryCode($countryCode)
                ->setIsCashPay(isset($this->citiesCashPay[$dpdId]))
                ->setLocationId($locations[$kladrCode]);

            $isCreate = $dpdLocation->getId();
            if (!$this->dpdLocationService->save($dpdLocation)) {
                $this->log()->warning('failed to save location', [
                    'id'    => $dpdLocation->getId(),
                    'dpdId' => $dpdLocation->getDpdId(),
                ]);
            } else {
                $isCreate ? $this->created++ : $this->updated++;
            }
        }
    }

    /**
     * @param string[] $codes
     * @param bool     $exact
     * @return string[]
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function getLocationsByKladrCodes(array $codes, bool $exact = false): array
    {
        $filter = ['SERVICE.CODE' => LocationService::KLADR_SERVICE_CODE];

        if ($exact) {
            $filter['=XML_ID'] = $codes;
        } else {
            $codes = array_map(function ($el) {
                return $el . '%';
            }, $codes);
            $filter['XML_ID'] = $codes;
        }

        $results = ExternalTable::query()
            ->setSelect(['LOCATION_ID', 'XML_ID'])
            ->setFilter($filter)
            ->registerRuntimeField(
                new ReferenceField(
                    'SERVICE',
                    ExternalServiceTable::getEntity(),
                    ['=this.SERVICE_ID' => 'ref.ID']
                )
            )
            ->exec();

        $result = [];
        while ($res = $results->fetch()) {
            $result[substr($res['XML_ID'], 0, 11)] = $res['LOCATION_ID'];
        }

        return $result;
    }
}
