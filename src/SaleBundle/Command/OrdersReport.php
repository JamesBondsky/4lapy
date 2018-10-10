<?php

namespace FourPaws\SaleBundle\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Internals\OrderPropsValueTable;
use Bitrix\Sale\Internals\OrderTable;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Serializer;

/**
 * Class OrdersReport
 *
 * @package FourPaws\SaleBundle\Command
 */
class OrdersReport extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /** @var string DATE_FROM */
    protected const DATE_FROM     = 'from';
    protected const ONLINE_ORDER  = 'online_order';
    protected const REPORT_FOLDER = '/upload/reports/';

    /** @var Serializer $serializer */
    private $serializer;
    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * OrdersReport constructor.
     *
     * @param Serializer $serializer
     * @param Filesystem $filesystem
     *
     * @throws LogicException
     */
    public function __construct(Serializer $serializer, Filesystem $filesystem)
    {
        $this->serializer = $serializer;
        $this->fileSystem = $filesystem;

        parent::__construct();
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function configure(): void
    {
        $this->setName('fourpaws:sale:orders:report')
             ->setDescription(
                 'Creates an orders report since given date'
             )
             ->setDefinition([
                 new InputArgument(static::DATE_FROM, InputArgument::OPTIONAL, 'Date from you want to get orders report (d.m.Y)', '01.07.2018'),
                 new InputArgument(static::ONLINE_ORDER, InputArgument::OPTIONAL, 'Which orders (online/offline) you want get (y/n) both when ignored'),
             ]);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws RuntimeException
     * @throws IOException
     * @throws SystemException
     * @throws ArgumentException
     * @throws InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $date = new \DateTime();
        $date->setTime(23, 59, 59);
        $dateFrom = $input->getArgument(static::DATE_FROM);
        $dateFrom = \DateTime::createFromFormat('d.m.Y', $dateFrom);
        $dateFrom->setTime(0, 0, 0);
        $onlineOrder = $input->getArgument(static::ONLINE_ORDER);
        $onlineOrder = $this->prepareOrderStatus($onlineOrder);

        $filter = $this->buildFilter($dateFrom, $date, $onlineOrder);

        $ordersArray = $this->getOrdersForReport($filter);

        $reportFilePath = $this->saveReport($ordersArray, $dateFrom, $date, $onlineOrder);

        $this->log()->info(
            sprintf(
                'Task finished! File - %s',
                $reportFilePath
            )
        );
    }


    /**
     * @param $onlineOrder
     *
     * @return bool|null
     */
    private function prepareOrderStatus($onlineOrder): ?bool
    {
        $valuesMap = ['y' => true, 'n' => false];
        if ($onlineOrder !== null) {
            $onlineOrder = strtolower($onlineOrder);
            if (!isset($valuesMap[$onlineOrder])) {
                throw new InvalidArgumentException('Please insert correct option!');
            }
            $onlineOrder = $valuesMap[$onlineOrder];
        }
        return $onlineOrder;
    }

    /**
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @param bool|null $onlineOrder
     *
     * @return array
     */
    private function buildFilter(\DateTime $dateFrom, \DateTime $dateTo, ?bool $onlineOrder): array
    {
        $filter = [
            '<DATE_INSERT' => $dateTo->format('d.m.Y H:i:s'),
            '>DATE_INSERT' => $dateFrom->format('d.m.Y H:i:s'),
        ];
        if ($onlineOrder === null) {
            return $filter;
        }

        if ($onlineOrder) {
            $filter['!MANZANA_PROP.VALUE'] = false;
        } else {
            $filter['MANZANA_PROP.VALUE'] = false;
        }

        return $filter;
    }


    /**
     * @param array $filter
     *
     * @return array
     * @throws ArgumentException
     */
    private function getOrdersForReport(array $filter): array
    {
        $ordersArray = [];
        $orders      = OrderTable::query()
                                 ->setSelect([
                                     'Номер заказа'       => 'ACCOUNT_NUMBER',
                                     'Дата'               => 'DATE',
                                     'Время'              => 'TIME',
                                     'Сумма'              => 'PRICE',
                                     'ID покупателя'      => 'USER_ID',
                                     'E-mail'             => 'USER.EMAIL',
                                     'Статус'             => 'STATUS.NAME',
                                     'ID чека из Манзаны' => 'MANZANA_PROP.VALUE',
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
                                 ->registerRuntimeField(
                                     'MANZANA_PROP',
                                     new ReferenceField(
                                         'MANZANA_PROP',
                                         OrderPropsValueTable::class,
                                         Query\Join::on('this.ID', 'ref.ORDER_ID')
                                                   ->where('ref.CODE', '=', 'MANZANA_NUMBER'),
                                         ['join_type' => 'LEFT']
                                     )
                                 )
                                 ->exec();

        while ($order = $orders->fetch()) {
            $ordersArray[] = $order;
        }
        return $ordersArray;
    }

    /**
     * @param array     $ordersArray
     * @param \DateTime $dateFrom
     * @param \DateTime $date
     * @param bool|null $onlineOrder
     *
     * @return string
     */
    private function saveReport(array $ordersArray, \DateTime $dateFrom, \DateTime $date, ?bool $onlineOrder): string
    {
        $csv            = $this->serializer->encode($ordersArray, 'csv');
        $ordersInReport = '';
        if ($onlineOrder !== null) {
            $ordersInReport = $onlineOrder ? '_online' : '_offline';
        }
        $fileName = sprintf('Report%s-%s%s.csv',
            $dateFrom->format('dmY'),
            $date->format('dmY'),
            $ordersInReport
        );
        $folder   = $_SERVER['DOCUMENT_ROOT'] . self::REPORT_FOLDER;
        $this->write($folder . $fileName, $csv, false);
        return self::REPORT_FOLDER . $fileName;
    }

    /**
     * @param string      $path
     * @param string      $result
     * @param bool        $append
     * @param string|null $encoding
     *
     * @throws IOException
     */
    protected function write(string $path, string $result, bool $append, string $encoding = null): void
    {
        if (null !== $encoding && $encoding !== mb_internal_encoding()) {
            $result = mb_convert_encoding($result, $encoding);
        }

        if ($append) {
            $data = \explode(PHP_EOL, $result);
            array_shift($data);
            $result = \implode(PHP_EOL, $data);

            $this->fileSystem->appendToFile($path, $result);
        } else {
            $this->fileSystem->dumpFile($path, $result);
        }
    }

}
