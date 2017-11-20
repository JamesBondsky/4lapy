<?php

namespace FourPaws\Console\Command;

use Bitrix\Highloadblock\HighloadBlockTable;
use FourPaws\App\Application;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateHlBlockServicesDefinition extends Command implements LoggerAwareInterface
{
    const HL_BLOCK_SERVICE_YML_FILE_PATH = '../app/config/services/bitrix-hl-blocks.yml';

    use LoggerAwareTrait;

    public function __construct()
    {
        parent::__construct();
        $this->setLogger(new Logger('HlBlockServiceGenerator', [new StreamHandler(STDOUT, Logger::DEBUG)]));
    }

    protected function configure()
    {
        $this->setName('services:generate-hblock')
             ->setDescription(
                 'Rewrite ' . self::HL_BLOCK_SERVICE_YML_FILE_PATH . ' with fresh definition of all existing HL-Blocks'
             );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $absolutePath = Application::getAbsolutePath(DIRECTORY_SEPARATOR . self::HL_BLOCK_SERVICE_YML_FILE_PATH);

        $this->log()->info('Идёт запись в yml файл...');
        $yml = fopen($absolutePath, 'wb+');

        if (false === $yml) {
            throw new RuntimeException('Ошибка открытия файла ' . self::HL_BLOCK_SERVICE_YML_FILE_PATH);
        }

        $header = <<<END
---

services:

  bx.hlblock.factory:
    class: 'Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory'

  bx.hlblock.base:
    class: '\SomeTable'
    factory: 'bx.hlblock.factory:createTableObject'


END;

        fputs($yml, $header);

        $dbHlBlockList = HighloadBlockTable::query()->setSelect(['*'])->exec();
        while ($arHlBlock = $dbHlBlockList->fetch()) {

            $name = trim($arHlBlock['NAME']);
            $nameLower = strtolower($name);

            $hlBlockDefinition = <<<END
  bx.hlblock.{$nameLower}:
    class: '{$name}Table'
    arguments: ['{$name}']
    parent: bx.hlblock.base


END;

            fputs($yml, $hlBlockDefinition);

        }

        fclose($yml);

        $this->log()->info(sprintf('Обработано HL-блоков: %d', $dbHlBlockList->getSelectedRowsCount()));
        $this->log()->notice(
            sprintf(
                'Проверьте файл `%s`',
                $absolutePath
            )
        );
        $this->log()->notice('и если всё в порядке, не забудьте его закомитить!');
    }

    /**
     * @return LoggerInterface
     */
    protected function log()
    {
        return $this->logger;
    }

}
