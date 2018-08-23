<?php

namespace FourPaws\SaleBundle\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Internals\OrderPropsValueTable;
use Bitrix\Sale\Internals\OrderTable;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Exception;
use FourPaws\SaleBundle\EventController\Event;
use Psr\Log\LoggerAwareInterface;
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

    protected const OPT_TYPE         = 'type';
    protected const OPT_SELECT_LIMIT = 'limit';

    protected const TYPE_ALL        = 'all';
    protected const TYPE_DUPLICATES = 'duplicates';
    protected const TYPE_ZERO_PRICE = 'zero_price';

    protected const TYPES = [
        self::TYPE_ALL,
        self::TYPE_DUPLICATES,
        self::TYPE_ZERO_PRICE,
    ];

    /**
     * @var int
     */
    protected $deleteCount = 0;

    /**
     * DeleteManzanaOrders constructor.
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
            ->setDescription('Deletes orders from manzana')
            ->addOption(
                static::OPT_TYPE,
                't',
                InputOption::VALUE_REQUIRED,
                'Type of deletion: all, duplicates, zero_price'
            )
            ->addOption(
                static::OPT_SELECT_LIMIT,
                'l',
                InputOption::VALUE_OPTIONAL,
                'SQL select limit'
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
        $type = $input->getOption(static::OPT_TYPE);
        $limit = (int)$input->getOption(static::OPT_SELECT_LIMIT);

        Event::disableEvents();

        switch ($type) {
            case self::TYPE_ALL:
                $this->log()->info('Deleting all manzana orders...');
                $query = $this->getClearAllOrdersQuery();
                break;
            case self::TYPE_DUPLICATES:
                $this->log()->info('Deleting manzana order duplicates...');
                $query = $this->getClearDuplicatesQuery();
                break;
            case self::TYPE_ZERO_PRICE:
                $this->log()->info('Deleting manzana zero price orders...');
                $query = $this->getClearZeroPriceOrdersQuery();
                break;
            default:
                throw new \InvalidArgumentException(
                    \sprintf('"--type" option must be one of: %s', implode(', ', self::TYPES))
                );
        }

        if ($limit) {
            $query->setLimit($limit);
        }

        $orders = $query->exec();

        while ($order = $orders->fetch()) {
            $this->deleteOrder($order['ORDER_ID']);
        }

        Event::enableEvents();

        $this->log()->info(\sprintf('Deleted %s orders with type "%s"', $this->deleteCount, $type));
    }

    /**
     * @return Query
     * @throws ArgumentException
     * @throws SystemException
     */
    protected function getClearDuplicatesQuery(): Query
    {
        $query = OrderPropsValueTable::query()
            ->setSelect(['ORDER_ID'])
            ->setFilter([
                '=CODE'  => 'MANZANA_NUMBER',
                '!VALUE' => false,
            ])
            ->registerRuntimeField(new ReferenceField(
                'ORDER_PROPS', OrderPropsValueTable::class,
                Query\Join::on('this.VALUE', 'ref.VALUE')
                    ->where('this.ORDER_ID', '!=', new Query\Filter\Expression\Column('ref.ORDER_ID'))
                    ->where('ref.CODE', '=', 'MANZANA_NUMBER'),
                ['join_type' => 'INNER']
            ));

        return $query;
    }

    /**
     * @return Query
     * @throws ArgumentException
     * @throws SystemException
     */
    protected function getClearZeroPriceOrdersQuery(): Query
    {
        $query = OrderPropsValueTable::query()
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
            );

        return $query;
    }

    /**
     * @return Query
     * @throws ArgumentException
     * @throws SystemException
     */
    protected function getClearAllOrdersQuery(): Query
    {
        $query = OrderPropsValueTable::query()
            ->setSelect(['ORDER_ID'])
            ->setOrder(['ORDER_ID' => 'DESC'])
            ->setFilter(
                [
                    'CODE'   => 'MANZANA_NUMBER',
                    '!VALUE' => false,
                ]
            );

        return $query;
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
