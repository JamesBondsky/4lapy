<?php

namespace FourPaws\SaleBundle\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Internals\OrderPropsValueTable;
use Bitrix\Sale\Internals\OrderTable;
use Bitrix\Sale\Internals\PaymentTable;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Exception;
use FourPaws\SaleBundle\EventController\Event;
use FourPaws\SaleBundle\Service\BasketRulesService;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DeleteManzanaOrders
 *
 * @package FourPaws\SaleBundle\Command
 */
class DeleteManzanaOrders extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    protected const OPT_ALL = 'all';

    /**
     * @var int
     */
    protected $deleteCount = 0;

    /**
     * DiscountResave constructor.
     *
     * @param null $name
     *
     * @throws LogicException
     */
    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function configure(): void
    {
        $this->setName('fourpaws:sale:order:manzana:delete')
            ->setDescription('Delete manzana orders')
            ->addOption(
                static::OPT_ALL,
                'a',
                InputOption::VALUE_NONE,
                'Delete all orders'
            );
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws Exception
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     * @throws ObjectException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $deleteAll = $input->getOption(static::OPT_ALL);

        Event::disableEvents();

        if ($deleteAll) {
            $this->log()->info('Deleting manzana orders...');

        } else {
            $this->log()->info('Deleting manzana order duplicates');
            $this->clearDuplicates();
            $this->log()->info('Deleting manzana zero price orders');
            $this->clearZeroPriceOrders();
        }

        Event::enableEvents();

        $this->log()->info(\sprintf('Deleted %s orders', $this->deleteCount));
    }

    /**
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     * @throws ObjectException
     * @throws ObjectNotFoundException
     * @throws \Exception
     */
    protected function clearDuplicates(): void
    {
        $duplicates = OrderPropsValueTable::query()
            ->setSelect([
                'VALUE',
                'CNT',
            ])
            ->setFilter([
                '=CODE'  => 'MANZANA_NUMBER',
                '!VALUE' => false,
            ])
            ->registerRuntimeField('CNT', [
                'data_type'  => 'integer',
                'expression' => ['COUNT(*)'],
            ])
            ->setOrder(['ORDER_ID' => 'ASC'])
            ->setGroup(['VALUE'])
            ->having('CNT', '>', '1')
            ->exec();

        while ($duplicate = $duplicates->fetch()) {
            $orderIds = $this->getDuplicates($duplicate['VALUE'], $duplicate['CNT'] - 1);
            foreach ($orderIds as $orderId) {
                $this->deleteOrder($orderId);
            }
        }
    }

    /**
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws \Exception
     */
    protected function clearZeroPriceOrders(): void
    {
        $zeroPriceOrders = OrderPropsValueTable::query()
            ->setSelect([
                'ORDER_ID',
                'VALUE',
                'ORDER.PRICE',
            ])
            ->setFilter([
                '=CODE'       => 'MANZANA_NUMBER',
                '!VALUE'      => false,
                'ORDER.PRICE' => 0,
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
            ->exec();

        while ($zeroPriceOrder = $zeroPriceOrders->fetch()) {
            $this->deleteOrder($zeroPriceOrder['ORDER_ID']);
        }
    }

    /**
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws Exception
     * @throws NotImplementedException
     * @throws ObjectException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function clearAllOrders(): void
    {
        $orders = OrderPropsValueTable::query()
            ->setSelect(['ORDER_ID'])
            ->setOrder(['ORDER_ID' => 'DESC'])
            ->setFilter(
                [
                    'CODE' => 'MANZANA_NUMBER',
                ]
            )
            ->exec();

        while ($manzanaOrder = $orders->fetch()) {
            $this->deleteOrder($manzanaOrder['ORDER_ID']);
        }
    }

    /**
     * @param string $manzanaNumber
     * @param int    $maxCount
     * @return array
     * @throws ArgumentException
     * @throws SystemException
     */
    protected function getDuplicates(string $manzanaNumber, $maxCount = 0): array
    {
        $query = OrderPropsValueTable::query()
            ->setSelect([
                'ORDER_ID',
            ])
            ->setFilter([
                'VALUE' => $manzanaNumber,
                'CODE'  => 'MANZANA_NUMBER',
            ]);

        if ($maxCount) {
            $query->setLimit($maxCount);
        }
        $orders = $query->exec();
        $result = [];
        while ($order = $orders->fetch()) {
            $result[] = $order['ORDER_ID'];
        }

        return $result;
    }

    /**
     * @param $orderId
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     * @throws ObjectException
     * @throws ObjectNotFoundException
     * @throws SystemException
     * @throws \Exception
     */
    protected function deleteOrder($orderId): void
    {
        if ($order = Order::load($orderId)) {
            /** @var Payment $payment */
            foreach ($order->getPaymentCollection() as $payment) {
                $payment->setPaid('N');
            }

            $order->save();

            $result = Order::delete($order->getId());
            if (!$result->isSuccess()) {
                $this->log()->error(
                    sprintf(
                        'Error deleting order #%s: %s',
                        $order->getId(),
                        \implode('. ', $result->getErrorMessages())
                    )
                );
            } else {
                $this->log()->debug(
                    sprintf(
                        'Deleted order #%s. Memory usage: %s',
                        $order->getId(),
                        memory_get_usage(true)
                    )
                );
                $this->deleteCount++;
            }
        } else {
            $this->log()->warning(sprintf('Order #%s not found', $orderId));
        }
    }
}
