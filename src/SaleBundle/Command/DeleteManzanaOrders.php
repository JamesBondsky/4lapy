<?php

namespace FourPaws\SaleBundle\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Internals\OrderPropsValueTable;
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
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DeleteManzanaOrders
 *
 * @package FourPaws\SaleBundle\Command
 */
class DeleteManzanaOrders extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

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
            ->setDescription('Delete all manzana orders');
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @param InputInterface $input
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
        $this->log()->info('Deleting manzana orders...');

        Event::disableEvents();

        $orders = OrderPropsValueTable::query()
            ->setSelect(['ORDER_ID'])
            ->setOrder(['ORDER_ID' => 'DESC'])
            ->setFilter(
                [
                    'CODE' => 'MANZANA_NUMBER',
                ]
            )
            ->exec();

        $deleted = 0;
        while ($manzanaOrder = $orders->fetch()) {
            $order = Order::load($manzanaOrder['ORDER_ID']);
            if (!$order) {
                $this->log()->warning(sprintf('Order #%s not found', $manzanaOrder['ORDER_ID']));
                continue;
            }

            /** @var Payment $payment */
            foreach ($order->getPaymentCollection() as $payment) {
                $payment->setPaid('N');
            }
            $order->save();

            $result = Order::delete($order->getId());
            if (!$result->isSuccess()) {
                $this->log()->error(
                    sprintf(
                        'Failed to delete order #%s: %s',
                        $order->getId(),
                        \implode('. ', $result->getErrorMessages())
                    )
                );
            }

            $deleted++;
            $this->log()->debug(
                \sprintf('Deleted order #%s, (memory: %s)', $order->getId(), memory_get_usage(true))
            );
        }

        Event::enableEvents();

        $this->log()->info(\sprintf('Deleted %s orders', $deleted));
    }
}
