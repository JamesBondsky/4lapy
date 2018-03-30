<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Service\Shares;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Application;
use Bitrix\Main\DB\Connection;
use Bitrix\Main\DB\SqlQueryException;
use Cocur\Slugify\SlugifyInterface;
use DateTimeImmutable;
use Exception;
use FourPaws\BitrixOrm\Model\Share;
use FourPaws\BitrixOrm\Type\TextContent;
use FourPaws\Helpers\IblockHelper;
use FourPaws\SapBundle\Dto\In\Shares\BonusBuy;
use FourPaws\SapBundle\Dto\In\Shares\BonusBuyFrom;
use FourPaws\SapBundle\Dto\In\Shares\BonusBuyFromItem;
use FourPaws\SapBundle\Dto\In\Shares\BonusBuyShare;
use FourPaws\SapBundle\Exception\CantDeleteShareException;
use FourPaws\SapBundle\Exception\NotFoundShareException;
use FourPaws\SapBundle\Repository\ShareRepository;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;

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
     * SharesService constructor.
     *
     * @param ShareRepository $repository
     * @param SlugifyInterface $slugify
     */
    public function __construct(ShareRepository $repository, SlugifyInterface $slugify)
    {
        $this->connection = Application::getConnection();
        $this->repository = $repository;
        $this->slugify = $slugify;
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
            try {
                $this->connection->startTransaction();

                if ($this->tryDeleteShare($share)) {
                    return;
                }

                $entity = $this->transformDtoToEntity($share, $promo);

                try {
                    $exists = $this->findShare($share);
                    $existsEntityId = $exists->getId();
                    $entity->withId($existsEntityId);
                    $entity->withCode($exists->getCode());

                    $this->tryUpdateShare($entity);
                } catch (NotFoundShareException $e) {
                    $this->tryAddShare($entity);
                }

                $this->connection->commitTransaction();
            } catch (Exception $e) {
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
     * @param BonusBuyShare $share
     * @param BonusBuy $promo
     *
     * @return Share
     */
    private function transformDtoToEntity(BonusBuyShare $share, BonusBuy $promo): Share
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
            ->withPropertyProducts($products);

        return $entity;
    }

    /**
     * @param BonusBuyShare $share
     *
     * @return bool
     * @throws RuntimeException
     */
    private function tryDeleteShare(BonusBuyShare $share): bool
    {
        if ($share->isDelete()) {
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

            return true;
        }

        return false;
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
        $share->withCode(IblockHelper::generateUniqueCode($share->getIblockId(), $this->slugify->slugify($share->getName())));

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
}
