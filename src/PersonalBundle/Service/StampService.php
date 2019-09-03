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
use FourPaws\External\ManzanaPosService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
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
     * @throws \FourPaws\External\Manzana\Exception\ExecuteErrorException
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

            if (!$discountCardNumber) {
                return 0;
            }
            try {
                $balanceResponse = $this->manzanaPosService->executeBalanceRequest((new BalanceRequest())->setCardByNumber($discountCardNumber));
            } catch (ExecuteErrorException $e) {
                if ($e->getCode() == 80241) { // Карта не найдена
                    $this->activeStampsCount = 0;
                    return $this->activeStampsCount;
                } else {
                    throw new ExecuteErrorException($e->getMessage(), $e->getCode());
                }
            }

            if (!$balanceResponse->isErrorResponse()) {
                $this->activeStampsCount = $balanceResponse->getCardStatusActiveBalance();

                //TODO save to user profile's field (to update its value asynchronously later)
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
