<?php


namespace FourPaws\PersonalBundle\Service;


use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Sale\BasketItem;
use Doctrine\Common\Collections\Collection;
use FourPaws\App\Application;
use FourPaws\External\Manzana\Dto\BalanceRequest;
use FourPaws\External\Manzana\Dto\ExtendedAttribute;
use FourPaws\External\Manzana\Exception\ExecuteErrorException;
use FourPaws\External\Manzana\Exception\ExecuteException;
use FourPaws\External\ManzanaPosService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
use Psr\Log\LoggerAwareInterface;

class StampService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    public const MARK_RATE = 400;
    public const MARKS_PER_RATE = 1;

    public const IS_STAMPS_OFFER_ACTIVE = true;

    /*public const EXCHANGE_RULES = [ // dev-манзана
        1000002 => [
            [
                'title' => 'Stamps_02_trade_action_10*5*P',
                'price' => 1097.10,
                'stamps' => 5,
            ],
            [
                'title' => 'Stamps_02_trade_action_20*15*P',
                'price' => 975.20,
                'stamps' => 15,
            ],
            [
                'title' => 'Stamps_02_trade_action_30*20*P',
                'price' => 853.30,
                'stamps' => 20,
            ],
        ],
        1000003 => [
            [
                'title' => 'Stamps_03_trade_action_10*5*P',
                'price' => 1016.10,
                'stamps' => 5,
            ],
            [
                'title' => 'Stamps_03_trade_action_20*15*P',
                'price' => 903.20,
                'stamps' => 15,
            ],
        ],
        1000004 => [
            [
                'title' => 'Stamps_04_trade_action_10*5*P',
                'price' => 2042.10,
                'stamps' => 5,
            ],
        ],
    ];*/

    public const EXCHANGE_RULES = [
        1035430 => [ // 1399 руб
            [
                'title' => 'Stamps_exchange_1035430_30*5*P',
                'price' => 979.30,
                'stamps' => 5,
            ],
            [
                'title' => 'Stamps_exchange_1035430_50*8*P',
                'price' => 699.50,
                'stamps' => 8,
            ],
        ],
        1021198 => [ // 2599 руб
            [
                'title' => 'Stamps_exchange_1021198_30*8*P',
                'price' => 1819.30,
                'stamps' => 8,
            ],
            [
                'title' => 'Stamps_exchange_1021198_50*12*P',
                'price' => 1299.50,
                'stamps' => 12,
            ],
        ],
        1035432 => [ // 1199 руб
            [
                'title' => 'Stamps_exchange_1035432_30*5*P',
                'price' => 839.30,
                'stamps' => 5,
            ],
            [
                'title' => 'Stamps_exchange_1035432_50*8*P',
                'price' => 599.50,
                'stamps' => 8,
            ],
        ],
        1031456 => [ // 2599 руб
            [
                'title' => 'Stamps_exchange_1031456_30*8*P',
                'price' => 1819.30,
                'stamps' => 8,
            ],
            [
                'title' => 'Stamps_exchange_1031456_50*12*P',
                'price' => 1299.50,
                'stamps' => 12,
            ],
        ],
        1021093 => [ // 2999.00 руб
            [
                'title' => 'Stamps_exchange_1021093_30*4*P',
                'price' => 2099.30,
                'stamps' => 4,
            ],
        ],
        1021094 => [ // 2639.00 руб
            [
                'title' => 'Stamps_exchange_1021094_30*4*P',
                'price' => 1847.30,
                'stamps' => 4,
            ],
        ],
        1007171 => [ // 925.00
            [
                'title' => 'Stamps_exchange_1007171_30*4*P',
                'price' => 647.50,
                'stamps' => 4,
            ],
        ],
        1016779 => [ // 1425.00
            [
                'title' => 'Stamps_exchange_1016779_30*4*P',
                'price' => 997.50,
                'stamps' => 4,
            ],
        ],
        1016780 => [ // 1647.00
            [
                'title' => 'Stamps_exchange_1016780_30*4*P',
                'price' => 1152.90,
                'stamps' => 4,
            ],
        ],
        1016781 => [ // 629.00
            [
                'title' => 'Stamps_exchange_1016781_30*4*P',
                'price' => 440.30,
                'stamps' => 4,
            ],
        ],
        1018875 => [ // 959.00
            [
                'title' => 'Stamps_exchange_1018875_30*4*P',
                'price' => 671.30,
                'stamps' => 4,
            ],
        ],
        1032297 => [ // 3499.00
            [
                'title' => 'Stamps_exchange_1032297_30*4*P',
                'price' => 2449.30,
                'stamps' => 4,
            ],
        ],
        1003335 => [ // 1499.00
            [
                'title' => 'Stamps_exchange_1003335_30*4*P',
                'price' => 1049.30,
                'stamps' => 4,
            ],
        ],
        1005888 => [ // 1629.00
            [
                'title' => 'Stamps_exchange_1005888_30*4*P',
                'price' => 1140.30,
                'stamps' => 4,
            ],
        ],
        1018157 => [ // 2229.00
            [
                'title' => 'Stamps_exchange_1018157_30*4*P',
                'price' => 1560.30,
                'stamps' => 4,
            ],
        ],
        1021946 => [ // 1429.00
            [
                'title' => 'Stamps_exchange_1021946_30*4*P',
                'price' => 1000.30,
                'stamps' => 4,
            ],
        ],
        1024525 => [ // 2165.00
            [
                'title' => 'Stamps_exchange_1024525_30*4*P',
                'price' => 1515.50,
                'stamps' => 4,
            ],
        ],
        1024682 => [ // 1699.00
            [
                'title' => 'Stamps_exchange_1024682_30*4*P',
                'price' => 1189.30,
                'stamps' => 4,
            ],
        ],
        1024683 => [ // 1285.00
            [
                'title' => 'Stamps_exchange_1024683_30*4*P',
                'price' => 899.50,
                'stamps' => 4,
            ],
        ],
        1024685 => [ // 969.00
            [
                'title' => 'Stamps_exchange_1024685_30*4*P',
                'price' => 678.30,
                'stamps' => 4,
            ],
        ],
        1029590 => [ // 1359.00
            [
                'title' => 'Stamps_exchange_1029590_30*4*P',
                'price' => 951.30,
                'stamps' => 4,
            ],
        ],
        1009253 => [ // 1485.00
            [
                'title' => 'Stamps_exchange_1009253_30*4*P',
                'price' => 1039.50,
                'stamps' => 4,
            ],
        ],
        1026366 => [ // 875.00
            [
                'title' => 'Stamps_exchange_1026366_30*4*P',
                'price' => 612.50,
                'stamps' => 4,
            ],
        ],
        1026368 => [ // 1155.00
            [
                'title' => 'Stamps_exchange_1026368_30*4*P',
                'price' => 808.50,
                'stamps' => 4,
            ],
        ],
        1026370 => [ // 1545.00
            [
                'title' => 'Stamps_exchange_1026370_30*4*P',
                'price' => 1081.50,
                'stamps' => 4,
            ],
        ],
        1026899 => [ // 1249.00
            [
                'title' => 'Stamps_exchange_1026899_30*4*P',
                'price' => 874.30,
                'stamps' => 4,
            ],
        ],
        1024781 => [ // 1999.00
            [
                'title' => 'Stamps_exchange_1024781_30*4*P',
                'price' => 1399.30,
                'stamps' => 4,
            ],
        ],
        1013633 => [ // 2099.00
            [
                'title' => 'Stamps_exchange_1013633_30*4*P',
                'price' => 1469.30,
                'stamps' => 4,
            ],
        ],
        1021195 => [ // 2199.00
            [
                'title' => 'Stamps_exchange_1021195_30*4*P',
                'price' => 1539.30,
                'stamps' => 4,
            ],
        ],
        1021196 => [ // 1199.00
            [
                'title' => 'Stamps_exchange_1021196_30*4*P',
                'price' => 839.30,
                'stamps' => 4,
            ],
        ],
        1021197 => [ // 899.00
            [
                'title' => 'Stamps_exchange_1021197_30*4*P',
                'price' => 629.30,
                'stamps' => 4,
            ],
        ],
        1021199 => [ // 1599.00
            [
                'title' => 'Stamps_exchange_1021199_30*4*P',
                'price' => 1119.30,
                'stamps' => 4,
            ],
        ],
        1021200 => [ // 1459.00
            [
                'title' => 'Stamps_exchange_1021200_30*4*P',
                'price' => 1021.30,
                'stamps' => 4,
            ],
        ],
        1022335 => [ // 1629.00
            [
                'title' => 'Stamps_exchange_1022335_30*4*P',
                'price' => 1140.30,
                'stamps' => 4,
            ],
        ],
        1022336 => [ // 1239.00
            [
                'title' => 'Stamps_exchange_1022336_30*4*P',
                'price' => 867.30,
                'stamps' => 4,
            ],
        ],
        1022510 => [ // 2079.00
            [
                'title' => 'Stamps_exchange_1022510_30*4*P',
                'price' => 1455.30,
                'stamps' => 4,
            ],
        ],
        1024483 => [ // 5929.00
            [
                'title' => 'Stamps_exchange_1024483_30*4*P',
                'price' => 4150.30,
                'stamps' => 4,
            ],
        ],
        1028883 => [ // 829.00
            [
                'title' => 'Stamps_exchange_1028883_30*4*P',
                'price' => 580.30,
                'stamps' => 4,
            ],
        ],
        1028908 => [ // 769.00
            [
                'title' => 'Stamps_exchange_1028908_30*4*P',
                'price' => 538.30,
                'stamps' => 4,
            ],
        ],
        1035431 => [ // 1999.00
            [
                'title' => 'Stamps_exchange_1035431_30*4*P',
                'price' => 1399.30,
                'stamps' => 4,
            ],
        ],
    ];


    /**
     * @var UserService
     */
    protected $currentUserProvider;
    /**
     * @var ManzanaPosService
     */
    protected $manzanaPosService;
    /**
     * @var int
     */
    protected $activeStampsCount;

    public function __construct()
    {
        $container = Application::getInstance()->getContainer();
        $this->currentUserProvider = $container->get(CurrentUserProviderInterface::class);
        $this->manzanaPosService = Application::getInstance()->getContainer()->get('manzana.pos.service');
    }


    /**
     * @param bool|null $withoutCache
     * @return int
     * @throws \FourPaws\External\Manzana\Exception\ExecuteException
     * @throws NotAuthorizedException
     */
    public function getActiveStampsCount(?bool $withoutCache = false): int
    {
        if (!$this->currentUserProvider->isAuthorized()) {
            $this->activeStampsCount = 0;
            return $this->activeStampsCount;
        }

        if (!$this->activeStampsCount || $withoutCache) {
            $discountCardNumber = $this->currentUserProvider->getCurrentUser()->getDiscountCardNumber();

            $user = $this->currentUserProvider->getCurrentUser();
            if (!$discountCardNumber) {
                $this->activeStampsCount = 0;
                $user->setActiveStamps($this->activeStampsCount);
                Application::getInstance()->getContainer()->get(UserRepository::class)->update($user);
                return $this->activeStampsCount;
            }
            try {
                $balanceResponse = $this->manzanaPosService->executeBalanceRequest((new BalanceRequest())->setCardByNumber($discountCardNumber));
            } catch (ExecuteErrorException $e) {
                if ($e->getCode() == 80241) { // Карта не найдена
                    $this->activeStampsCount = 0;
                    $user->setActiveStamps($this->activeStampsCount);
                    Application::getInstance()->getContainer()->get(UserRepository::class)->update($user);
                    return $this->activeStampsCount;
                } else {
                    throw new ExecuteErrorException($e->getMessage(), $e->getCode());
                }
            } catch (ExecuteException $e) {
                $this->log()->error(__METHOD__ . ': executeBalanceRequest exception: '. $e->getMessage());
                $this->activeStampsCount = $user->getActiveStamps();
                return $this->activeStampsCount;
            }

            if (!$balanceResponse->isErrorResponse()) {
                $this->activeStampsCount = $balanceResponse->getCardStatusActiveBalance();

                $user->setActiveStamps($this->activeStampsCount);
                Application::getInstance()->getContainer()->get(UserRepository::class)->update($user);
            } else {
                $this->log()->error(__METHOD__ . '. Не удалось получить balanceResponse по карте ' . $discountCardNumber . '. Ошибка: ' . $balanceResponse->getMessage());
                $this->activeStampsCount = 0;
            }
        }

        // для отладки марок
        //$this->activeStampsCount = 27;
        return $this->activeStampsCount;
    }

    /**
     * @param Collection|ExtendedAttribute[] $extendedAttributeCollection
     * @param int|null $availableStampsCount
     * @return array
     */
    public function getMaxAvailableLevel($extendedAttributeCollection, ?int $availableStampsCount = 0): array
    {
        // Реализация согласована. Определение, какой уровень наилучший, с помощью того, на какой уровень нужно больше марок
        $maxLevel = [];

        /** @var ExtendedAttribute $extendedAttribute */
        $maxDiscountSize = 0;
        foreach ($extendedAttributeCollection as $extendedAttribute) {
            $discount = $this->parseLevelKey($extendedAttribute->getKey());
            if ($discountStampsNeeded = (int)$discount['discountStamps']) {
                $quantity = $extendedAttribute->getValue();
                $discountSize = $discountStampsNeeded
                    * $quantity; // Количество товара, на которое доступна эта скидка

                // если марок не хватает на всё количество единиц, на которое готова списать Manzana (возможно в кейсе, когда в корзине уже выбран обмен марок и по другим товарам тоже),
                // то уменьшаем количество штук в расчете самого выгодного обмена
                if ($availableStampsCount < $discountSize && $quantity > 1) {
                    do {
                        --$quantity;
                        $discountSize = $discountStampsNeeded
                            * $quantity; // Количество товара, на которое доступна эта скидка
                    } while ($availableStampsCount < $discountSize && $quantity > 0);
                }

                if ($discountSize > $maxDiscountSize && $availableStampsCount >= $discountSize) {
                    $maxLevel = [
                        'key' => $extendedAttribute->getKey(),
                        'value' => $quantity,
                    ];
                    $maxDiscountSize = $discountSize;
                }
            }
        }

        return $maxLevel;
    }

    /**
     * Получение параметров уровня обмена марок из его "номера РА" (ключа)
     * @param string $key
     * @return array
     */
    public function parseLevelKey(string $key): array
    {
        $keyArray = [];
        preg_match('/(\d+)\*(\d+)\*([VP])$/', $key, $discount);

        if ($discount[1] && $discount[2] && $discount[3]) {
            $keyArray = [
                'discountValue' => $discount[1],
                'discountStamps' => $discount[2],
                'discountType' => $discount[3],
            ];
        }

        return $keyArray;
    }

    /**
     * @param BasketItem $basketItem
     * @param $offerXmlId
     * @return array
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\NotImplementedException
     */
    public function getBasketItemStampsInfo($basketItem, $offerXmlId)
    {
        $hasStamps = isset(self::EXCHANGE_RULES[$offerXmlId]); // todo get from manzana

        $stampLevels = [];
        $maxStampsLevelValue = 0;

        $useStamps = false;
        $useStampsAmount = 0;

        if ($hasStamps) {
            if (isset($basketItem->getPropertyCollection()->getPropertyValues()['USE_STAMPS'])) {
                $useStamps = (bool)$basketItem->getPropertyCollection()->getPropertyValues()['USE_STAMPS']['VALUE'];
            }

            if ($useStamps) {
                if (isset($basketItem->getPropertyCollection()->getPropertyValues()['USED_STAMPS_LEVEL'])) {
                    $useStampsAmount = unserialize($basketItem->getPropertyCollection()->getPropertyValues()['USED_STAMPS_LEVEL']['VALUE'])['stampsUsed'];
                }
            } else {
                if (isset($basketItem->getPropertyCollection()->getPropertyValues()['MAX_STAMPS_LEVEL'])) {
                    $maxStampsLevelKey = unserialize($basketItem->getPropertyCollection()->getPropertyValues()['MAX_STAMPS_LEVEL']['VALUE'])['key'];
                    if ($maxStampsLevelKey) {
                        $maxStampsLevelValue = $this->parseLevelKey($maxStampsLevelKey)['discountStamps'];
                    }
                }

                // для отладки марок
                //dump($offerXmlId . ' - ' . $maxStampsLevelValue);
                $stampLevels = $this->getBasketItemStampLevels($basketItem, $offerXmlId, $maxStampsLevelValue);
            }
        }

        return [
            'HAS_STAMPS' => $hasStamps,
            'STAMP_LEVELS' => $stampLevels,
            'CAN_USE_STAMPS' => (!$useStamps && $maxStampsLevelValue),
            'USE_STAMPS' => $useStamps,
            'USED_STAMP_AMOUNT' => $useStampsAmount,
        ];
    }

    /**
     * @param BasketItem $basketItem
     * @param $offerXmlId
     * @param $maxStampsLevelValue
     * @return array
     * @throws ArgumentNullException
     */
    public function getBasketItemStampLevels($basketItem, $offerXmlId, $maxStampsLevelValue)
    {
        $stampLevels = [];

        foreach (self::EXCHANGE_RULES[$offerXmlId] as $stampLevel) {
            $stampLevelInfo = $this->parseLevelKey($stampLevel['title']);
            if (is_array($stampLevelInfo) && ($stampLevelInfo['discountStamps'] >= $maxStampsLevelValue)) {
                $discountPrice = $this->getBasketItemDiscountPrice($basketItem, $stampLevelInfo);

                if ($discountPrice === null) {
                    continue;
                }

                $stampLevels[] = [
                    'price' => $discountPrice,
                    'stamps' => $stampLevelInfo['discountStamps'],
                ];
            }
        }

        return $stampLevels;
    }

    /**
     * @param BasketItem $basketItem
     * @param $stampLevelInfo
     * @return float
     * @throws ArgumentNullException
     */
    public function getBasketItemDiscountPrice($basketItem, $stampLevelInfo) : float
    {
        $discountPrice = null;

        $basketItemPrice = $basketItem->getBasePrice();

        if ($stampLevelInfo['discountType'] == 'V') {
            $discountPrice = $basketItemPrice - $stampLevelInfo['discountValue'];
        } else if ($stampLevelInfo['discountType'] == 'P') {
            $discountPrice = $basketItemPrice * (1 - ($stampLevelInfo['discountValue'] / 100));
        }

        return $discountPrice;
    }
}
