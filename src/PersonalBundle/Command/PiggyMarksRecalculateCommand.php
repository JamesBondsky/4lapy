<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\Command;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ORM\Query\Filter\ConditionTree;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Internals\OrderPropsValueTable;
use Bitrix\Sale\Internals\OrderTable;
use Bitrix\Sale\Order;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use FourPaws\App\Application;
use FourPaws\AppBundle\Service\LockerInterface;
use FourPaws\Helpers\BxCollection;
use FourPaws\PersonalBundle\Service\PiggyBankService;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SapBundle\Pipeline\PipelineRegistry;
use Psr\Log\LoggerAwareInterface;
use function sprintf;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Bitrix\Main\Entity\Query;

/**
 * Class SapCommand
 *
 * @package FourPaws\SapBundle\Command
 */
class PiggyMarksRecalculateCommand extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    private const OPTION_DEBUG = 'debug';
    private const OPTION_DEBUG_SHORTCUT = 'd';

    private const OPTION_DATE_START = 'date_start';
    private const OPTION_DATE_START_SHORTCUT = 'ds';

    private const OPTION_DATE_FINISH = 'date_finish';
    private const OPTION_DATE_FINISH_SHORTCUT = 'df';

    private const DATE_FORMAT = 'd.m.Y H:i:s';

    /**
     * @var PipelineRegistry
     */
    protected $pipelineRegistry;
    /**
     * @var LockerInterface
     */
    private $lockerService;

    /**
     * ImportCommand constructor.
     *
     * @param PipelineRegistry $pipelineRegistry
     *
     * @throws LogicException
     */
    public function __construct(PipelineRegistry $pipelineRegistry)
    {
        $this->pipelineRegistry = $pipelineRegistry;

        parent::__construct();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function configure(): void
    {
        $this->setName('articul:piggy:marks:recalculate')
            ->setDescription('Recalculate piggy marks')
            ->addOption(
                self::OPTION_DEBUG,
                self::OPTION_DEBUG_SHORTCUT,
                InputOption::VALUE_OPTIONAL,
                'Debug - print selected orders to console without recalculate marks',
                false
            )
            ->addOption(
                self::OPTION_DATE_START,
                self::OPTION_DATE_START_SHORTCUT,
                InputOption::VALUE_REQUIRED,
                'Date start for select order insert/update in d.m.Y format'
            )
            ->addOption(
                self::OPTION_DATE_FINISH,
                self::OPTION_DATE_FINISH_SHORTCUT,
                InputOption::VALUE_REQUIRED,
                'Date finish for select order insert/update in d.m.Y format'
            );
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     * @throws ArgumentException
     * @throws NotImplementedException
     */
    public function execute(InputInterface $input, OutputInterface $output): bool
    {
        $debug = $input->getOption(self::OPTION_DEBUG);
        $dateStartStr = $input->getOption(self::OPTION_DATE_START);
        $dateFinishStr = $input->getOption(self::OPTION_DATE_FINISH);

        if ($dateStartStr === null || $dateFinishStr === null) {
            $this->log()->critical(sprintf(
                'date_start or date_finish is not set.'
            ));
            return false;
        }

        try {
            $dateStart = new DateTime($dateStartStr . ' 00:00:00', self::DATE_FORMAT);
            $dateFinish = new DateTime($dateFinishStr . ' 23:59:59', self::DATE_FORMAT);
            if ($dateFinish < $dateStart) {
                throw new Exception('dateFinish must be longer than dateStart', 500);
            }
        } catch (ObjectException|Exception $e) {
            $this->log()->critical(sprintf(
                $e->getMessage() . ' [' . $e->getCode() . ']'
            ));
            return false;
        }

        try {
            /** @var ArrayCollection $orders */
            $orders = $this->getOrders($dateStart, $dateFinish);
        } catch (Exception $e) {
            $this->log()->critical(sprintf(
                $e->getMessage() . ' [' . $e->getCode() . ']'
            ));
            return false;
        }

        $ordersCount = $orders->count();
        dump('Orders count: ' . $ordersCount);
        if ($debug) {
            /** @var Order $order */
            foreach ($orders as $order) {
                dump(
                    'ORDER_ID=' . $order->getId() .
                    ' IS_NEW_SITE_ORDER=' . BxCollection::getOrderPropertyByCode($order->getPropertyCollection(), 'IS_NEW_SITE_ORDER')->getValue() .
                    ' IS_MANZANA_ORDER=' . BxCollection::getOrderPropertyByCode($order->getPropertyCollection(), 'IS_MANZANA_ORDER')->getValue() .
                    ' DATE_INSERT=' . $order->getDateInsert() . ' DATE_UPDATE=' . $order->getField('DATE_UPDATE')
                );
            }
        } else {
            $i = 1;
            foreach ($orders as $order) {
                dump('Process for order ' . $order->getId() . ' started ' . $i++ . '/' . $ordersCount . '.');
                $this->piggy($order);
            }
        }

        return true;
    }

    /**
     * @param DateTime $dateStart
     * @param DateTime $dateFinish
     * @return ArrayCollection
     * @throws ArgumentException
     * @throws SystemException
     */
    public function getOrders(DateTime $dateStart, DateTime $dateFinish): ArrayCollection
    {
        $orderCollection = new ArrayCollection();

        $orders = OrderTable::query()
            ->setSelect(['ID'])
            ->where(Query::filter()
                ->logic('or')
                ->where(Query::filter()
                    ->logic('and')
                    ->where('IS_NEW_SITE_ORDER.VALUE', BitrixUtils::BX_BOOL_TRUE)
                    ->where('DATE_INSERT', '>=', $dateStart)
                    ->where('DATE_INSERT', '<=', $dateFinish)
                    ->where('DATE_UPDATE', '>=', $dateStart)
                    ->where('DATE_UPDATE', '<=', $dateFinish)
                )
                ->where(Query::filter()
                    ->logic('and')
                    ->where('IS_MANZANA_ORDER.VALUE', BitrixUtils::BX_BOOL_TRUE)
                    ->where('DATE_INSERT', '>=', $dateStart)
                    ->where('DATE_INSERT', '<=', $dateFinish)
                )
            )
            ->registerRuntimeField(
                'IS_NEW_SITE_ORDER',
                new ReferenceField(
                    'IS_NEW_SITE_ORDER',
                    OrderPropsValueTable::class,
                    (new ConditionTree())
                        ->where(
                            'ref.CODE', 'IS_NEW_SITE_ORDER'
                        )
                        ->whereColumn(
                            'this.ID', 'ref.ORDER_ID'
                        )
                )
            )
            ->registerRuntimeField(
                'IS_MANZANA_ORDER',
                new ReferenceField(
                    'IS_MANZANA_ORDER',
                    OrderPropsValueTable::class,
                    Query\Join::on('this.ID', 'ref.ORDER_ID')
                        ->where('ref.CODE', '=', 'IS_MANZANA_ORDER')
                )
            )
            ->exec();

        while ($order = $orders->fetch()) {
            $order = Order::load($order['ID']);
            $orderCollection->set($order->getId(), $order);
        }

        return $orderCollection;
    }


    /**
     * @param Order $order
     */
    protected function piggy(Order $order)
    {
        try {
            /** @var PiggyBankService $piggyBankService */
            $piggyBankService = Application::getInstance()->getContainer()->get('piggy_bank.service');

            if ($piggyBankService->isPiggyBankDateExpired()) {
                return;
            }

            /** @var PiggyBankService $piggyBankService */
            $piggyBankService = Application::getInstance()->getContainer()->get('piggy_bank.service');

            if ($order instanceof Order) {
                /** @var BasketService $basketService */
                $basketService = Application::getInstance()->getContainer()->get(BasketService::class);

                $basket = $order->getBasket();
                $items = $basket->getOrderableItems();
                if ($items->isEmpty()) {
                    return;
                }
                $offersIds = [];
                foreach ($items as $itemIndex => $item) {
                    $offersIds[] = $item->getProductId();
                }
                if ($offersIds) {
                    $itemsProps = $piggyBankService->fetchItems($offersIds);
                }

                $sumVetapteka = 0;
                $sumNotVetapteka = 0;
                /** @var BasketItem $item */
                foreach ($items as $itemIndex => $item) {
                    $productId = $item->getProductId();
                    //удаление марок
                    if (in_array($productId, $piggyBankService->getMarksIds(), false)) {
                        try {
                            $basket->deleteItem($item->getInternalIndex());
                        } catch (Exception $e) {
                        }

                        continue;
                    }

                    $price = $item->getPrice() * $item->getQuantity();
                    if ($itemsProps[$productId]['IS_VETAPTEKA']) {
                        $sumVetapteka += $price;
                    } else {
                        $sumNotVetapteka += $price;
                    }
                }

                $marksToAdd = floor($sumVetapteka / $piggyBankService::MARK_RATE) * $piggyBankService::MARKS_PER_RATE_VETAPTEKA;
                $marksToAdd += floor($sumNotVetapteka / $piggyBankService::MARK_RATE) * $piggyBankService::MARKS_PER_RATE;

                if ($marksToAdd > 0) {
                    $basketItem = $basketService->addOfferToBasket(
                        $piggyBankService->getVirtualMarkId(),
                        //$piggyBankService->getPhysicalMarkId(),
                        (int)$marksToAdd,
                        ['CURRENCY' => 'RUB'],
                        true,
                        $basket
                    );
                }
                $basket->save();
                $order->save();
            }
        } catch (Exception $e) {
            $logger = LoggerFactory::create('piggyBank');
            $logger->critical('failed to add PiggyBank marks for order: ' . $e->getMessage());
        }
    }
}
