<?php

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Application as BitrixApplication;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\SystemException;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application;
use FourPaws\App\Templates\MediaEnum;
use FourPaws\AppBundle\Bitrix\FourPawsComponent;
use FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException;
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\Helpers\WordHelper;
use FourPaws\PersonalBundle\Entity\Order;
use FourPaws\PersonalBundle\Entity\OrderSubscribeItem;
use FourPaws\PersonalBundle\Service\OrderService as PersonalOrderService;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;
use FourPaws\StoreBundle\Service\StoreService;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;


/** @noinspection AutoloadingIssuesInspection
 *
 * Class FourPawsPersonalCabinetOrderItemComponent
 */
class FourPawsPersonalCabinetOrderItemComponent extends FourPawsComponent
{
    use LazyLoggerAwareTrait;

    /**
     * @var StoreService
     */
    private $storeService;

    /**
     * @var OrderSubscribeService $orderSubscribeService
     */
    private $orderSubscribeService;


    /**
     * FourPawsPersonalCabinetOrderItemComponent constructor.
     *
     * @param null|\CBitrixComponent $component
     *
     * @throws LogicException
     * @throws SystemException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     */
    public function __construct($component = null)
    {
        // LazyLoggerAwareTrait не умеет присваивать имя по классам без неймспейса
        // делаем это вручную
        $this->logName = __CLASS__;
        $this->storeService = Application::getInstance()->getContainer()->get('store.service');
        parent::__construct($component);
    }

    /**
     * @param $params
     *
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
        $params['ORDER'] = $params['ORDER'] ?? null;
        if (!$params['ORDER'] instanceof Order) {
            $params['ORDER'] = null;
        }

        $params['CACHE_TYPE'] = $params['CACHE_TYPE'] ?? 'A';
        $params['CACHE_TIME'] = $params['CACHE_TIME'] ?? 3600;
        $params['CACHE_TIME'] = 0;

        // подстраховка для идентификатора кеша
        $params['ORDER_ID'] = $params['ORDER']->getId();

        return parent::onPrepareComponentParams($params);
    }

    /**
     * @throws Exception
     * @throws SystemException
     */
    public function prepareResult(): void
    {
        /** @var Order $personalOrder */
        $personalOrder = $this->arParams['ORDER'];

        (new TaggedCacheHelper($this->getResultCachePath()))->addTag('order:item:' . $personalOrder->getId());

        $this->arResult['ORDER'] = $personalOrder;
        $this->arResult['METRO'] = new ArrayCollection($this->storeService->getMetroInfo());
        if($this->arParams['ORDER_SUBSCRIBE']){
            try {
                $this->arResult['ITEMS'] = $this->getSubscribeItemsFormatted();
            } catch (\Exception $e) {
                $this->setError(sprintf("Произошла ошибка: %s", $e->getMessage()));
            }
        }

        $statusId = $personalOrder->getStatusId();

        $this->arResult['CAN_CANCEL'] = ($statusId && !in_array($statusId, PersonalOrderService::STATUS_FINAL, true) && !(in_array($statusId, PersonalOrderService::STATUS_CANCEL, true)));
        $this->arResult['CAN_EXTEND'] = true;
    }

    /**
     * @return OrderSubscribeService
     * @throws LogicException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     */
    public function getOrderSubscribeService(): OrderSubscribeService
    {
        if (!$this->orderSubscribeService) {
            $appCont = Application::getInstance()->getContainer();
            $this->orderSubscribeService = $appCont->get('order_subscribe.service');
        }

        return $this->orderSubscribeService;
    }

    protected function getResultCachePath()
    {
        /** @var Order $personalOrder */
        $personalOrder = $this->arParams['ORDER'];
        $cachePath = BitrixApplication::getInstance()->getManagedCache()->getCompCachePath(
            $this->getRelativePath()
        );

        return $cachePath . '/' . $personalOrder->getId();
    }

    /**
     * @return array
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\PersonalBundle\Exception\NotFoundException
     * @throws Exception
     */
    protected function getSubscribeItemsFormatted()
    {
        $items = [];
        $orderSubscribe = $this->arParams['ORDER_SUBSCRIBE'];
        $orderSubscribeItems = $this->getOrderSubscribeService()->getItemsBySubscribeId($orderSubscribe->getId());

        if($orderSubscribeItems->isEmpty()){
            throw new Exception('Не найдены товары в подписке');
        }

        /** @var OrderSubscribeItem $orderSubscribeItem */
        foreach($orderSubscribeItems as $orderSubscribeItem){
            /** @var Offer $offer */
            $offer = $orderSubscribeItem->getOffer();

            $images = $offer->getResizeImages(80, 145);
            if(!empty($images)){
                $image = $images->first()->getSrc();
            } else{
                $image = $path = (new ResizeImageDecorator())->setSrc(MediaEnum::NO_IMAGE_WEB_PATH)
                    ->setResizeWidth(80)
                    ->setResizeHeight(145)
                    ->getSrc();
            }

            $sum = $offer->getSubscribePrice() * $orderSubscribeItem->getQuantity();


            $item = [
                'ID' => $offer->getId(),
                'IMAGE' => $image,
                'NAME' => $offer->getName(),
                'DETAIL_PAGE_URL' => $offer->getDetailPageUrl(),
                'HAS_STOCK' => $offer->isShare(),
                'BRAND' => $offer->getProduct()->getBrandName(),
                'FLAVOUR' => $offer->getFlavourWithWeight(),
                'WEIGHT' => $offer->getCatalogProduct()->getWeight(),
                'ARTICLE' => $offer->getXmlId(),
                'PRICE' => \number_format($offer->getSubscribePrice(), 2, '.', ' '),
                'QUANTITY' => $orderSubscribeItem->getQuantity(),
                'SUM' => \number_format($sum, 2, '.', ' '),
            ];

            $items[] = $item;
        }

        return $items;
    }

    /**
     * @param int $id
     * @return string
     */
    protected function resizeImage(int $id): string
    {
        try {
            $path = ResizeImageDecorator::createFromPrimary($id)
                ->setResizeWidth(80)
                ->setResizeHeight(145)->getSrc();
        } catch (FileNotFoundException $e) {
            $path = (new ResizeImageDecorator())->setSrc(MediaEnum::NO_IMAGE_WEB_PATH)
                ->setResizeWidth(80)
                ->setResizeHeight(145)
                ->getSrc();
        }

        return $path;
    }

    /**
     * @param $message
     * @return mixed
     */
    private function setError($message)
    {
        return $this->arResult['ERROR'] = $message;
    }

}
