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

    public const DISCOUNT_LEVELS = [ //TODO del
        1 => [
            'LEVEL' => 1,
            'MARKS_NEEDED' => 7,
            'DISCOUNT' => 10,
        ],
        2 => [
            'LEVEL' => 2,
            'MARKS_NEEDED' => 15,
            'DISCOUNT' => 20,
        ],
        3 => [
            'LEVEL' => 3,
            'MARKS_NEEDED' => 25,
            'DISCOUNT' => 30,
        ],
    ];

    public const EXCHANGE_RULES = [ //TODO FIX xml_ids array
        1000002 => [
            [
                'title' => 'Stamps_02_trade_action_10*5*P', //FIXME title пока еще не используется. Нужен?
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
    public function getActiveStampsCount(?bool $withoutCache = false): int //TODO answer with this value in new API method
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
        //TODO переделать способ определения максимальной скидки, учитывая, хватит ли пользователю марок на применение этого уровня)
        $maxLevel = [];

        /** @var ExtendedAttribute $extendedAttribute */
        $maxDiscountSize = 0;
        foreach ($extendedAttributeCollection as $extendedAttribute) {
            $discount = $this->parseLevelKey($extendedAttribute->getKey());
            if ($discountStampsNeeded = $discount['discountStamps']) {
                $discountSize = $discountStampsNeeded
                    * $extendedAttribute->getValue(); // Количество товара, на которое доступна эта скидка

                if ($discountSize > $maxDiscountSize && $availableStampsCount >= $discountSize) {
                    $maxLevel = [
                        'key' => $extendedAttribute->getKey(),
                        'value' => $extendedAttribute->getValue(),
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
     * @param null $activeStampsCount
     * @return array
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\NotImplementedException
     */
    public function getBasketItemStampsInfo($basketItem, $offerXmlId, $activeStampsCount = null)
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

                foreach (self::EXCHANGE_RULES[$offerXmlId] as $stampLevel) {
                    $stampLevelInfo = $this->parseLevelKey($stampLevel['title']);
                    if (is_array($stampLevelInfo) && ($stampLevelInfo['discountStamps'] >= $maxStampsLevelValue)) {
                        $discountPrice = $this->getBasketItemDiscountPrice($basketItem, $stampLevelInfo);

                        if ($discountPrice === null) {
                            continue;
                        }

                        $stampLevelArr = [
                            'price' => $discountPrice,
                            'stamps' => $stampLevelInfo['discountStamps'],
                        ];

                        $stampLevels[] = $stampLevelArr;
                    }
                }
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
     * @param $stampLevelInfo
     * @return int
     * @throws ArgumentNullException
     */
    public function getBasketItemDiscountPrice($basketItem, $stampLevelInfo) : int
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
