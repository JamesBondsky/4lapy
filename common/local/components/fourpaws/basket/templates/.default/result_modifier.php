<?php

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Application;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Components\BasketComponent;
use FourPaws\Components\FourPawsFastOrderComponent;

/** @global BasketComponent $component */
$component = $this->getComponent();

/** @var Basket $basket */
$basket = $arResult['BASKET'];
$orderableBasket = $basket->getOrderableItems();

$notAlowedItems = new ArrayCollection();
/** @var BasketItem $basketItem */
$isUpdate = false;
$updatedItems = [];
$orderableIds = [];
foreach ($orderableBasket as $basketItem) {
    if ($basketItem->getId() === 0 || $basketItem->getProductId() === 0) {
        $basketItem->delete();
        $isUpdate = true;
        continue;
    }
    $offer = $component->getOffer((int)$basketItem->getProductId());
    $useOffer = $offer instanceof Offer && $offer->getId() > 0;
    if (!$useOffer) {
        $basketItem->delete();
        $isUpdate = true;
        continue;
    }
    $orderableIds[] = $basketItem->getId();
    if (!$basketItem->isDelay() && ($offer->isByRequest() || $offer->getQuantity() === 0)) {
        $basketItem->setField('DELAY', 'Y');
        $updatedItems[] = $basketItem->getId();
        $isUpdate = true;
    }
}

$basketItems = $basket->getBasketItems();
foreach ($basketItems as $basketItem) {
    if ($basketItem->getId() === 0 || $basketItem->getProductId() === 0) {
        continue;
    }
    $offer = $component->getOffer((int)$basketItem->getProductId());
    $useOffer = $offer instanceof Offer && $offer->getId() > 0;
    if (!$useOffer) {
        continue;
    }
    if ($orderableBasket->isEmpty() || !\in_array($basketItem->getId(), $orderableIds, true)) {
        $notAlowedItems->add($basketItem);
    }
    $offerQuantity = $offer->getQuantity();
    if ($offerQuantity > 0 && $offerQuantity > $basketItem->getQuantity() && $basketItem->isDelay()
        && !$offer->isByRequest() && !\in_array($basketItem->getId(), $updatedItems,
            true) && !\in_array($basketItem->getId(), $orderableIds, true)) {
        $basketItem->setField('DELAY', 'N');
        $isUpdate = true;
    }
}
unset($updatedItems);
if ($isUpdate) {
    $basket->save();
    LocalRedirect(Application::getInstance()->getContext()->getRequest()->getRequestedPageDirectory());
}
unset($isUpdate);

$arResult['NOT_ALOWED_ITEMS'] = $notAlowedItems;

\CBitrixComponent::includeComponentClass('fourpaws:fast.order');
/** @noinspection ExceptionsAnnotatingAndHandlingInspection */
try {
    $fastOrderClass = new FourPawsFastOrderComponent();
} catch (SystemException $e) {
    $logger = LoggerFactory::create('system');
    $logger->error('Ошибка загрузки компонента - ' . $e->getMessage());
    return $this->ajaxMess->getSystemError();
}

/** @todo пока берем ближайшую доставку из быстрого заказа */
$arResult['OFFER_MIN_DELIVERY'] = [];
foreach ($notAlowedItems as $basketItem) {
    if ($basketItem->getId() === 0 || $basketItem->getProductId() === 0) {
        continue;
    }
    $offer = $component->getOffer((int)$basketItem->getProductId());
    $useOffer = $offer instanceof Offer && $offer->getId() > 0;
    if (!$useOffer) {
        continue;
    }
    if ($offer->isByRequest()) {
        $arResult['OFFER_MIN_DELIVERY'][$basketItem->getProductId()] = $fastOrderClass->getDeliveryDate($component->getOffer((int)$basketItem->getProductId()), true);
    }
}
$a = 1;