<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\AppBundle\Command;

use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Sale\Internals\OrderTable;
use Bitrix\Sale\Internals\PaySystemActionTable;
use Bitrix\Sale\Order;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\SaleBundle\Service\OrderService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;

class OrderPaySystemChange extends Command
{
    protected const TIME_TO_PAY = 1200; // 20 minutes

    protected const OPT_TIME = 'time';

    /**
     * @var OrderService
     */
    protected $orderService;

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
            );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = $input->getOption(self::OPT_TIME) ?: static::TIME_TO_PAY;

        $date = new \DateTime();
        $date->setTimestamp(time() - $time);
        $orders = OrderTable::query()->setSelect(['ID'])
            ->setFilter([
                '<DATE_INSERT' => $date->format('d.m.Y H:i:s'),
                'PAYED' => 'N',
                'PAYMENT_SYSTEM.CODE' => OrderService::PAYMENT_ONLINE
            ])->registerRuntimeField(
                new ReferenceField(
                    'PAYMENT_SYSTEM',
                    PaySystemActionTable::class,
                    ['=this.PAY_SYSTEM_ID' => 'ref.ID'],
                    ['join_type' => 'INNER']
                )
            )->exec();

        while ($order = $orders->fetch()) {
            $this->orderService->processPaymentError(Order::load($order['ID']));
        }
    }
}
