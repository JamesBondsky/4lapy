<?php

namespace FourPaws\SaleBundle\Service\Reports;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Internals\BasketTable;
use Bitrix\Sale\Internals\OrderTable;
use FourPaws\SaleBundle\Dto\Reports\ReportResult;
use FourPaws\SaleBundle\Dto\Reports\RROrderReport\Order;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Exception\RuntimeException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Serializer;

class RROrderReportService
{
    protected const CHUNK_SIZE = 5000;

    protected const STEP_ALL   = 0;
    protected const STEP_FIRST = 1;

    /**
     * @var ArrayTransformerInterface
     */
    protected $arrayTransformer;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * AvailabilityReportService constructor.
     *
     * @param Filesystem                $filesystem
     * @param ArrayTransformerInterface $arrayTransformer
     * @param Serializer                $serializer
     */
    public function __construct(
        Filesystem $filesystem,
        ArrayTransformerInterface $arrayTransformer,
        Serializer $serializer
    )
    {
        $this->serializer = $serializer;
        $this->fileSystem = $filesystem;
        $this->arrayTransformer = $arrayTransformer;
    }

    /**
     * @param string      $path
     * @param int         $step
     * @param \DateTime   $from
     *
     * @return ReportResult
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws IOException
     * @throws RuntimeException
     * @throws UnexpectedValueException
     */
    public function export(string $path, int $step, \DateTime $from): ReportResult
    {
        $orderIds = $this->getOrderIds($from);
        $countTotal = \count($orderIds);
        $orderIds = \array_chunk($orderIds, static::CHUNK_SIZE);
        $stepCount = \count($orderIds);

        if ($step !== static::STEP_ALL) {
            $currentStep = $step - 1;
            $orderIds = [$orderIds[$currentStep]];
        } else {
            $currentStep = static::STEP_ALL;
        }

        $append = $currentStep !== 0;
        $countProcessed = 0;

        foreach ($orderIds as $chunk) {
            if (!$chunk) {
                continue;
            }

            $data = $this->getOrderData($chunk);

            $result = [];
            foreach ($data as $order) {
                $result[] = $this->arrayTransformer->toArray($order);
            }
            $this->write(
                $path,
                $this->serializer->encode($result, 'csv', ['csv_delimiter' => ';']),
                $append
            );
            $currentStep++;
            $countProcessed += \count($chunk);
            $append = true;
        }

        return (new ReportResult())
            ->setCountProcessed($countProcessed)
            ->setCountTotal($countTotal)
            ->setProgress($currentStep / $stepCount);
    }

    /**
     * @param string      $path
     * @param string      $result
     * @param bool        $append
     *
     * @throws IOException
     */
    protected function write(string $path, string $result, bool $append): void
    {
        $data = \explode(PHP_EOL, $result);
        array_shift($data);
        $result = \implode(PHP_EOL, $data);

        if ($append) {
            $this->fileSystem->appendToFile($path, $result);
        } else {
            $this->fileSystem->dumpFile($path, $result);
        }
    }

    /**
     * @param \DateTime $dateFrom
     *
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function getOrderIds(\DateTime $dateFrom): array
    {
        $query = OrderTable::query()
                           ->setSelect(['ID'])
                           ->setFilter([
                               '>DATE_INSERT' => $dateFrom->format('d.m.Y H:i:s'),
                           ]);

        return \array_column($query->exec()->fetchAll(), 'ID');
    }

    /**
     * @param array $orderIds
     *
     * @return Order[]
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function getOrderData(array $orderIds): array
    {
        $query = BasketTable::query()
                            ->setFilter(['ORDER_ID' => $orderIds])
                            ->setSelect([
                                'ORDER_NUMBER' => 'ORDER.ACCOUNT_NUMBER',
                                'ARTICLE'      => 'ELEMENT.XML_ID',
                                'USER_ID'      => 'ORDER.USER_ID',
                                'ORDER_DATE'   => 'ORDER.DATE_INSERT',
                            ])
                            ->setOrder(['ORDER_ID' => 'ASC'])
                            ->registerRuntimeField(
                                new ReferenceField(
                                    'ORDER',
                                    OrderTable::class,
                                    ['=this.ORDER_ID' => 'ref.ID'],
                                    ['join_type' => 'INNER']
                                )
                            )
                            ->registerRuntimeField(
                                new ReferenceField(
                                    'ELEMENT',
                                    ElementTable::class,
                                    ['=this.PRODUCT_ID' => 'ref.ID'],
                                    ['join_type' => 'INNER']
                                )
                            );

        $result = [];

        $rows = $query->exec();
        while ($row = $rows->fetch()) {
            /** @var DateTime $date */
            $date = $row['ORDER_DATE'];
            $result[] = (new Order())
                ->setProductXmlId($row['ARTICLE'])
                ->setOrderNumber($row['ORDER_NUMBER'])
                ->setUserId($row['USER_ID'])
                ->setDate(new \DateTimeImmutable('@' . $date->getTimestamp()));
        }

        return $result;
    }
}
