<?php

namespace FourPaws\CatalogBundle\Service;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use FourPaws\Catalog\Exception\ShareMoreOneFoundException;
use FourPaws\Catalog\Exception\ShareNotFoundException;
use FourPaws\Catalog\Model\Share;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Query\ShareQuery;

class ShareService
{
    /**
     * @param string $shareCode
     *
     * @throws ShareNotFoundException
     * @return Share
     * @throws ShareMoreOneFoundException
     */
    public function getByCode(string $shareCode): Share
    {
        $shareCollection = (new ShareQuery())->withFilterParameter('=CODE', $shareCode)->exec();

        if ($shareCollection->isEmpty()) {
            throw new ShareNotFoundException(
                sprintf('Акция с кодом `%s` не найдена.', $shareCode)
            );
        }

        if ($shareCollection->count() > 1) {
            throw new ShareMoreOneFoundException(
                sprintf('Найдено более одного бренда с кодом `%s`', $shareCode)
            );
        }

        return $shareCollection->current();
    }

    /**
     * @param Share $share
     * @throws IblockNotFoundException
     * @return Category
     */
    public function getShareCategory(Share $share): Category
    {
        return Category::createRoot()
            ->withName(sprintf('Товары бренда %s', $share->getName()));
    }
    
    /**
     * @return int
     */
    public static function getBonusOptPercent(): int
    {
        return self::BONUS_OPT_PERCENT;
    }
}
