<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\AppBundle\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Sale\Internals\OrderTable;
use Bitrix\Sale\Internals\PaySystemActionTable;
use Bitrix\Sale\Order;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\SaleBundle\Enum\OrderPayment;
use FourPaws\SaleBundle\Exception\SberbankOrderNotFoundException;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\PaymentService;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;

class OrderPaySystemChange extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    protected const TIME_TO_PAY = 1200; // 20 minutes

    protected const MAX_TIME = 86400; // 1 day

    protected const OPT_MAX_TIME = 'max';

    protected const OPT_TIME = 'time';

    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * @var PaymentService
     */
    protected $paymentService;

    /**
     * OrderPaySystemChange constructor.
     *
     * @param null|string $name
     *
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(string $name = null)
    {
        parent::__construct($name);
        $this->orderService = Application::getInstance()->getContainer()->get(OrderService::class);
        $this->paymentService = Application::getInstance()->getContainer()->get(PaymentService::class);
    }

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this
            ->setName('fourpaws:order:paysystem:change')
            ->setDescription('Changes payment type from online to cash if order haven\'t been paid in time')
            ->addOption(
                self::OPT_TIME,
                't',
                InputOption::VALUE_OPTIONAL,
                'Time in seconds before online payment changed to cash'
            )
            ->addOption(
                self::OPT_MAX_TIME,
                'm',
                InputOption::VALUE_OPTIONAL,
                'Maximum difference between order create date and current date'
            );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = $input->getOption(self::OPT_TIME) ?: static::TIME_TO_PAY;
        $maxTime = $input->getOption(self::OPT_MAX_TIME) ?: static::MAX_TIME;

        $date = new \DateTime();
        $date->setTimestamp(time() - $time);
        $maxDate = new \DateTime();
        $maxDate->setTimestamp(time() - $maxTime);

        $filter = [
            '<DATE_INSERT'        => $date->format('d.m.Y H:i:s'),
            '>DATE_INSERT'        => $maxDate->format('d.m.Y H:i:s'),
            '=PAYMENT_SYSTEM.CODE' => OrderPayment::PAYMENT_ONLINE,
            '!CANCELED'           => 'Y',
            '!PAYED'       => 'Y',
        ];

        $orders = OrderTable::query()->setSelect(['ID'])
            ->setFilter($filter)
            ->registerRuntimeField(
                new ReferenceField(
                    'PAYMENT_SYSTEM',
                    PaySystemActionTable::class,
                    ['=this.PAY_SYSTEM_ID' => 'ref.ID'],
                    ['join_type' => 'INNER']
                )
            )
            ->exec();

        while ($order = $orders->fetch()) {
            if (!$saleOrder = Order::load($order['ID'])) {
                continue;
            }

            if ($this->orderService->isOldSiteOrder($saleOrder)) {
                continue;
            }

            try {
                try {
                    $this->paymentService->processOnlinePaymentByOrderNumber($saleOrder);
                } catch (SberbankOrderNotFoundException $e) {
                    $this->paymentService->processOnlinePaymentError($saleOrder);
                }
            } catch (\Exception $e) {
                $this->log()->error(sprintf('%s: %s %s', \get_class($e), $e->getCode(), $e->getMessage()));
            }
            $this->log()->info(sprintf('Changed payment system for order: %s', $saleOrder->getId()));
        }

        $this->log()->info('Task finished.');
    }
}
