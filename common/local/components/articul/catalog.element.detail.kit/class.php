<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Doctrine\Common\Collections\ArrayCollection;
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
            $params['CACHE_TIME'] = 86400;
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
        if ($this->startResultCache($this->arParams['CACHE_TIME'], [$this->arParams['PRODUCT'], $this->arParams['OFFER']])) {
            $hideKitBlock = false;
            $product = $this->getProduct($this->arParams['CODE']);
            $catalogElementDetailClass = new CatalogElementDetailComponent();
            $offer = $catalogElementDetailClass->getCurrentOffer($product, $this->arParams['OFFER_ID']);

            $selectionOffers = new ArrayCollection();
            $pedestal = null;
            if (($product->getSection()->getCode() == 'banki-bez-kryshki-akvariumy' || $product->getSection()->getCode() == 'detskie-akvariumy-akvariumy' || $product->getSection()->getCode() == 'komplekty-akvariumy' || $product->getSection()->getCode() == 'tumby-podstavki-akvariumy') && $product->getAquariumCombination() != '') {
                $isAquarium = $product->getSection()->getCode() != 'tumby-podstavki-akvariumy';
                if($isAquarium){
                    $pedestal = $product->getPedestal($product->getAquariumCombination());
                } else {
                    $pedestal = $product->getAquarium($product->getAquariumCombination());
                }
                if (!empty($pedestal)) {
                    if($isAquarium){
                        $volumeStr = strtolower($offer->getVolumeReference()->getName());
                    } else {
                        $volumeStr = strtolower($pedestal->getVolumeReference()->getName());
                    }
                    if (mb_strpos($volumeStr, 'л')) {
                        $volume = intval(str_replace(',', '.', preg_replace("/[^0-9]/", '', $volumeStr)));
                        if ($volume < 250) {
                            $selectionOffers['filters'] = $product->getInternalFilters($volume);
                        } else {
                            $selectionOffers['filters'] = $product->getExternalFilters($volume);
                        }
                        $selectionOffers['lamps'] = $product->getLamps();
                        $selectionOffers['decor'] = $product->getDecor();
                    } else {
                        $hideKitBlock = true;
                    }
                } else {
                    $hideKitBlock = true;
                }
            } else {
                $hideKitBlock = true;
            }

            $this->arResult = [
                'HIDE_KIT_BLOCK' => $hideKitBlock,
                'PRODUCT' => $product,
                'OFFER' => $offer,
                'PEDESTAL' => $pedestal,
                'SELECTION_OFFERS' => $selectionOffers
            ];


            $this->setResultCacheKeys([
                'HIDE_BLOCK',
                'PRODUCT',
                'OFFER',
                'PEDESTAL',
                'OFFERS',
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
