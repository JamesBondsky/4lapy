<?php

namespace FourPaws\Appbundle\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Db\SqlQueryException;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserTable;
use FourPaws\AppBundle\Exception\InvalidArgumentException;
use FourPaws\Migrator\Client\User;
use FourPaws\Migrator\Entity\MapTable;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MigrateData
 *
 * @package FourPaws\Console\Command
 *
 * Миграция данных со старого сайта из консоли
 */
class UpdateUserPasswords extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    private const OPT_FILE = 'file';

    private const PROGRESS_BAR_FORMAT = ' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%';

    private $updated = 0;

    private $notFound = 0;

    /**
     * UpdateUserPasswords constructor.
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
        $this->setName('bitrix:user:update:passwords')
            ->setDescription('Update user passwords from file')
            ->addOption(
                self::OPT_FILE,
                'f',
                InputOption::VALUE_REQUIRED,
                'file path'
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @throws SystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->log()->info('Update started');
        $file = $input->getOption(self::OPT_FILE);
        if (!file_exists($file)) {
            throw new InvalidArgumentException('file not found');
        }

        if (!$fp = fopen($file, 'rb')) {
            throw new InvalidArgumentException('cannot open file');
        }

        $lineCount = 0;
        while (fgets($fp)) {
            $lineCount++;
        }

        $progressBar = new ProgressBar($output, $lineCount);
        $progressBar->setFormat(self::PROGRESS_BAR_FORMAT);

        rewind($fp);
        $progressBar->start();

        $data = [];
        while ([$externalId, $hash] = fgetcsv($fp)) {
            $data[$externalId] = $hash;
            if (\count($data) >= 500) {
                $this->processData($data);
                $progressBar->advance(500);
                $data = [];
            }
        }
        $this->processData($data);
        $progressBar->finish();

        $this->log()->info(sprintf(
            'Update complete. Updated %s. Not found %s.',
            $this->updated,
            $this->notFound
        ));
    }

    /**
     * @param array $data
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SqlQueryException
     * @throws SystemException
     */
    protected function processData(array $data) {
        if (empty($data)) {
            return;
        }

        $users = $this->getUsers(array_keys($data));

        foreach ($data as $externalId => $hash) {

            if (!isset($users[$externalId])) {
                $this->log()->warning(sprintf('user with externalId %s not found', $externalId));
                $this->notFound++;
            } else {
                Application::getConnection()->query(
                    sprintf('UPDATE b_user SET PASSWORD = "%s" WHERE ID = %s', $hash, $users[$externalId])
                );
                $this->updated++;
            }
        }
    }

    /**
     * @param $externalIds
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function getUsers($externalIds) {
        $users = UserTable::query()->setSelect(['ID', 'MAP.EXTERNAL_ID'])
            ->setFilter([
                'MAP.ENTITY'      => User::ENTITY_NAME,
                'MAP.EXTERNAL_ID' => $externalIds,
            ])
            ->registerRuntimeField(
                new ReferenceField(
                    'MAP',
                    MapTable::class,
                    ['=this.ID' => 'ref.INTERNAL_ID'],
                    ['join_type' => 'INNER']
                )
            )->exec()
            ->fetchAll();

        $result = [];
        foreach ($users as $user) {
            $result[$user['MAIN_USER_MAP_EXTERNAL_ID']] = $user['ID'];
        }

        return $result;
    }
}
