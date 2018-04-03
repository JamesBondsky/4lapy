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
            $countOperator = $promo->isApplyOnce() ? 'single' : 'min';
        } /** @noinspection PhpUndefinedClassInspection */
        catch (\TypeError $e) {
            throw new InvalidArgumentException($e->getMessage());
        }

        if ($type === 'Z006') {
            if ($share->getBonusBuyTo()->count() !== 1 || $share->getBonusBuyFrom()->count() !== 1) {
                throw new InvalidArgumentException('У акций типа Z006 должна быть только одна группа элементов');
            }
            /**
             * @var BonusBuyTo $itemsTo
             */
            $itemsTo = $share->getBonusBuyTo()->first();
            $discountPercent = $itemsTo->getPercent();
            $products = $itemsTo->getProductIds();
            /**
             * @var BonusBuyFrom $itemsFrom
             */
            $itemsFrom = $share->getBonusBuyFrom()->first();
            $countCondition = $itemsFrom->getGroupQuantity() - 1;
            if (!ArrayHelper::arraysEquals($products, $itemsFrom->getProductIds())) {
                throw new InvalidArgumentException('У акций типа Z006 должны быть одинаковые товары в группах за и на которые дают скидки');
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
                            'Filtration_operator' => $filtrationOperator,
                            'Count_operator' => $countOperator,
                            'Value' => $discountPercent,
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
            ->withPropertyBasketRules([$basketRule->getId()]);

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
