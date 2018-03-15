<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Highloadblock\DataManager;
use Bitrix\Main\ArgumentException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\MobileApiBundle\Dto\Object\Location\MetroLine;
use FourPaws\MobileApiBundle\Dto\Object\Location\MetroStation;
use Psr\Log\LoggerAwareInterface;

class LocationService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var DataManager
     */
    private $metroManager;
    /**
     * @var DataManager
     */
    private $metroLinesManager;

    public function __construct(DataManager $metroManager, DataManager $metroLinesManager)
    {
        $this->metroManager = $metroManager;
        $this->metroLinesManager = $metroLinesManager;
    }

    /**
     * @param string $locationCode
     *
     * @return Collection|MetroLine[]
     */
    public function getMetroLinesWithStations(string $locationCode = ''): Collection
    {
        $lines = new ArrayCollection();
        foreach ($this->getLines($locationCode) as $line) {
            $lines->set($line->getId(), $line);
        }

        $stations = $this->getStations($lines->getKeys());
        foreach ($stations as $station) {
            /**
             * @var MetroLine $line
             */
            $line = $lines->get($station->getLineId());
            $line->addStation($station);
        }
        return $lines;
    }


    /**
     * @param string $locationCode
     *
     * @return Collection|MetroLine[]
     */
    protected function getLines(string $locationCode = ''): Collection
    {
        $query = $this->metroLinesManager::query()
            ->addSelect('ID')
            ->addSelect('UF_NAME')
            ->addSelect('UF_COLOUR_CODE')
            ->whereNotNull('UF_NAME')
            ->setCacheTtl(86400);

        if ($locationCode) {
            $query->where('UF_CITY_LOCATION', $locationCode);
        }
        $result = $query->exec();
        try {
            $result->addFetchDataModifier(function (array $data) {
                return new MetroLine((int)$data['ID'], $data['UF_NAME'], $data['UF_COLOUR_CODE'] ?? '');
            });
        } catch (ArgumentException $e) {
            $this->log()->error($e->getMessage(), ['location_code' => $locationCode]);
            return new ArrayCollection();
        }

        return new ArrayCollection($result->fetchAll());
    }

    /**
     * @param array $lines
     *
     * @return Collection|MetroStation[]
     */
    protected function getStations(array $lines): Collection
    {
        $result = $this->metroManager::query()
            ->addSelect('ID')
            ->addSelect('UF_NAME')
            ->addSelect('UF_BRANCH')
            ->whereIn('UF_BRANCH', $lines)
            ->whereNotNull('UF_BRANCH')
            ->whereNotNull('UF_NAME')
            ->setCacheTtl(86400)
            ->exec();

        try {
            $result->addFetchDataModifier(function (array $data) {
                return new MetroStation((int)$data['ID'], $data['UF_NAME'], $data['UF_BRANCH']);
            });
        } catch (ArgumentException $e) {
            $this->log()->error($e->getMessage(), $lines);
            return new ArrayCollection();
        }

        return new ArrayCollection($result->fetchAll());
    }
}
