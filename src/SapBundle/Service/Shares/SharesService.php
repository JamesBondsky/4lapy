<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Service\Shares;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\Connection;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Entity\DeleteResult;
use Bitrix\Main\Error;
use Cocur\Slugify\SlugifyInterface;
use DateTimeImmutable;
use Exception;
use FourPaws\BitrixOrm\Model\Share;
use FourPaws\BitrixOrm\Type\TextContent;
use FourPaws\Enum\UserGroup;
use FourPaws\Helpers\ArrayHelper;
use FourPaws\Helpers\IblockHelper;
use FourPaws\SapBundle\Dto\In\Shares\BonusBuy;
use FourPaws\SapBundle\Dto\In\Shares\BonusBuyFrom;
use FourPaws\SapBundle\Dto\In\Shares\BonusBuyFromItem;
use FourPaws\SapBundle\Dto\In\Shares\BonusBuyShare;
use FourPaws\SapBundle\Dto\In\Shares\BonusBuyTo;
use FourPaws\SapBundle\Exception\BitrixEntityProxyException;
use FourPaws\SapBundle\Exception\CantDeleteShareException;
use FourPaws\SapBundle\Exception\InvalidArgumentException;
use FourPaws\SapBundle\Exception\NotFoundShareException;
use FourPaws\SapBundle\Exception\SapBundleException;
use FourPaws\SapBundle\Model\BasketRule;
use FourPaws\SapBundle\Repository\BasketRulesRepository;
use FourPaws\SapBundle\Repository\ShareRepository;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;

/** @noinspection EfferentObjectCouplingInspection */
/**
 * @todo уменьшить Efferent coupling
 */

/**
 * Class SharesService
 *
 * @package FourPaws\SapBundle\Service\Shares
 */
class SharesService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var ShareRepository
     */
    private $repository;
    /**
     * @var SlugifyInterface
     */
    private $slugify;
    /**
     * @var Connection
     */
    private $connection;
    /**
     * @var BasketRulesRepository
     */
    private $basketRulesRepository;

    /**
     * SharesService constructor.
     *
     * @param ShareRepository $repository
     * @param SlugifyInterface $slugify
     * @param BasketRulesRepository $basketRulesRepository
     */
    public function __construct(
        ShareRepository $repository,
        SlugifyInterface $slugify,
        BasketRulesRepository $basketRulesRepository
    ) {
        $this->connection = Application::getConnection();
        $this->repository = $repository;
        $this->slugify = $slugify;
        $this->basketRulesRepository = $basketRulesRepository;
    }

    /**
     * @param string $groupName
     * @param string $shareName
     *
     * @return string
     */
    public function getGroupHash(string $groupName, string $shareName): string
    {
        return \md5(\sprintf('%s|%s', $groupName, $shareName));
    }

    /**
     * @param BonusBuy $promo
     *
     * @throws RuntimeException
     * @throws SqlQueryException
     */
    public function import(BonusBuy $promo): void
    {
        foreach ($promo->getBonusBuyShare() as $share) {
            /** @noinspection BadExceptionsProcessingInspection */
            try {
                $this->connection->startTransaction();

                if ($share->isDelete()) {
                    $this->tryDeleteShare($share);
                    $this->tryDeleteBasketRule($share);
                    continue;
                }
                /** работа с ПРСК */
                $basketRule = $this->basketRuleFactory($share, $promo);
                if ($existBasketRule = $this->basketRulesRepository->findOneByXmlId($share->getShareNumber())) {
                    $basketRule->setId($existBasketRule->getId());
                    $this->basketRulesRepository->update($basketRule);
                } else {
                    $this->basketRulesRepository->create($basketRule);
                }
                /** работа с ПРСК  конец*/

                $entity = $this->transformDtoToEntity($share, $promo, $basketRule);

                /** @noinspection BadExceptionsProcessingInspection */
                try {
                    $exists = $this->findShare($share);
                    $existsEntityId = $exists->getId();
                    $entity->withId($existsEntityId);
                    $entity->withCode($exists->getCode());

                    $this->tryUpdateShare($entity);

                } /** @noinspection BadExceptionsProcessingInspection */
                catch (NotFoundShareException $e) {
                    $this->tryAddShare($entity);
                }

                $this->connection->commitTransaction();
            } catch (Exception $e) {
                $this->log()->info(
                    \sprintf(
                        'Ошибка %s',
                        $e->getMessage()
                    )
                );
                /**
                 * Глобально исключение - откатываем транзакцию
                 */
                $this->connection->rollbackTransaction();

            }
        }
    }

    /**
     * @param BonusBuyShare $share
     *
     * @return Share
     * @throws NotFoundShareException
     */
    private function findShare(BonusBuyShare $share): Share
    {
        /** @var Share $result */
        $result = $this->repository->findByXmlId($share->getShareNumber());

        if (null === $result) {
            throw new NotFoundShareException(
                \sprintf(
                    'Акция #%s не найдена',
                    $share->getShareNumber()
                )
            );
        }

        return $result;
    }

    /**
     *
     *
     * @param BonusBuyShare $share
     * @param BonusBuy $promo
     *
     * @throws \FourPaws\SapBundle\Exception\RuntimeException
     * @throws \FourPaws\SapBundle\Exception\InvalidArgumentException
     * @throws ArgumentException
     * @throws \Bitrix\Main\SystemException
     *
     * @return BasketRule
     */
    private function basketRuleFactory(BonusBuyShare $share, BonusBuy $promo): BasketRule
    {
        /**
         * @todo не судите строго, ченить сделаю с этой простыней, например вынесу в класс
         */
        /** @noinspection BadExceptionsProcessingInspection */
        try {
            $type = $share->getType();
            $activeFrom = $promo->getStartDate();
            $activeTo = $promo->getEndDate();
            $name = $share->getDescription();
            $xmlId = $share->getShareNumber();
        } /** @noinspection PhpUndefinedClassInspection */
        catch (\TypeError $e) {
            throw new InvalidArgumentException($e->getMessage());
        }

        if ($type === 'Z005') {
            try {
                $countOperator = $promo->isApplyOnce() ? 'once' : 'condition_count';
                $logic = $share->getLogic();
            } /** @noinspection PhpUndefinedClassInspection */
            catch (\TypeError $e) {
                throw new InvalidArgumentException($e->getMessage());
            }

            if ($share->getBonusBuyTo()->count() !== 1 && $share->getBonusBuyFrom()->count() > 0) {
                throw new InvalidArgumentException(
                    'У Z005 должна быть одна группа подарков и как минимум одна группа предпосылок'
                );
            }
            /** @var BonusBuyTo $itemsTo */
            $itemsTo = $share->getBonusBuyTo()->first();
            $productsTo = $itemsTo->getProductIds()->toArray();
            if (empty($productsTo)) {
                throw new InvalidArgumentException('У Z005 должны быть подарки на сайте');
            }
            $giftsCount = $itemsTo->getQuantity();

            $actions = [
                'CLASS_ID' => 'CondGroup',
                'DATA' => [
                    'All' => 'AND',
                ],
                'CHILDREN' => [
                    [
                        'CLASS_ID' => 'ADV:Gift',
                        'DATA' => [
                            'Count_operator' => $countOperator,
                            'count' => $giftsCount,
                            'list' => $productsTo,
                            'All' => $logic,
                        ],
                        'CHILDREN' => [],
                    ],
                ],
            ];
            foreach ($share->getBonusBuyFrom() as $bonusBuyFrom) {
                $countCondition = $bonusBuyFrom->getGroupQuantity();
                $productsFrom = $bonusBuyFrom->getProductIds()->toArray();
                if (empty($productsFrom)) {
                    throw new InvalidArgumentException('У Z005 у каждой группы должны быть товары на сайте');
                }
                $actions['CHILDREN'][0]['CHILDREN'][] = [
                    'CLASS_ID' => 'ADV:BasketFilterQuantityRatio',
                    'DATA' => [
                        'All' => 'AND',
                        'Value' => $countCondition,
                    ],
                    'CHILDREN' => [
                        [
                            'CLASS_ID' => 'CondIBElement',
                            'DATA' => [
                                'logic' => 'Equal',
                                'value' => $productsFrom,
                            ],
                        ],
                    ],
                ];
            }

        } elseif (
            $type === 'Z006'
            &&
            $share->getBonusBuyFrom()->count() === 1
        ) {
            if ($share->getBonusBuyTo()->count() !== 1) {
                throw new InvalidArgumentException(
                    'У Z006 должна быть только одна группа элементов на которые действует скидка'
                );
            }
            try {
                $countOperator = $promo->isApplyOnce() ? 'single' : 'min';
            } /** @noinspection PhpUndefinedClassInspection */
            catch (\TypeError $e) {
                throw new InvalidArgumentException($e->getMessage());
            }
            /**
             * @var BonusBuyTo $itemsTo
             */
            $itemsTo = $share->getBonusBuyTo()->first();
            if (1 > $discountPercent = $itemsTo->getPercent()) {
                throw new InvalidArgumentException('Не передан процент скидки');
            }
            $products = $itemsTo->getProductIds()->toArray();
            /**
             * @var BonusBuyFrom $itemsFrom
             */
            $itemsFrom = $share->getBonusBuyFrom()->first();
            $countCondition = $itemsFrom->getGroupQuantity();
            if (!ArrayHelper::arraysEquals($products, $itemsFrom->getProductIds()->toArray())) {
                throw new InvalidArgumentException(
                    'У акций типа Z006 должны быть одинаковые товары в группах за и на которые дают скидки'
                );
            }
            $filtrationOperator = 'separate';

            $actions = [
                'CLASS_ID' => 'CondGroup',
                'DATA' => [
                    'All' => 'AND'
                ],
                'CHILDREN' => [
                    [
                        'CLASS_ID' => 'ADV:DetachedRowDiscount',
                        'DATA' => [
                            'Type' => 'percent',
                            'Filtration_operator' => $filtrationOperator,
                            'Count_operator' => $countOperator,
                            'All' => 'AND',
                            'Value' => $discountPercent,
                            'Multiplier' => 1,
                        ],
                        'CHILDREN' => [
                            [
                                'CLASS_ID' => 'ADV:BasketFilterQuantityRatio',
                                'DATA' => [
                                    'All' => 'AND',
                                    'Value' => $countCondition,
                                ],
                                'CHILDREN' => [
                                    [
                                        'CLASS_ID' => 'CondIBElement',
                                        'DATA' => [
                                            'logic' => 'Equal',
                                            'value' => $products
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];

        } elseif (
            $type === 'Z006'
            &&
            $share->getBonusBuyFrom()->count() > 1
        ) {
            if ($share->getBonusBuyTo()->count() !== 1) {
                throw new InvalidArgumentException(
                    'У Z006 должна быть только одна группа элементов на которые действует скидка'
                );
            }
            try {
                $logic = $share->getLogic();
                $countOperator = $logic === 'AND' ? 'min' : 'array_sum';
                $countOperator = $promo->isApplyOnce() ? 'single' : $countOperator;
            } /** @noinspection PhpUndefinedClassInspection */
            catch (\TypeError $e) {
                throw new InvalidArgumentException($e->getMessage());
            }
            /**
             * @var BonusBuyTo $itemsTo
             */
            $itemsTo = $share->getBonusBuyTo()->first();
            if (1 > $discountPercent = $itemsTo->getPercent()) {
                throw new InvalidArgumentException('Не передан процент скидки');
            }
            $products = $itemsTo->getProductIds()->toArray();
            if (!$products) {
                throw new InvalidArgumentException('Не переданы скидочные товары');
            }
            $multiplier = $itemsTo->getQuantity();

            $filtrationOperator = 'only_first';

            $actions = [
                'CLASS_ID' => 'CondGroup',
                'DATA' => [
                    'All' => 'AND'
                ],
                'CHILDREN' => [
                    [
                        'CLASS_ID' => 'ADV:DetachedRowDiscount',
                        'DATA' => [
                            'Type' => 'percent',
                            'Filtration_operator' => $filtrationOperator,
                            'Count_operator' => $countOperator,
                            'All' => 'AND',
                            'Value' => $discountPercent,
                            'Multiplier' => $multiplier,
                        ],
                        'CHILDREN' => []
                    ]
                ]
            ];

            /**
             * @var BonusBuyFrom $itemsFrom
             */
            $notFound = true;
            $countCondition = 1;
            $skipConditionOffset = 0;
            foreach ($share->getBonusBuyFrom() as $k => $itemsFrom) {
                if (ArrayHelper::arraysEquals($products, $itemsFrom->getProductIds()->toArray())) {
                    $notFound = false;
                    $countCondition = $itemsFrom->getGroupQuantity();
                    $skipConditionOffset = $k;
                }
            }
            if ($notFound) {
                throw new InvalidArgumentException(
                    'У US-A41 должна быть одна группа предпосылок равная группе товаров со скидкой'
                );
            }

            $actions['CHILDREN'][0]['CHILDREN'][] = [
                'CLASS_ID' => 'ADV:BasketFilterQuantityRatio',
                'DATA' => [
                    'All' => 'AND',
                    'Value' => $countCondition,
                ],
                'CHILDREN' => [
                    [
                        'CLASS_ID' => 'CondIBElement',
                        'DATA' => [
                            'logic' => 'Equal',
                            'value' => $products
                        ]
                    ]
                ]
            ];

            foreach ($share->getBonusBuyFrom() as $k => $itemsFrom) {
                if ($k === $skipConditionOffset) {
                    continue;
                }
                $actions['CHILDREN'][0]['CHILDREN'][] = [
                    'CLASS_ID' => 'ADV:BasketFilterQuantityRatio',
                    'DATA' => [
                        'All' => 'AND',
                        'Value' => $itemsFrom->getGroupQuantity(),
                    ],
                    'CHILDREN' => [
                        [
                            'CLASS_ID' => 'CondIBElement',
                            'DATA' => [
                                'logic' => 'Equal',
                                'value' => $itemsFrom->getProductIds()->toArray()
                            ]
                        ]
                    ]
                ];
            }
        } elseif ($type === 'Z008') {

            try {
                $countOperator = $promo->isApplyOnce() ? 'once' : 'condition_count';
            } /** @noinspection PhpUndefinedClassInspection */
            catch (\TypeError $e) {
                throw new InvalidArgumentException($e->getMessage());
            }
            if ($share->getBonusBuyTo()->count() !== 1 || $share->getBonusBuyFrom()->count() !== 1) {
                throw new InvalidArgumentException('У акций типа Z008 должна быть только одна группа элементов');
            }
            if ($share->getMinPriceSum() < 1) {
                throw new InvalidArgumentException('У акций типа Z008 должна быть установлена цена начала действия');
            }
            /**
             * @var BonusBuyTo $itemsTo
             */
            $itemsTo = $share->getBonusBuyTo()->first();
            $productsTo = $itemsTo->getProductIds()->toArray();
            $giftsCount = $itemsTo->getQuantity();
            /**
             * @var BonusBuyFrom $itemsFrom
             */
            $itemsFrom = $share->getBonusBuyFrom()->first();
            $productsFrom = $itemsFrom->getProductIds()->toArray();

            $actions = [
                'CLASS_ID' => 'CondGroup',
                'DATA' => [
                    'All' => 'AND'
                ],
                'CHILDREN' => [
                    [
                        'CLASS_ID' => 'ADV:Gift',
                        'DATA' => [
                            'Count_operator' => $countOperator,
                            'count' => $giftsCount,
                            'list' => $productsTo,
                            'All' => 'AND',
                        ],
                        'CHILDREN' => [
                            [
                                'CLASS_ID' => 'ADV:BasketFilterBasePriceRatio',
                                'DATA' => [
                                    'All' => 'AND',
                                    'Value' => $share->getMinPriceSum(),
                                ],
                                'CHILDREN' => [
                                    [
                                        'CLASS_ID' => 'CondIBElement',
                                        'DATA' => [
                                            'logic' => 'Equal',
                                            'value' => $productsFrom
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        } elseif (
            $type === 'Z011'
            &&
            /** Набор. Вот так классно мы его определяем, ведь если ввести новый тип, то список уйдет в бесконечность */
            $share->getBonusBuyFrom()->count() > 1
        ) {

            try {
                $countOperator = $promo->isApplyOnce() ? 'single' : 'min';
            } /** @noinspection PhpUndefinedClassInspection */
            catch (\TypeError $e) {
                throw new InvalidArgumentException($e->getMessage());
            }

            if ($share->getBonusBuyTo()->count() !== 1) {
                throw new InvalidArgumentException(
                    'У скидки за набор должна быть одна группа элементов на которые дается скидка'
                );
            }
            /** @var BonusBuyTo $itemsTo */
            $itemsTo = $share->getBonusBuyTo()->first();
            if (1 > $discountPercent = $itemsTo->getPercent()) {
                throw new InvalidArgumentException('Не передан процент скидки');
            }

            $actions = [
                'CLASS_ID' => 'CondGroup',
                'DATA' => [
                    'All' => 'AND',
                ],
                'CHILDREN' => [
                    [
                        'CLASS_ID' => 'ADV:DetachedRowDiscount',
                        'DATA' => [
                            'Type' => 'percent',
                            'Filtration_operator' => 'separate',
                            'Count_operator' => $countOperator,
                            'All' => 'AND',
                            'Value' => $discountPercent,
                            'Multiplier' => 1,
                        ],
                        'CHILDREN' => [],
                    ],
                ],
            ];
            foreach ($share->getBonusBuyFrom() as $bonusBuyFrom) {
                if ($bonusBuyFrom->getGroupQuantity() !== 1) {
                    throw new InvalidArgumentException(
                        'У скидки за набор необходимо добавить один товар из каждой группы'
                    );
                }
                $productsFrom = $bonusBuyFrom->getProductIds()->toArray();
                if (empty($productsFrom)) {
                    throw new InvalidArgumentException('У скидки за набор у каждой группы должны быть товары на сайте');
                }
                $actions['CHILDREN'][0]['CHILDREN'][] = [
                    'CLASS_ID' => 'ADV:BasketFilterQuantityMore',
                    'DATA' => [
                        'All' => 'AND',
                        'Value' => 0.0,
                    ],
                    'CHILDREN' => [
                        [
                            'CLASS_ID' => 'CondIBElement',
                            'DATA' => [
                                'logic' => 'Equal',
                                'value' => $productsFrom,
                            ],
                        ],
                    ],
                ];
            }
        } elseif (
            $type === 'Z011'
            &&
            $share->getBonusBuyFrom()->count() === 1
        ) {
            /** @var BonusBuyTo $itemsTo */
            $itemsTo = $share->getBonusBuyTo()->first();
            if (0.1 < $discountValue = $itemsTo->getAbsolute()) {
                $discountType = 'absolute';
            } elseif (0.1 < $discountValue = $itemsTo->getPercent()) {
                $discountType = 'percent';
            } else {
                throw new InvalidArgumentException('Не передана величина скидки');
            }
            /**
             * @var BonusBuyFrom $itemsFrom
             */
            $itemsFrom = $share->getBonusBuyFrom()->first();
            $countCondition = $itemsFrom->getGroupQuantity();
            $products = $itemsFrom->getProductIds()->toArray();

            $actions = [
                'CLASS_ID' => 'CondGroup',
                'DATA' => [
                    'All' => 'AND',
                ],
                'CHILDREN' => [
                    [
                        'CLASS_ID' => 'ADV:DetachedRowDiscount',
                        'DATA' => [
                            'Type' => $discountType,
                            'Filtration_operator' => 'union',
                            'Count_operator' => 'max',
                            'All' => 'AND',
                            'Value' => $discountValue,
                            'Multiplier' => 1,
                        ],
                        'CHILDREN' => [
                            [
                                'CLASS_ID' => 'ADV:BasketFilterQuantityMore',
                                'DATA' => [
                                    'All' => 'AND',
                                    'Value' => 0.0,
                                ],
                                'CHILDREN' => [
                                    [
                                        'CLASS_ID' => 'CondIBElement',
                                        'DATA' => [
                                            'logic' => 'Equal',
                                            'value' => $products,
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'CLASS_ID' => 'ADV:BasketFilterQuantityMore',
                                'DATA' => [
                                    'All' => 'AND',
                                    'Value' => $countCondition - 1,
                                ],
                                'CHILDREN' => [
                                    [
                                        'CLASS_ID' => 'CondIBElement',
                                        'DATA' => [
                                            'logic' => 'Equal',
                                            'value' => $products,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ];
        } else {
            throw new \FourPaws\SapBundle\Exception\RuntimeException('TODO');
        }

        return (new BasketRule())
            ->setName($name)
            ->setXmlId($xmlId)
            ->setActiveFrom($activeFrom)
            ->setActiveTo($activeTo)
            ->setUserGroups([UserGroup::ALL_USERS])
            ->setActions($actions);
    }

    /**
     * @param BonusBuyShare $share
     * @param BonusBuy $promo
     * @param BasketRule $basketRule
     *
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \FourPaws\SapBundle\Exception\InvalidArgumentException
     *
     * @return Share
     */
    private function transformDtoToEntity(BonusBuyShare $share, BonusBuy $promo, BasketRule $basketRule): Share
    {
        $items = $share->getBonusBuyFrom();
        $products = $items->map(function (BonusBuyFrom $item) {
            return $item->getBonusBuyFromItems()->map(
                function (BonusBuyFromItem $product) {
                    return $product->getOfferId();
                }
            )->toArray();
        })->toArray();

        $products = \array_reduce($products, function ($array, $current) {
            return \array_merge($array ?? [], $current ?? []);
        });

        //Проверяем не скидка ли это за набор и если да, то подгружаем айдишники и записываем в JSON
        $jsonGroupSet = '';
        try {
            $type = $share->getType();
        } /** @noinspection PhpUndefinedClassInspection */
        catch (\TypeError $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
        if (
            $type === 'Z011'
            &&
            $share->getBonusBuyFrom()->count() > 1
        ) {
            $groups = [];
            foreach ($share->getBonusBuyFrom() as $bonusBuyFrom) {
                $groups[] = $bonusBuyFrom->getProductIds()->toArray();
            }
            $groups = array_filter($groups);
            if (!empty($groups)) {
                $jsonGroupSet = json_encode($groups);
            }
        }

        $entity = new Share();

        $entity->withActive('N')
            ->withXmlId($share->getShareNumber())
            ->withDateActiveFrom(DateTimeImmutable::createFromMutable($promo->getStartDate()))
            ->withDateActiveTo(DateTimeImmutable::createFromMutable($promo->getEndDate()))
            ->withDetailText((new TextContent())->withText($share->getDescription()))
            ->withName($share->getDescription())
            ->withPreviewText((new TextContent())->withText($share->getDescription()))
            ->withPropertyLabel($share->getMark())
            ->withPropertyOnlyMp('N')
            ->withPropertyProducts($products)
            ->withPropertyBasketRules([$basketRule->getId()])
            ->withPropertyJsonGroupSet($jsonGroupSet);

        return $entity;
    }


    /**
     *
     *
     * @param BonusBuyShare $share
     *
     * @throws \RuntimeException
     */
    private function tryDeleteShare(BonusBuyShare $share)
    {
        try {
            $result = $this->repository->delete($this->findShare($share));

            if (!$result->isSuccess()) {
                throw new CantDeleteShareException(\implode(', ', $result->getErrorMessages()));
            }

            $this->log()->info(
                \sprintf(
                    'Акция #%s удалена',
                    $share->getShareNumber()
                )
            );
        } catch (CantDeleteShareException | NotFoundShareException $e) {
            $this->log()->error($e->getMessage());
        }
    }

    /**
     * @param Share $share
     *
     * @throws RuntimeException
     */
    private function tryUpdateShare(Share $share): void
    {
        $result = $this->repository->update($share);

        if ($result->isSuccess()) {
            $this->log()->info(
                \sprintf(
                    'Акция #%s обновлена',
                    $share->getId()
                )
            );
        } else {
            $this->log()->error(
                \sprintf(
                    'Ошибка обновления акции #%s: %s',
                    $share->getId(),
                    \implode(', ', $result->getErrorMessages())
                )
            );
        }
    }

    /**
     * @param Share $share
     *
     * @throws RuntimeException
     */
    private function tryAddShare(Share $share)
    {
        $share->withCode(IblockHelper::generateUniqueCode($share->getIblockId(),
            $this->slugify->slugify($share->getName())));

        $result = $this->repository->create($share);

        if ($result->isSuccess()) {
            $this->log()->info(
                \sprintf(
                    'Акция #%s создана',
                    $result->getId()
                )
            );
        } else {
            $this->log()->error(
                \sprintf(
                    'Ошибка создания акции #%s: %s',
                    $share->getXmlId(),
                    \implode(', ', $result->getErrorMessages())
                )
            );
        }
    }


    /**
     *
     *
     * @param BonusBuyShare $share
     *
     * @throws \RuntimeException
     * @throws \Bitrix\Main\SystemException
     */
    private function tryDeleteBasketRule(BonusBuyShare $share)
    {
        try {
            if (!$basketRule = $this->basketRulesRepository->findOneByXmlId($share->getShareNumber())) {
                throw new BitrixEntityProxyException(
                    (new DeleteResult())->addError(new Error('правило корзины не найдено'))
                );
            }

            $this->basketRulesRepository->delete($basketRule);

            $this->log()->info(
                \sprintf(
                    'Правило корзины #%s удалено',
                    $share->getShareNumber()
                )
            );
        } catch (ArgumentException | SapBundleException $e) {
            $this->log()->error($e->getMessage()); // warning ?
        }
    }
}
