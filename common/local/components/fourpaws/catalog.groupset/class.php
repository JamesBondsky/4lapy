<?php
/**
 * Created by PhpStorm.
 * Date: 26.04.2018
 * Time: 17:50
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\Components;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Sale\BasketItem;
use CBitrixComponent;
use FourPaws\App\Application;
use FourPaws\BitrixOrm\Model\Share;
use FourPaws\Catalog\Model\Offer;
use FourPaws\CatalogBundle\AjaxController\ProductInfoController;
use FourPaws\Helpers\WordHelper;
use FourPaws\SaleBundle\Service\BasketService;

/** @noinspection AutoloadingIssuesInspection */

/**
 * Class GroupSet
 * @package FourPaws\Components
 */
class GroupSet extends CBitrixComponent
{

    protected $basketService;

    /**
     * GroupSet constructor.
     *
     * @param CBitrixComponent|null $component
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);

        $container = Application::getInstance()->getContainer();
        $this->basketService = $container->get(BasketService::class);
    }

    public function executeComponent()
    {
        /** @var Offer $currentOffer */
        $currentOffer = $this->arParams['OFFER'];
        $groupSets = [];
        try {
            $groupSets = ProductInfoController::getGroupSets($currentOffer);
        } /** @noinspection PhpUndefinedClassInspection */ catch (TypeError $e) {
            echo 'Не передан предложение', PHP_EOL;
            $logger = LoggerFactory::create('productDetail');
            $logger->error($e->getMessage());
        }
        if (empty($groupSets)) {
            return null;
        }
        $this->arResult['SHARE'] = $groupSets[0]['share'];
        $this->arResult['EMPTY_SLOTS'] = \count($groupSets[0]['groupSet']) - 1;
        $this->loadTemplateFields();
        $this->includeComponentTemplate();
        return $this->arResult['PRICE'] > 0 && $this->arResult['OFFER_ID'] > 0;
    }

    protected function loadTemplateFields()
    {
        /** @var Offer $currentOffer */
        $currentOffer = $this->arParams['OFFER'];
        /** @var Share $share */
        $share = $this->arResult['SHARE'];

        $this->arResult['IMG'] = $currentOffer->getResizeImages(140, 140)->first();
        $this->arResult['OFFER_ID'] = $currentOffer->getId();
        $this->arResult['PRICE'] = $currentOffer->getPrice();

        $product = $currentOffer->getProduct();
        $this->arResult['NAME']
            = '<strong>' . $product->getBrandName() . '</strong> ' . \lcfirst(\trim($product->getName()));

        if (0 < $weight = $currentOffer->getCatalogProduct()->getWeight()) {
            $weight = WordHelper::showWeight($weight);
        } else {
            $weight = '';
        }
        $this->arResult['WEIGHT'] = $weight;
        $this->arResult['PROMO_DESCRIPTION'] = $share->getPreviewText();

    }
}