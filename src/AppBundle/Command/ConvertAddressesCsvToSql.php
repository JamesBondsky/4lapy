<?php

namespace FourPaws\Appbundle\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Location\Name\LocationTable;
use FourPaws\AppBundle\Exception\InvalidArgumentException;
use FourPaws\Migrator\Client\User;
use FourPaws\Migrator\Entity\MapTable;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConvertAddressesCsvToSql extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    private const OPT_FILE = 'file';

    private const OPT_OUT_FILE = 'out';

    private const PROGRESS_BAR_FORMAT = ' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%';

    private $notFound = 0;

    /**
     * UpdateUserPasswords constructor.
     *
     * @param null|string $name
     */
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
    }

    /**
     * Configure command
     *
     * @throws InvalidArgumentException
     */
    protected function configure(): void
    {
        $this->setName('bitrix:user:update:address')
            ->setDescription('convert addresses csv to sql from file')
            ->addOption(
                self::OPT_FILE,
                'f',
                InputOption::VALUE_REQUIRED,
                'file path'
            )
            ->addOption(
                self::OPT_OUT_FILE,
                'o',
                InputOption::VALUE_REQUIRED,
                'out file path'
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws SystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->log()->info('Convert started');
        $fileName = $input->getOption(self::OPT_FILE);
        $file = realpath($fileName);
        if (!$file) {
            $file = realpath(__DIR__ . '/../' . $fileName);
            if (!$file) {
                if (!isset($_SERVER['DOCUMENT_ROOT'])) {
                    $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../..');
                }
                $file = realpath($_SERVER['DOCUMENT_ROOT'] . '/' . $fileName);
                if (!$file) {
                    $file = $fileName;
                }
            }
        }

        $fileName = $input->getOption(self::OPT_OUT_FILE);
        $outFile = realpath($fileName);
        if (!$outFile) {
            $outFile = realpath(__DIR__ . '/../' . $fileName);

            if (!$outFile) {
                if (!isset($_SERVER['DOCUMENT_ROOT'])) {
                    $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../..');
                }

                $outFile = realpath($_SERVER['DOCUMENT_ROOT'] . '/' . $fileName);
                if (!$outFile) {
                    $outFile = $fileName;
                }
            }
        }

        if (!file_exists($file)) {
            throw new InvalidArgumentException('file not found');
        }

        if (!$fp = fopen($file, 'rb')) {
            throw new InvalidArgumentException('cannot open file');
        }

        if (!$fpo = fopen($outFile, 'wb+')) {
            throw new InvalidArgumentException('cannot create output file');
        }

        $data = [];
        while ([$oldUserId, $profileId, $profileName, $code, $value] = \fgetcsv($fp)) {
            if (!isset($data[$oldUserId][$profileId])) {
                $main = isset($data[$oldUserId]) ? 0 : 1;
                $data[$oldUserId][$profileId] = [
                    'PROFILE_NAME' => $profileName,
                    'OLD_USER_ID'  => $oldUserId,
                    'FIELDS'       => [],
                ];
                $data[$oldUserId][$profileId]['FIELDS']['UF_MAIN'] = $main;
            }

            if($code !== 'UF_MAIN') {
                $data[$oldUserId][$profileId]['FIELDS'][$code] = $value;
            }
        }
        $progressBar = new ProgressBar($output, \count($data));
        $progressBar->setFormat(self::PROGRESS_BAR_FORMAT);
        $progressBar->start();

        $size = 5000;
        $chunks = array_chunk($data, $size, true);
        unset($data);
        foreach ($chunks as $data) {
            $this->processData($data, $fpo);
            $progressBar->advance($size);
        }
        $progressBar->finish();

        $this->log()->info(sprintf(
            'Update complete. Not found %s.',
            $this->notFound
        ));
    }

    /**
     * @param array $data
     * @param       $fpo
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function processData(array $data, $fpo)
    {
        if (empty($data)) {
            return;
        }

        $users = $this->getUsers(array_keys($data));
        $notFoundUsers = [];
        foreach ($data as $oldUserId => $profiles) {
            $i = 0;
            foreach ($profiles as $profileId => $profile) {
                $i++;

                foreach ($profile['FIELDS'] as &$field) {
                    $field = trim($field);
                    if(!empty($field)){
                        $field = trim(\addslashes($field));
                    }
                }

                unset($field);

                $notFoundUsers[] = $oldUserId;
                if (!isset($users[$oldUserId])) {
                    $this->notFound++;
                } else {
                    $cityLocationUnFormatted = trim($profile['FIELDS']['DELIVERY_CITY']);
                    $cityLocation = $cityLocationUnFormatted ? '"' . $cityLocationUnFormatted . '"' : '';
                    $city = trim($profile['FIELDS']['TOWN']);
                    $city = $city ? '"' . $city . '"' : '';
                    if (empty($city) && !empty($cityLocationUnFormatted)) {
                        $res = LocationTable::query()->setSelect(['NAME'])->where('LOCATION.CODE',
                            $cityLocationUnFormatted)->setLimit(1)->exec();
                        if ($res->getSelectedRowsCount() > 0) {
                            $city = '"' . $res->fetch()['NAME'] . '"';
                        }
                    }
                    $comments = '';
                    // customer_notes и DETAILS - примечание
                    // delivery_address - адрес доставки
                    if (!empty($profile['FIELDS']['delivery_address'])) {
                        $comments = '"' . $profile['FIELDS']['delivery_address'] . '"';
                    }
                    if (empty($comments) && !empty($profile['FIELDS']['customer_notes'])) {
                        $comments = '"' . $profile['FIELDS']['customer_notes'] . '"';
                    }
                    if (empty($comments) && !empty($profile['FIELDS']['DETAILS'])) {
                        $comments = '"' . $profile['FIELDS']['DETAILS'] . '"';
                    }
                    $values = [
                        'UF_USER_ID'       => '"' . $users[$oldUserId] . '"',
                        'UF_NAME'          => '"' . \addslashes(trim($profile['PROFILE_NAME'])) . '"',
                        'UF_CITY_LOCATION' => $cityLocation,
                        'UF_CITY'          => $city,
                        'UF_STREET'        => $profile['FIELDS']['STREET'] ? '"' . $profile['FIELDS']['STREET'] . '"' : '',
                        'UF_HOUSE'         => $profile['FIELDS']['HOME'] ? '"' . $profile['FIELDS']['HOME'] . '"' : '',
                        'UF_HOUSING'       => $profile['FIELDS']['CORP'] ? '"' . $profile['FIELDS']['CORP'] . '"' : '',
                        'UF_ENTRANCE'      => $profile['FIELDS']['POD'] ? '"' . $profile['FIELDS']['POD'] . '"' : '',
                        'UF_FLOOR'         => $profile['FIELDS']['ETAG'] ? '"' . $profile['FIELDS']['ETAG'] . '"' : '',
                        'UF_FLAT'          => $profile['FIELDS']['KVART'] ? '"' . $profile['FIELDS']['KVART'] . '"' : '',
                        'UF_MAIN'          => $profile['FIELDS']['UF_MAIN'],
                        'UF_DETAILS'       => $comments,
                    ];

                    \TrimArr($values, true);

                    if (!empty($values)) {
                        \fwrite($fpo,
                            \sprintf('INSERT INTO %s (%s) VALUES(%s);%s',
                                'adv_adress',
                                implode(',', \array_keys($values)),
                                implode(',', \array_values($values)),
                                PHP_EOL
                            )
                        );
                    }
                }
            }
        }
        if(!empty($notFoundUsers)) {
            $this->log()->warning(sprintf('user with externalIds not found: %s', implode(', ', $notFoundUsers)));
        }
    }

    /**
     * @param $externalIds
     *
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function getUsers(array $externalIds): array
    {
        $result = [];

        $res = MapTable::query()
            ->where('ENTITY', User::ENTITY_NAME)
            ->whereIn('EXTERNAL_ID', $externalIds)
            ->setSelect(['EXTERNAL_ID', 'INTERNAL_ID'])
            ->exec();
        while ($user = $res->fetch()) {
            $result[(int)$user['EXTERNAL_ID']] = (int)$user['INTERNAL_ID'];
        }

        return $result;
    }
}
