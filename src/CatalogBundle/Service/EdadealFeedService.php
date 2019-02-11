<?php

namespace FourPaws\CatalogBundle\Service;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\CatalogBundle\Translate\Configuration;
use FourPaws\CatalogBundle\Translate\ConfigurationInterface;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Helpers\WordHelper;
use FourPaws\StoreBundle\Service\StoreService;
use Psr\Log\LoggerAwareInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Filesystem\Filesystem;
use FourPaws\App\Application;
use FourPaws\App\Application as App;

/**
 * Class EdadealFeedService
 *
 * @package FourPaws\CatalogBundle\Service
 */
class EdadealFeedService extends FeedService implements LoggerAwareInterface
{
    /** @var array $arResult */
    private $arResult;
    /** @var int $time */
    private $time;
    /** @var array $offers */
    private $offers;
    /** @var array $arMeasures */
    private $arMeasures;
    /** @var StoreService $storeService */
    private $storeService;
    /** @var \FourPaws\StoreBundle\Entity\StoreSearchResult */
    private $stores;

    use LazyLoggerAwareTrait;

    /**
     * EdadealFeedService constructor.
     *
     * @param SerializerInterface $serializer
     * @param Filesystem $filesystem
     */
    public function __construct(SerializerInterface $serializer, Filesystem $filesystem)
    {
        parent::__construct($serializer, $filesystem, '');
    }

    /**
     * @param ConfigurationInterface $configuration
     * @param int $step
     * @param string $stockID
     *
     * If need to continue, return true. Else - false.
     *
     * @return boolean
     *
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     */
    public function process(ConfigurationInterface $configuration, int $step, string $stockID = null): bool
    {
        /**
         * @var Configuration $configuration
         */

        $this->arResult = [
            'catalogs' => [],
            'offers' => [],
            'version' => 2
        ];

        $this->time = ConvertTimeStamp(time(), 'FULL');

        $this->getStores($configuration);
        $this->getCurrentShares($configuration);
        if (count($this->offers) > 0) {
            $this->getCurrentOffers($configuration);
        }

        $this->prepareResult();

        return $this->createResultFile($configuration, $this->arResult);
    }

    /**
     * @param Configuration $configuration
     */
    private function getStores(Configuration $configuration)
    {
        $container = App::getInstance()->getContainer();
        $this->storeService = $container->get('store.service');
        $tmpStores = $this->storeService->getAllStores(StoreService::TYPE_SHOP);
        foreach ($tmpStores->getStores() as $store) {
            $address = $store->getAddress();
            $arAddress = explode(', ', $address);
            $city = $arAddress[count($arAddress) - 1];
            if ($city == '-' || $store->getAddress() == '-') {
                continue;
            }
            $this->stores[] = $city . ', ' . str_replace(', ' . $city, '', $store->getAddress());
        }
    }

    /**
     * @param Configuration $configuration
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     */
    private function getCurrentShares(Configuration $configuration): void
    {
        $files = [];
        $this->offers = [];

        $arOrder = [
            'ID' => 'ASC'
        ];

        $arFilter = [
            'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::SHARES),
            'ACTIVE' => 'Y',
            '<=DATE_ACTIVE_FROM' => $this->time,
            '>=DATE_ACTIVE_TO' => $this->time,
            '!PROPERTY_PRODUCTS' => false,
            '!PREVIEW_PICTURE' => false
        ];

        $arSelect = [
            'ID',
            'IBLOCK_ID',
            'NAME',
            'DATE_ACTIVE_FROM',
            'DATE_ACTIVE_TO',
            'PREVIEW_PICTURE',
            'PREVIEW_TEXT',
            'DETAIL_TEXT'
        ];

        $dbShare = \CIBlockElement::GetList($arOrder, $arFilter, false, false, $arSelect);
        while ($cibeShare = $dbShare->GetNextElement()) {
            $share = $cibeShare->GetFields();
            $share['PROPERTIES'] = $cibeShare->GetProperties();

            if (
                $share['DATE_ACTIVE_FROM'] != '' &&
                $share['DATE_ACTIVE_TO'] != '' &&
                (
                    $share['PROPERTIES']['SHARE_TYPE']['VALUE'] == '' ||
                    $share['PROPERTIES']['SHARE_TYPE']['VALUE'] == 'aktsiya-v-im-roznitse' ||
                    $share['PROPERTIES']['SHARE_TYPE']['VALUE'] == 'aktsiya-v-roznitse'
                )
            ) {
                if (strpos($share['DATE_ACTIVE_FROM'], ' ') === false) {
                    $share['DATE_ACTIVE_FROM'] .= '00:00:00';
                }
                if (strpos($share['DATE_ACTIVE_TO'], ' ') === false) {
                    $share['DATE_ACTIVE_TO'] .= '23:59:59';
                }

                $products = array_unique($share['PROPERTIES']['PRODUCTS']['VALUE']);

                $descr = 'Акция действительна в магазинах торговой сети "Четыре Лапы" при наличии товара в магазине. Количество товара ограничено.';

                $this->arResult['catalogs'][$share['ID']] = [
                    'id' => $share['ID'],
                    'conditions' => str_replace("\r\n", '', html_entity_decode(\HTMLToTxt($descr))),
                    'date_start' => \DateTime::createFromFormat('d.m.Y H:i:s',
                        $share['DATE_ACTIVE_FROM'])->format(\DateTime::RFC3339),
                    'date_end' => \DateTime::createFromFormat('d.m.Y H:i:s',
                        $share['DATE_ACTIVE_TO'])->format(\DateTime::RFC3339),
                    'is_main' => true,
                    'image' => $path = \sprintf(
                        'http%s://%s%s',
                        $configuration->isHttps() ? 's' : '',
                        $configuration->getServerName(),
                        '/upload/edadeal/edadeal.jpg'
                    ),
                    'offers' => $products !== null ? $products : [],
                    'target_shops' => $this->stores,
                    'label' => $share['PROPERTIES']['LABEL']['VALUE']
                ];

                $this->offers = array_merge($this->offers, $products !== null ? $products : []);
            }
        }
    }

    /**
     * @param Configuration $configuration
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     */
    private function getCurrentOffers(Configuration $configuration)
    {
        //инфа об офферах
        $arOrder = [
            'ID' => 'ASC'
        ];

        $arFilter = [
            'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS),
            'XML_ID' => $this->offers,
            'ACTIVE' => 'Y',
            '!PROPERTY_IMG' => false
        ];

        $arSelect = [
            'ID',
            'IBLOCK_ID',
            'NAME',
            'CATALOG_GROUP_2',
            'XML_ID',
            'WEIGHT'
        ];

        //единицы измерения
        $this->getCurrentMeasures();

        $files = [];
        $products = [];
        $dbItems = \CIBlockElement::GetList($arOrder, $arFilter, false, false, $arSelect);
        while ($cibeOffer = $dbItems->GetNextElement()) {
            $offer = $cibeOffer->GetFields();
            $offer['PROPERTIES'] = $cibeOffer->GetProperties();
            if (intval($offer['CATALOG_QUANTITY']) == 0) {
                foreach ($this->arResult['catalogs'] as &$catalog) {
                    if (isset($catalog['offers'][$offer['XML_ID']])) {
                        unset($catalog['offers'][$offer['XML_ID']]);
                    }
                }
                continue;
            }

            $this->arResult['offers'][$offer['XML_ID']] = [
                'id' => $offer['XML_ID'],
                'sku' => $offer['ID'],
                'image' => $offer['PROPERTIES']['IMG']['VALUE'][0],
                'description' => $offer['NAME']
            ];

            if (
            (floatval($offer['PROPERTIES']['PRICE_ACTION']['VALUE']) < floatval($offer['CATALOG_PRICE_2'])
                && $offer['PROPERTIES']['PRICE_ACTION']['VALUE'] != ''
                && $offer['PROPERTIES']['PRICE_ACTION']['VALUE'] != 0)
            ) {
                $this->arResult['offers'][$offer['XML_ID']]['price_old'] = floatval($offer['CATALOG_PRICE_2']);
                $this->arResult['offers'][$offer['XML_ID']]['price_new'] = floatval($offer['PROPERTIES']['PRICE_ACTION']['VALUE']);
            } else {
                $this->arResult['offers'][$offer['XML_ID']]['price_new'] = floatval($offer['CATALOG_PRICE_2']);
            }

            if (strpos(mb_strtolower($offer['NAME']), 'корм') !== false) {
                $this->arResult['offers'][$offer['XML_ID']]['quantity'] = (float)WordHelper::showWeightNumber($offer['CATALOG_WEIGHT'], true);
                $this->arResult['offers'][$offer['XML_ID']]['quantity_unit'] = 'кг';
            }

            //штрих-код
            if (!empty($offer['PROPERTIES']['BARCODE']['VALUE'][0])) {
                $this->arResult['offers'][$offer['XML_ID']]['barcode'] = $offer['PROPERTIES']['BARCODE']['VALUE'][0];
            }

            //img
            if (!empty($offer['PROPERTIES']['IMG']['VALUE'][0])) {
                $files[$offer['PROPERTIES']['IMG']['VALUE'][0]] = $offer['XML_ID'];
            }

            $products[$offer['PROPERTIES']['CML2_LINK']['VALUE']][] = $offer['XML_ID'];
        }

        $this->getCurrentProducts($products);

        //файлы офферов
        $this->setFilesPaths($configuration, $files, 'offers');
    }

    /**
     * @param array $products
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     */
    private function getCurrentProducts(array $products)
    {
        $arSelect = [
            'ID',
            'IBLOCK_ID',
            'PROPERTY_BRAND.NAME',
            'ACTIVE'
        ];

        $arFilter = [
            'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS),
            'ID' => array_keys($products)
        ];

        $dbProduct = \CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
        while ($arProduct = $dbProduct->Fetch()) {
            //берем первое предложение из описания
            if ($arProduct['ACTIVE'] == 'Y') {
                foreach ($products[$arProduct['ID']] as $offer) {
                    $this->arResult['offers'][$offer]['brand'] = $arProduct['PROPERTY_BRAND_NAME'];
                }
            } else {
                foreach ($products[$arProduct['ID']] as $offer) {
                    unset($this->arResult['offers'][$offer]);
                }
            }
        }
    }

    private function getCurrentMeasures()
    {
        $this->arMeasures = [];
        $dbMeasure = \CCatalogMeasure::getList();
        while ($arMeasure = $dbMeasure->GetNext()) {
            $this->arMeasures[$arMeasure['ID']] = $arMeasure['SYMBOL'];
        }
    }


    /**
     * @param Configuration $configuration
     * @param array $files
     * @param string $key
     * @return bool
     */
    private function setFilesPaths(Configuration $configuration, array $files, string $key): bool
    {
        $uploadDir = \COption::GetOptionString('main', 'upload_dir', 'upload');
        $dbFiles = \CFile::GetList([], ['@ID' => implode(',', array_keys($files))]);
        while ($file = $dbFiles->Fetch()) {
            $path = '/' . $uploadDir . '/' . $file['SUBDIR'] . '/' . $file['FILE_NAME'];
            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
                unset($this->arResult[$key][$files[$file['ID']]]);
            }

            $path = \sprintf(
                'http%s://%s%s',
                $configuration->isHttps() ? 's' : '',
                $configuration->getServerName(),
                $path
            );

            if (isset($this->arResult[$key][$files[$file['ID']]]['image'])) {
                $this->arResult[$key][$files[$file['ID']]]['image'] = $path;
            }
        }
        return true;
    }

    private function prepareResult()
    {
        foreach ($this->arResult['catalogs'] as &$catalog) {
            if ($catalog['label'] != '' && strpos($catalog['label'], '+') !== false) {
                foreach ($catalog['offers'] as $offerID) {
                    $this->arResult['offers'][$offerID]['discount_label'] = $catalog['label'];
                }
            }
            unset($catalog['label']);
            $catalog['offers'] = array_values($catalog['offers']);
        }
        unset($catalog);

        foreach ($this->arResult['offers'] as $key => $offer) {
            $hasInCatalog = false;
            foreach ($this->arResult['catalogs'] as $catalog) {
                foreach ($catalog['offers'] as $offerID) {
                    if ($offer['id'] == $offerID) {
                        $hasInCatalog = true;
                    }
                }
            }
            if (!$hasInCatalog) {
                unset($this->arResult['offers'][$key]);
            }
        }

        foreach ($this->arResult['catalogs'] as $catalogID => $catalog) {
            foreach ($catalog['offers'] as $key => $offerID) {
                if (!in_array($offerID, array_keys($this->arResult['offers']))) {
                    unset($this->arResult['catalogs'][$catalogID]['offers'][$key]);
                }
            }
            if (count($this->arResult['catalogs'][$catalogID]['offers']) == 0) {
                unset($this->arResult['catalogs'][$catalogID]);
            }
        }

        foreach ($this->arResult['catalogs'] as $catalogID => &$catalog) {
            $catalog['offers'] = array_values($catalog['offers']);
        }

        $this->arResult['catalogs'] = array_values($this->arResult['catalogs']);
        $this->arResult['offers'] = array_values($this->arResult['offers']);
    }

    /**
     * @param Configuration $configuration
     * @param array $arResult
     * @return bool
     */
    private function createResultFile(Configuration $configuration, array $arResult): bool
    {
        $this->publicFeedJson($arResult, Application::getAbsolutePath($configuration->getExportFile()));
        return true;
    }

}
