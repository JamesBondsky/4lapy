<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\DeliveryBundle\Dpd\Lib\User;
use FourPaws\DeliveryBundle\Entity\DpdLocation;
use FourPaws\DeliveryBundle\Exception\FileNotFoundException;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Service\DpdLocationService;
use FtpClient\FtpClient;
use FtpClient\FtpException;
use Ipolh\DPD\API\Service\Geography;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DpdLocationsImport extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    protected const COUNTRIES = ['RU'];

    /**
     * @var array
     */
    protected $ftpConfig;

    /**
     * @var DpdLocationService
     */
    protected $dpdLocationsService;

    /**
     * DpdLocationsImport constructor.
     *
     * @param null|string        $name
     * @param array              $ftpConfig
     * @param DpdLocationService $dpdLocationsService
     */
    public function __construct(?string $name = null, array $ftpConfig, DpdLocationService $dpdLocationsService)
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
        $start = microtime(true);
        $updated = 0;
        $created = 0;
        $deleted = 0;

        try {
            $filePath = '';

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

            if (!file_exists($filePath)) {
                throw new FileNotFoundException(sprintf('file %s not found', $filePath));
            }

            /** @var Geography $geography */
            $geography = User::getInstance()->getService('geography');
            $citiesCashPay = [];
            foreach ($geography->getCitiesCashPay() as $city) {
                $citiesCashPay[$city['CITY_ID']] = $city['CITY_ID'];
            }

            ini_set('auto_detect_line_endings', true);
            if (!$fp = fopen($filePath, 'rb')) {
                throw new FileNotFoundException(sprintf('failed to open file %s', $filePath));
            }

            while ([$dpdId, $kladrCode, $prefix, $name, $areaWithRegion, $country] = fgetcsv($fp, 0, ';')) {
                $countryCode = substr($kladrCode, 0, 2);
                if (!\in_array($countryCode, static::COUNTRIES, true)) {
                    continue;
                }
                if (!$dpdId) {
                    continue;
                }

                [, $area, $region] = preg_match('~(.+),(.+)~', $areaWithRegion);

                try {
                    $location = $this->dpdLocationsService->getOneByDpdId($dpdId);
                } catch (NotFoundException $e) {
                    $location = (new DpdLocation())->setDpdId($dpdId);
                }

                $location->setPrefix(trim($prefix))
                    ->setName(trim($name))
                    ->setAreaName(trim($area))
                    ->setRegionName(trim($region))
                    ->setCountryName(trim($country))
                    ->setCountryCode($countryCode)
                    ->setIsCashPay(isset($citiesCashPay[$dpdId]));

                $isCreate = $location->getId();
                if (!$this->dpdLocationsService->save($location)) {
                    $this->log()->warning('failed to save location', [
                        'id'    => $location->getId(),
                        'dpdId' => $location->getDpdId(),
                    ]);
                } else {
                    $isCreate ? $created++ : $updated++;
                }
            }
            ini_set('auto_detect_line_endings', false);

        } catch (\Exception $e) {
            $this->log()->error(
                sprintf('failed to import locations: %s: %s', \get_class($e), $e->getMessage())
            );
        }

        $this->log()->info(
            sprintf(
                'Task finished, time: %ss. Created: %s, updated: %s, deleted: %s',
                round(microtime(true) - $start, 2),
                $created,
                $updated,
                $deleted
            )
        );
    }
}
