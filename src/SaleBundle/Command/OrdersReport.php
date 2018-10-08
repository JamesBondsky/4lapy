<?php

namespace FourPaws\SaleBundle\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Sale\Internals\OrderTable;
use Bitrix\Sale\Internals\StatusLangTable;
use Symfony\Component\Serializer\Serializer;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class OrdersReport extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /** @var string DATE_FROM */
    protected const DATE_FROM = 'from';

    protected const REPORT_FOLDER = '/upload/';

    /** @var Serializer $serializer */
    private $serializer;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * OrdersReport constructor.
     *
     * @param null $name
     *
     * @throws LogicException
     */
    public function __construct(Serializer $serializer, Filesystem $filesystem, $name = null)
    {
        parent::__construct($name);
        $this->serializer = $serializer;
        $this->fileSystem = $filesystem;
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function configure(): void
    {
        $this->setName('fourpaws:sale:orders:report')
             ->setDescription('Creates an orders report since given date')
             ->setDefinition([
                 new InputArgument(static::DATE_FROM, InputArgument::OPTIONAL, 'Date from you want to get orders report', date('01.07.2018')),
             ]);
    }


    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $date = new \DateTime();
        $date->setTime(23, 59, 59);
        $dateFrom = $input->getArgument(static::DATE_FROM);
        $dateFrom = \DateTime::createFromFormat('d.m.Y', $dateFrom);

        $filter = [
            '<DATE_INSERT' => $date->format('d.m.Y H:i:s'),
            '>DATE_INSERT' => $dateFrom->format('d.m.Y H:i:s'),
        ];

        $ordersArray = [];
        $orders      = OrderTable::query()->setSelect([
            'Номер заказа' => 'ACCOUNT_NUMBER',
            'Дата' => 'DATE',
            'Время' => 'TIME',
            'Сумма' => 'PRICE',
            'ID покупателя' => 'USER_ID',
            'E-mail' => 'USER.EMAIL',
            'Статус' => 'STATUS.NAME',
        ])
                                 ->setFilter($filter)
                                 ->registerRuntimeField(
                                     'DATE',
                                     new ExpressionField(
                                         'DATE',
                                         'DATE_FORMAT(%s, "%%d.%%m.%%Y")',
                                         ['DATE_INSERT']
                                    )
                                 )
                                 ->registerRuntimeField(
                                     'TIME',
                                     new ExpressionField(
                                         'TIME',
                                         'DATE_FORMAT(%s, "%%H:%%i:%%s")',
                                         ['DATE_INSERT']
                                    )
                                 )
                                 ->exec();
        while ($order = $orders->fetch()) {
            $ordersArray[] = $order;
        }

        $csv  = $this->serializer->encode($ordersArray, 'csv');
        $path = sprintf('Report%s-%s.csv',
            $dateFrom->format('dmY'),
            $date->format('dmY')
        );
        $folder = $_SERVER['DOCUMENT_ROOT'] . self::REPORT_FOLDER;
        $this->write($folder . $path, $csv, false);


        $this->log()->info('Task finished');
    }

    protected function write(string $path, string $result, bool $append, string $encoding = null): void
    {
        if (null !== $encoding && $encoding !== mb_internal_encoding()) {
            $result = mb_convert_encoding($result, $encoding);
        }

        if ($append) {
            // удаление заголовка
            $data = \explode(PHP_EOL, $result);
            array_shift($data);
            $result = \implode(PHP_EOL, $data);

            $this->fileSystem->appendToFile($path, $result);
        } else {
            $this->fileSystem->dumpFile($path, $result);
        }
    }

}