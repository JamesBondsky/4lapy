<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application;
use FourPaws\BitrixOrm\Model\Exceptions\CatalogProductNotFoundException;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\Components\CatalogElementDetailComponent;
use FourPaws\LocationBundle\LocationService;

class CatalogElementDetailKitComponent extends \CBitrixComponent
{
    /**
     * @var bool $hideKitBlock
     */
    private $hideKitBlock;
    /**
     * @var Category $productSection
     */
    private $productSection;
    /**
     * @var Product $product
     */
    private $product;
    /**
     * @var Offer $offer
     */
    private $offer;
    /**
     * @var Offer $offer
     */
    private $additionalItem;
    /**
     * @var ArrayCollection $selectionOffers
     */
    private $selectionOffers;

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
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function executeComponent()
    {
        /** @var LocationService $locationService */
        $locationService = Application::getInstance()->getContainer()->get('location.service');
        if ($this->startResultCache($this->arParams['CACHE_TIME'], [$this->arParams['PRODUCT'], $this->arParams['OFFER'], $locationService->getCurrentLocation()])) {
            $this->hideKitBlock = false;
            $this->product = $this->getProduct($this->arParams['CODE']);
            $catalogElementDetailClass = new CatalogElementDetailComponent();
            $this->offer = $catalogElementDetailClass->getCurrentOffer($this->product, $this->arParams['OFFER_ID']);

            $this->selectionOffers = new ArrayCollection();
            $this->additionalItem = null;
            $this->productSection = $this->product->getSection();
            if (
                $this->productSection !== null &&
                $this->product->getAquariumCombination() != '' &&
                (
                    $this->productSection->getCode() == 'banki-bez-kryshki-akvariumy' ||
                    $this->productSection->getCode() == 'detskie-akvariumy-akvariumy' ||
                    $this->productSection->getCode() == 'komplekty-akvariumy' ||
                    $this->product->getSection()->getCode() == 'tumby-podstavki-akvariumy'
                )
            ) {
                $isAquarium = $this->product->getSection()->getCode() != 'tumby-podstavki-akvariumy';
                if ($isAquarium) {
                    $this->additionalItem = $this->product->getPedestal($this->product->getAquariumCombination());
                } else {
                    $this->additionalItem = $this->product->getAquarium($this->product->getAquariumCombination());
                }
                if (!empty($this->additionalItem)) {
                    $volume = $this->getVolume($isAquarium);
                    if (!$this->hideKitBlock) {
                        $this->getSectionOffers($volume);
                    }
                } else {
                    $this->hideKitBlock = true;
                }
            } elseif ($this->productSection !== null && $this->productSection->getCode() == 'komplekty-akvariumy') {
                $volume = $this->getVolume(true);
                if (!$this->hideKitBlock) {
                    $this->getSectionOffers($volume);
                }
            } else {
                $this->hideKitBlock = true;
            }

            $this->arResult = [
                'HIDE_KIT_BLOCK' => $this->hideKitBlock,
                'PRODUCT' => $this->product,
                'OFFER' => $this->offer,
                'ADDITIONAL_ITEM' => $this->additionalItem,
                'SELECTION_OFFERS' => $this->selectionOffers
            ];

            $this->setResultCacheKeys([
                'HIDE_BLOCK',
                'PRODUCT',
                'OFFER',
                'ADDITIONAL_ITEM',
                'OFFERS',
            ]);

            $this->includeComponentTemplate();
        }
    }

    /**
     * @param bool $isAquarium
     * @return int|null
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    protected function getVolume(bool $isAquarium)
    {
        $volume = null;
        if ($isAquarium) {
            $volumeStr = strtolower($this->offer->getVolumeReference()->getName());
        } else {
            $volumeStr = strtolower($this->additionalItem->getVolumeReference()->getName());
        }
        if (mb_strpos($volumeStr, 'л')) {
            $volume = intval(str_replace(',', '.', preg_replace("/[^0-9]/", '', $volumeStr)));
        } else {
            $this->hideKitBlock = true;
        }
        return $volume;
    }

    /**
     * @throws \Bitrix\Main\ArgumentException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    protected function getLamps()
    {
        $this->selectionOffers['lamps'] = $this->product->getLamps();
    }

    /**
     * @throws \Bitrix\Main\ArgumentException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    protected function getDecor()
    {
        $this->selectionOffers['decor'] = $this->product->getDecor();
    }

    /**
     * @param int $volume
     * @return void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    protected function getFilters(int $volume): void
    {
        if ($volume < 250) {
            $this->selectionOffers['filters'] = $this->product->getInternalFilters($volume);
        } else {
            $this->selectionOffers['filters'] = $this->product->getExternalFilters($volume);
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

    /**
     * @param $volume
     * @throws \Bitrix\Main\ArgumentException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    protected function getSectionOffers($volume)
    {
        $this->getFilters($volume);
        if ($this->selectionOffers['filters']->count() > 0) {
            $this->getLamps();
            if ($this->selectionOffers['lamps']->count() > 0) {
                $this->getDecor();
                if ($this->selectionOffers['decor']->count() == 0) {
                    $this->hideKitBlock = true;
                }
            } else {
                $this->hideKitBlock = true;
            }
        } else {
            $this->hideKitBlock = true;
        }
    }
}
