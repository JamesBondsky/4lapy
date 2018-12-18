<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\BitrixOrm\Model\Exceptions\CatalogProductNotFoundException;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\Components\CatalogElementDetailComponent;

class CatalogElementDetailKitComponent extends \CBitrixComponent
{
    /**
     * @param $params
     *
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
        if (!isset($params['CACHE_TIME'])) {
            $params['CACHE_TIME'] = 36000000;
        }

        return parent::onPrepareComponentParams($params);
    }

    /**
     * @return mixed
     *
     * @throws CatalogProductNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function executeComponent()
    {
        if ($this->startResultCache($this->arParams['CACHE_TIME'], $this->arParams['PRODUCT'] && $this->arParams['OFFER'])) {
            $hideBlock = false;
            $product = $this->getProduct($this->arParams['CODE']);
            $catalogElementDetailClass = new CatalogElementDetailComponent();
            $offer = $catalogElementDetailClass->getCurrentOffer($product, $this->arParams['OFFER_ID']);
            $pedestal = null;
            $externalFilters = null;
            $internalFilters = null;
            $lamps = null;
            if (($product->getSection()->getCode() == 'banki-bez-kryshki-akvariumy' || $product->getSection()->getCode() == 'detskie-akvariumy-akvariumy') && $product->getAquariumCombination() != '') {
                $pedestal = $product->getPedestal($product->getAquariumCombination());
                if (!empty($pedestal)) {
                    $volumeStr = strtolower($offer->getVolumeReference()->getName());
                    if (mb_strpos($volumeStr, 'л')) {
                        $volume = intval(str_replace(',', '.', preg_replace("/[^0-9]/", '', $volumeStr)));
                        $internalFilters = $product->getInternalFilters($volume);
                        $externalFilters = $product->getExternalFilters($volume);
                        $lamps = $product->getLamps();
                    } else {
                        $hideBlock = true;
                    }
                } else {
                    $hideBlock = true;
                }
            } else {
                $hideBlock = true;
            }

            $this->arResult = [
                'HIDE_BLOCK' => $hideBlock,
                'PRODUCT' => $product,
                'OFFER' => $offer,
                'PEDESTAL' => $pedestal,
                'EXTERNAL_FILTERS' => $externalFilters,
                'INTERNAL_FILTERS' => $internalFilters,
                'LAMPS' => $lamps
            ];


            $this->setResultCacheKeys([
                'HIDE_BLOCK',
                'PRODUCT',
                'OFFER',
                'PEDESTAL',
                'OFFERS',
                'EXTERNAL_FILTERS',
                'INTERNAL_FILTERS',
                'LAMPS'
            ]);

            $this->includeComponentTemplate();
        }
    }

    /**
     * @param string $code
     * @return Product
     * @throws CatalogProductNotFoundException
     */
    protected function getProduct(string $code): Product
    {
        $res = (new ProductQuery())
            ->withFilterParameter('CODE', $code)
            ->exec();
        if ($res->count() === 0) {
            throw new CatalogProductNotFoundException('Товар по коду не найден');
        }

        return $res->first();
    }
}
