<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 12.03.2019
 * Time: 12:27
 */

namespace FourPaws\CatalogBundle\Service;


use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Collection\PriceCollection;
use FourPaws\Catalog\Exception\PriceNotFoundException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Price;
use FourPaws\Catalog\Query\PriceQuery;
use FourPaws\CatalogBundle\Exception\ArgumentException;
use FourPaws\LocationBundle\LocationService;
use FourPaws\App\Application as App;
use Psr\Log\LoggerAwareInterface;

class PriceService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @param int $offerId
     * @param string $regionCode
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getProductPriceByRegion(int $offerId, string $regionCode = ''): Price
    {
        if (!$regionCode) {
            /** @var LocationService $locationService */
            $locationService = App::getInstance()->getContainer()->get('location.service');
            $regionCode = $locationService->getCurrentRegionCode();
        }

        try {
            /** @var CatalogGroupService $catalogGroupService */
            $catalogGroupService = App::getInstance()->getContainer()->get('catalog_group.service');
            $catalogGroupId = $catalogGroupService->getCatalogGroupIdByRegion($regionCode);

            /** @var PriceCollection $prices */
            $prices = (new PriceQuery())
                ->withSelect(['ID'])
                ->withFilter([
                    '=PRODUCT_ID' => $offerId,
                    '=CATALOG_GROUP_ID' => [$catalogGroupId, Offer::CATALOG_GROUP_ID_BASE]
                ])->exec();

            /** @var Price $price */
            $price = $prices->filterByCatalogGroupId($catalogGroupId)->isEmpty()
                ? $prices->filterByCatalogGroupId(Offer::CATALOG_GROUP_ID_BASE)->first()
                : $prices->filterByCatalogGroupId($catalogGroupId)->first();

            if (!$price) {
                throw new PriceNotFoundException(sprintf("Не найдены цены для товара %s региона %s", $offerId, $regionCode));
            }

            return $price;
        } catch (PriceNotFoundException $e) {
            $this->log()->error($e->getMessage());
            return new Price;
        }
    }
}