<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\Repository;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\Query\Join;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Internals\BasketTable;
use Bitrix\Sale\Internals\OrderPropsValueTable;
use Bitrix\Sale\Internals\OrderTable;
use Bitrix\Sale\Internals\PaySystemActionTable;
use Bitrix\Sale\Internals\ShipmentTable;
use Bitrix\Sale\Internals\StatusTable;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\AppBundle\Exception\EmptyEntityClass;
use FourPaws\AppBundle\Repository\BaseRepository;
use FourPaws\BitrixOrm\Query\IblockElementQuery;
use FourPaws\BitrixOrm\Utils\IblockPropEntityConstructor;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Helpers\WordHelper;
use FourPaws\PersonalBundle\Entity\Order;
use FourPaws\PersonalBundle\Entity\OrderDelivery;
use FourPaws\PersonalBundle\Entity\OrderItem;
use FourPaws\PersonalBundle\Entity\OrderPayment;
use FourPaws\PersonalBundle\Entity\OrderProp;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class OrderRepository
 *
 * @package FourPaws\PersonalBundle\Repository
 */
class OrderRepository extends BaseRepository
{
    /** @var UserService */
    public $curUserService;
    /** @var Order $entity */
    protected $entity;

    /**
     * OrderRepository constructor.
     *
     * @inheritdoc
     *
     * @param CurrentUserProviderInterface $currentUserProvider
     */
    public function __construct(
        ValidatorInterface $validator,
        ArrayTransformerInterface $arrayTransformer,
        CurrentUserProviderInterface $currentUserProvider
    ) {
        parent::__construct($validator, $arrayTransformer);
        $this->setDataManager(new OrderTable());
        $this->setEntityClass(Order::class);
        $this->curUserService = $currentUserProvider;
    }

    /**
     * @param array $params
     *
     * массив
     * [
     *      'select'=>array
     *      'filter'=>array
     *      'order'=>array
     *      'limit'=>int
     *      'offset'=>int
     *      'ttl'=>int
     *      'group'=>array
     *      'runtime'=>array
     *      'countTotal'=>bool
     * ]
     *
     * @throws ArgumentException
     * @return ArrayCollection|Order[]
     */
    public function getUserOrders(array $params = []): ArrayCollection
    {
        if (!isset($params['filter']['USER_ID'])) {
            $params['filter']['USER_ID'] = $this->curUserService->getCurrentUserId();
        }
        $params['runtime'][] = new ReferenceField(
            'STATUS_MAIN',
            StatusTable::getEntity(),
            Join::on('this.STATUS_ID', 'ref.ID')
        );
        $params['order'] = ['STATUS_MAIN.SORT' => 'asc', 'DATE_INSERT' => 'desc'];
        $params['setKey'] = 'ID';
        return $this->findBy($params);
    }

    /**
     * @param int $orderId
     *
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws EmptyEntityClass
     * @throws SystemException
     * @throws ArgumentException
     * @throws IblockNotFoundException
     * @throws \Exception
     * @return array
     */
    public function getOrderItems(int $orderId): array
    {
        $queryCacheTtl = 360000;
        $iblockId = IblockUtils::getIblockId(
            IblockType::CATALOG,
            IblockCode::OFFERS
        );
        $productIblockId = IblockUtils::getIblockId(
            IblockType::CATALOG,
            IblockCode::PRODUCTS
        );

        /**
         * получаем свойства для обращения к свойствам и кешируем запросы на долгое время, ибо меняться будет крайне редко
         */
        $volumePropId = PropertyTable::query()
                            ->where('IBLOCK_ID', $iblockId)
                            ->whereIn('CODE', 'VOLUME_REFERENCE')
                            ->setCacheTtl($queryCacheTtl)
                            ->setSelect(['ID'])
                            ->exec()->fetch()['ID'];

        $sizePropId = PropertyTable::query()->where('IBLOCK_ID', $iblockId)->where(
            'CODE',
            'CLOTHING_SIZE'
        )->setCacheTtl($queryCacheTtl)->setSelect(['ID'])->exec()->fetch()['ID'];
        $cml2LinkPropId = PropertyTable::query()->where('IBLOCK_ID', $iblockId)->where(
            'CODE',
            'CML2_LINK'
        )->setCacheTtl($queryCacheTtl)->setSelect(['ID'])->exec()->fetch()['ID'];
        $imgPropId = PropertyTable::query()->where('IBLOCK_ID', $iblockId)->where(
            'CODE',
            'IMG'
        )->setCacheTtl($queryCacheTtl)->setSelect(['ID'])->exec()->fetch()['ID'];

        $brandPropId = PropertyTable::query()->where('IBLOCK_ID', $productIblockId)->where(
            'CODE',
            'BRAND'
        )->setCacheTtl($queryCacheTtl)->setSelect(['ID'])->exec()->fetch()['ID'];
        $flavourPropId = PropertyTable::query()->where('IBLOCK_ID', $productIblockId)->where(
            'CODE',
            'FLAVOUR'
        )->setCacheTtl($queryCacheTtl)->setSelect(['ID'])->exec()->fetch()['ID'];
        $basketRes = BasketTable::query()
            ->setSelect([
                '*',
                'SUMMARY_PRICE',

                'PROPERTY_IMG'    => 'OFFER_PROPS.PROPERTY_' . $imgPropId,
                'PROPERTY_VOLUME' => 'OFFER_PROPS.PROPERTY_' . $volumePropId,
                'PROPERTY_SIZE'   => 'OFFER_PROPS.PROPERTY_' . $sizePropId,

                'PROPERTY_BRAND'   => 'PRODUCT_PROPS.PROPERTY_' . $brandPropId,
                'PROPERTY_FLAVOUR' => 'PRODUCT_PROPS.PROPERTY_' . $flavourPropId,
            ])
            ->where('ORDER_ID', $orderId)
            ->registerRuntimeField(new ReferenceField(
                'OFFER_PROPS',
                IblockPropEntityConstructor::getDataClass($iblockId)::getEntity(),
                Join::on('this.PRODUCT_ID', 'ref.IBLOCK_ELEMENT_ID')
            ))
            ->registerRuntimeField(new ReferenceField(
                'PRODUCT_PROPS',
                IblockPropEntityConstructor::getDataClass($productIblockId)::getEntity(),
                Join::on('this.OFFER_PROPS.PROPERTY_' . $cml2LinkPropId, 'ref.IBLOCK_ELEMENT_ID')
            ))
            ->exec();
        $result = new ArrayCollection();
        $items = [];
        $allWeight = 0;
        $allSum = 0;
        while ($item = $basketRes->fetch()) {
            /**
             * @var array $item
             */
            if (!isset($items[$item['PRODUCT_ID']])) {
                if (empty($item['PROPERTY_SELECTED'])) {
                    if (!empty($item['PROPERTY_SIZE'])) {
                        $res = HLBlockFactory::createTableObject('ClothingSize')::query()
                            ->setSelect(['UF_NAME'])
                            ->where('UF_XML_ID', $item['PROPERTY_SIZE'])
                            ->setCacheTtl($queryCacheTtl)
                            ->exec();
                        if ($res->getSelectedRowsCount() > 0) {
                            $item['PROPERTY_SELECTED'] = $res->fetch()['UF_NAME'];
                            $item['PROPERTY_SELECTED_NAME'] = 'Размер';
                        } else {
                            $item['PROPERTY_SELECTED'] = '';
                            $item['PROPERTY_SELECTED_NAME'] = 'Размер';
                        }
                    } elseif (!empty($item['PROPERTY_VOLUME'])) {
                        $res = HLBlockFactory::createTableObject('Volume')::query()
                            ->setSelect(['UF_NAME'])
                            ->where('UF_XML_ID', $item['PROPERTY_VOLUME'])
                            ->setCacheTtl($queryCacheTtl)
                            ->exec();
                        if ($res->getSelectedRowsCount() > 0) {
                            $item['PROPERTY_SELECTED'] = $res->fetch()['UF_NAME'];
                            $item['PROPERTY_SELECTED_NAME'] = 'Вариант фасовки';
                        } else {
                            $item['PROPERTY_SELECTED'] = '';
                            $item['PROPERTY_SELECTED_NAME'] = 'Вариант фасовки';
                        }
                    } else {
                        $item['PROPERTY_SELECTED'] = WordHelper::showWeight((float)$item['WEIGHT']);
                        $item['PROPERTY_SELECTED_NAME'] = 'Вариант фасовки';
                    }

                    /** установка фалага акции для товара, кешировать не надо - в компоненте кешируется вывод, больше нигде не используется
                     * @todo вынести из цикла и делать 1 запрос на получение по заказу*/
                    if(strpos($item['PRODUCT_XML_ID'], '#') !== false){
                        $explode = explode('#',$item['PRODUCT_XML_ID']);
                        $xmlId = end($explode);
                    }
                    else{
                        $xmlId = $item['PRODUCT_XML_ID'];
                    }
                    $shares = (new IblockElementQuery())->withOrder(['SORT'=>'ASC','ACTIVE_FROM'=>'DESC'])->withFilter([
                        'IBLOCK_ID'         => IblockUtils::getIblockId(IblockType::PUBLICATION,
                            IblockCode::SHARES),
                        'ACTIVE'            => 'Y',
                        'ACTIVE_DATE'       => 'Y',
                        'PROPERTY_PRODUCTS' => $xmlId,
                    ])->withNav(['nTopCount'=>1])->exec();
                    $item['HAVE_STOCK'] = $shares->isEmpty() ? 'N' : 'Y';
                }

                unset($item['PROPERTY_SIZE'], $item['PROPERTY_VOLUME']);

                if (!empty($item['PROPERTY_FLAVOUR'])) {
                    $unserialize = unserialize($item['PROPERTY_FLAVOUR']);
                    if (\is_array($unserialize['VALUE']) && !empty($unserialize['VALUE'])) {
                        $res = HLBlockFactory::createTableObject('Flavour')::query()
                            ->setSelect(['UF_NAME'])
                            ->whereIn('UF_XML_ID', $unserialize['VALUE'])
                            ->setCacheTtl($queryCacheTtl)
                            ->exec();
                        if ($res->getSelectedRowsCount() > 0) {
                            $vals = [];
                            while ($hlItem = $res->fetch()) {
                                $vals[] = $hlItem['UF_NAME'];
                            }
                            $item['PROPERTY_FLAVOUR'] = implode(', ', $vals);
                        } else {
                            $item['PROPERTY_FLAVOUR'] = '';
                        }
                    } else {
                        $item['PROPERTY_FLAVOUR'] = '';
                    }
                }

                if (!empty($item['PROPERTY_BRAND'])) {
                    $res = ElementTable::query()
                        ->setSelect(['NAME'])
                        ->where('ID', $item['PROPERTY_BRAND'])
                        ->setCacheTtl($queryCacheTtl)
                        ->exec();
                    if ($res->getSelectedRowsCount() > 0) {
                        $item['PROPERTY_BRAND'] = $res->fetch()['NAME'];
                    }
                }

                if (!empty($item['PRODUCT_XML_ID'])) {
                    $explode = explode('#', $item['PRODUCT_XML_ID']);
                    if (\is_array($explode)) {
                        $item['PRODUCT_XML_ID'] = end($explode);
                    }
                }

                $allWeight += (float)$item['WEIGHT'] * (float)$item['QUANTITY'];
                $allSum += (float)$item['SUMMARY_PRICE'];
                $key = !empty($item['PRODUCT_XML_ID']) ? $item['PRODUCT_XML_ID'] : '';
                if (\mb_strlen($key) <= 1) {
                    $key = $item['PRODUCT_ID'];
                }
                $items[$key] = $item;
            }
        }
        if ($basketRes->getSelectedRowsCount() > 0) {
            $result = new ArrayCollection($this->dataToEntity(
                $items,
                sprintf('array<string, %s>', OrderItem::class)
            ));
        }
        return [$result, $allWeight, $allSum];
    }

    /**
     * @param int $paySystemId
     *
     * @throws EmptyEntityClass
     * @return BaseEntity|OrderPayment
     */
    public function getPayment(int $paySystemId): OrderPayment
    {
        $payment = PaySystemActionTable::query()
            ->where('PAY_SYSTEM_ID', $paySystemId)
            ->setCacheTtl(360000)
            ->setLimit(1)
            ->setSelect([
                'ID',
                'NAME',
                'CODE',
            ])->exec()->fetch();
        if (\is_array($payment)) {
            return $this->dataToEntity(
                $payment,
                OrderPayment::class
            );
        }
        return new OrderPayment();
    }

    /**
     * @param int $orderId
     *
     * @throws EmptyEntityClass
     * @return BaseEntity|OrderDelivery
     */
    public function getDelivery(int $orderId): OrderDelivery
    {
        $shipment = ShipmentTable::query()
            ->where('ORDER_ID', $orderId)
            ->where('SYSTEM', 'N')
            ->where('EXTERNAL_DELIVERY', 'N')
            ->setLimit(1)
            ->setCacheTtl(360000)
            ->setSelect([
                'ID',
                'DELIVERY_NAME',
                'PRICE_DELIVERY',
                'DEDUCTED',
                'DATE_DEDUCTED',
            ])->exec()->fetch();
        if (\is_array($shipment)) {
            return $this->dataToEntity(
                $shipment,
                OrderDelivery::class
            );
        }

        return new OrderDelivery();
    }

    /**
     * @param int $orderId
     *
     * @throws EmptyEntityClass
     * @return ArrayCollection|OrderProp[]
     */
    public function getOrderProps(int $orderId): ArrayCollection
    {
        $props = [];
        $propRes = OrderPropsValueTable::query()
            ->where('ORDER_ID', $orderId)
            ->setCacheTtl(360000)
            ->setSelect([
                'NAME',
                'VALUE',
                'CODE',
                'ID',
            ])->exec();
        while ($prop = $propRes->fetch()) {
            $props[$prop['CODE']] = $prop;
        }
        if (!empty($props)) {
            return new ArrayCollection($this->dataToEntity(
                $props,
                sprintf('array<string, %s>', OrderProp::class)
            ));
        }

        return new ArrayCollection();
    }
}
