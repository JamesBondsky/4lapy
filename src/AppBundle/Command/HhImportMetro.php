<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\AppBundle\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Highloadblock\DataManager;
use Bitrix\Main\Application as BitrixApplication;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\Connection;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Sale\Location\LocationTable;
use Cocur\Slugify\SlugifyInterface;
use FourPaws\App\Application;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HhImportMetro extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    private const ARGUMENT_CITY_ID = 'city-id';

    private const ARGUMENT_LOCATION_ID = 'location-id';

    private const URL_PATTERN = 'https://api.hh.ru/metro/%s';

    /**
     * @var DataManager
     */
    private $metroManager;

    /**
     * @var DataManager
     */
    private $metroLinesManager;

    /**
     * @var SlugifyInterface
     */
    private $slugify;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(?string $name = null, SlugifyInterface $slugify, ClientInterface $client)
    {
        parent::__construct($name);

        $app = Application::getInstance();
        $this->metroManager = $app->getContainer()->get('bx.hlblock.metrostations');
        $this->metroLinesManager = $app->getContainer()->get('bx.hlblock.metroways');
        $this->slugify = $slugify;
        $this->client = $client;
        $this->connection = BitrixApplication::getConnection();
    }

    public function configure()
    {
        $this
            ->setName('fourpaws:import:metro-hh')
            ->setDescription('Import metro data')
            ->addArgument(
                self::ARGUMENT_CITY_ID,
                InputArgument::REQUIRED,
                'HH ID of city where to find metros'
            )
            ->addArgument(
                self::ARGUMENT_LOCATION_ID,
                InputArgument::REQUIRED,
                'Location code of city in Bitrix'
            );
    }

    /**
     * {@inheritDoc}
     * @throws \Symfony\Component\Console\Exception\RuntimeException
     * @throws \Bitrix\Main\DB\SqlQueryException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $cityId = (int)$input->getArgument(static::ARGUMENT_CITY_ID);
        if ($cityId < 0) {
            throw new InvalidArgumentException(sprintf('Passed wrong city id: %s', $cityId));
        }

        $locationId = (string)$input->getArgument(static::ARGUMENT_LOCATION_ID);
        if (!$locationId) {
            throw new InvalidArgumentException(sprintf('Passed wrong location code: %s', $locationId));
        }

        $isExist = LocationTable::query()
                ->where('CODE', $locationId)
                ->exec()
                ->getSelectedRowsCount() === 1;
        if (!$isExist) {
            throw new InvalidArgumentException(sprintf(
                'Passed wrong location code: %s %s',
                $locationId,
                'Does not exist in database'
            ));
        }

        $lines = $this->getLines($cityId);
        $this->import($lines, $locationId);
    }

    /**
     * @param array  $lines
     * @param string $locationId
     *
     * @throws \Bitrix\Main\Db\SqlQueryException
     */
    protected function import(array $lines, string $locationId)
    {
        $this->connection->startTransaction();

        try {
            $linesToAdd = $this->getAddLines($lines, $locationId);
            $this->addLines($linesToAdd);

            $metrosToAdd = $this->getAddMetros($lines);
            $this->addMetros($metrosToAdd);

            $this->connection->commitTransaction();
            $this->log()->info('Проимпортировано линий: ' . \count($linesToAdd));
            $this->log()->info('Проимпортировано станций метро: ' . \count($metrosToAdd));
        } catch (\Exception $exception) {
            $this->log()->debug($exception->getMessage());
            $this->connection->rollbackTransaction();
        }
    }

    /**
     * @param array $linesToAdd
     *
     * @throws ArgumentException
     */
    protected function addLines(array $linesToAdd)
    {
        foreach ($linesToAdd as $lineToAdd) {
            if (!$this->metroLinesManager::add($lineToAdd)->isSuccess()) {
                $this->log()->error('Line add error', $lineToAdd);
                throw new ArgumentException('Line add error');
            }
        }
    }

    /**
     * @param array  $lines
     * @param string $locationId
     *
     * @return array
     */
    protected function getAddLines(array $lines, string $locationId)
    {
        $map = [];
        foreach ($lines as $line) {
            $map[mb_strtolower($line['name'])] = $line;
        }


        $linesData = $this->metroLinesManager::query()
            ->addSelect('ID')
            ->addSelect('LOWER_NAME')
            ->whereIn(
                new ExpressionField(
                    'LOWER_NAME',
                    'LOWER(%s)',
                    'UF_NAME'
                ),
                array_keys($map)
            )
            ->registerRuntimeField(new ExpressionField(
                'LOWER_NAME',
                'LOWER(%s)',
                'UF_NAME'
            ))
            ->exec()
            ->fetchAll();

        $shouldAdd = array_diff_key(
            $map,
            array_flip(array_map(function ($lineData) {
                return $lineData['LOWER_NAME'];
            }, $linesData))
        );

        return array_map(function ($data) use ($locationId) {
            return [
                'UF_XML_ID'        => $this->slugify->slugify($data['name']),
                'UF_NAME'          => $data['name'],
                'UF_COLOUR_CODE'   => mb_strtolower($data['hex_color']),
                'UF_CITY_LOCATION' => $locationId,
            ];
        }, $shouldAdd);
    }

    /**
     * @param array $metrosToAdd
     *
     * @throws ArgumentException
     */
    protected function addMetros(array $metrosToAdd)
    {
        foreach ($metrosToAdd as $metroToAdd) {
            if (!$this->metroManager::add($metroToAdd)->isSuccess()) {
                $this->log()->error('Metro add error', $metroToAdd);
                throw new ArgumentException('Metro add error');
            }
        }
    }

    /**
     * @param array $lines
     *
     * @throws ArgumentException
     * @return array
     */
    protected function getAddMetros(array $lines)
    {
        foreach ($lines as $index => $line) {
            $lines[$index]['stations'] = array_map(function ($station) use ($line) {
                $station['line'] = mb_strtolower($line['name']);
                return $station;
            }, $line['stations']);
        }
        $metros = array_merge(...array_map(function ($line) {
            return $line['stations'];
        }, $lines));
        $map = [];
        foreach ($metros as $metro) {
            $map[mb_strtolower($metro['name'])] = $metro;
        }

        $metroData = $this->metroManager::query()
            ->addSelect('LOWER_NAME')
            ->whereIn(
                new ExpressionField(
                    'LOWER_NAME',
                    'LOWER(%s)',
                    'UF_NAME'
                ),
                array_keys($map)
            )
            ->registerRuntimeField(new ExpressionField(
                'LOWER_NAME',
                'LOWER(%s)',
                'UF_NAME'
            ))
            ->exec()
            ->fetchAll();

        $lines = $this->metroLinesManager::query()
            ->addSelect('ID')
            ->addSelect('LOWER_NAME')
            ->registerRuntimeField('LOWER_NAME', new ExpressionField(
                'LOWER_NAME',
                'LOWER(%s)',
                'UF_NAME'
            ))
            ->exec()
            ->fetchAll();
        $linesMap = [];
        foreach ($lines as $line) {
            $linesMap[$line['LOWER_NAME']] = $line['ID'];
        }


        $shouldAdd = array_diff_key(
            $map,
            array_flip(array_map(function ($lineData) {
                return $lineData['LOWER_NAME'];
            }, $metroData))
        );

        $wrongBranches = array_filter(
            array_map(function ($data) {
                return $data['line'];
            }, $shouldAdd),
            function ($lineName) use ($linesMap) {
                return !array_key_exists($lineName, $linesMap);
            }
        );

        if (\count($wrongBranches) > 0) {
            $this->log()->error('Not found lines', $wrongBranches);
            throw new ArgumentException('Not found lines');
        }

        return array_map(function ($data) use ($linesMap) {
            return [
                'UF_XML_ID' => $this->slugify->slugify($data['name']),
                'UF_NAME'   => $data['name'],
                'UF_BRANCH' => $linesMap[$data['line']],
            ];
        }, $shouldAdd);
    }

    /**
     * @param int $cityId
     *
     * @throws \Symfony\Component\Console\Exception\RuntimeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return array
     */
    protected function getLines(int $cityId): array
    {
        $content = $this->client->request('GET', (string)$cityId)->getBody()->getContents();
        $data = \GuzzleHttp\json_decode($content, true);
        if (!\is_array($data)) {
            throw new RuntimeException('Wrong HH response');
        }

        if (!($data['lines'] ?? [])) {
            throw new RuntimeException('No lines was returned');
        }
        return $data['lines'];
    }
}
