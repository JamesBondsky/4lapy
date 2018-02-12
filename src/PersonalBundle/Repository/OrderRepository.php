<?php

namespace FourPaws\PersonalBundle\Repository;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
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
use FourPaws\BitrixOrm\Utils\IblockPropEntityConstructor;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
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
     * @return ArrayCollection
     * @throws \Exception
     */
    public function getUserOrders(array $params = []): ArrayCollection
    {
        if (!isset($params['filter']['USER_ID'])) {
            $params['filter']['USER_ID'] = $this->curUserService->getCurrentUserId();
        }
        $params['runtime'][] = new ReferenceField('STATUS_MAIN', StatusTable::getEntity(),
            Join::on('this.STATUS_ID', 'ref.ID'));
        $params['order'] = ['STATUS_MAIN.SORT' => 'asc', 'DATE_INSERT' => 'desc'];
        $params['setKey'] = 'ID';
        return $this->findBy($params);
    }

    /**
     * @param int $orderId
     *
     * @return array
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws EmptyEntityClass
     * @throws SystemException
     * @throws ArgumentException
     * @throws IblockNotFoundException
     * @throws \Exception
     */
    public function getOrderItems(int $orderId): array
    {
        $queryCacheTtl = 360000;
        $iblockId = IblockUtils::getIblockId(IblockType::CATALOG,
            IblockCode::OFFERS);
        $productIblockId = IblockUtils::getIblockId(IblockType::CATALOG,
            IblockCode::PRODUCTS);

        $volumePropId = PropertyTable::query()->where('IBLOCK_ID', $iblockId)->where('CODE',
            'VOLUME')->setCacheTtl($queryCacheTtl)->setSelect(['ID'])->exec()->fetch()['ID'];
        $sizePropId = PropertyTable::query()->where('IBLOCK_ID', $iblockId)->where('CODE',
            'CLOTHING_SIZE')->setCacheTtl($queryCacheTtl)->setSelect(['ID'])->exec()->fetch()['ID'];
        $cml2LinkPropId = PropertyTable::query()->where('IBLOCK_ID', $iblockId)->where('CODE',
            'CML2_LINK')->setCacheTtl($queryCacheTtl)->setSelect(['ID'])->exec()->fetch()['ID'];
        $imgPropId = PropertyTable::query()->where('IBLOCK_ID', $iblockId)->where('CODE',
            'IMG')->setCacheTtl($queryCacheTtl)->setSelect(['ID'])->exec()->fetch()['ID'];
        $brandPropId = PropertyTable::query()->where('IBLOCK_ID', $productIblockId)->where('CODE',
            'BRAND')->setCacheTtl($queryCacheTtl)->setSelect(['ID'])->exec()->fetch()['ID'];
        $basketRes = BasketTable::query()
            ->setSelect([
                '*',
                'PROPERTY_IMG'    => 'OFFER_PROPS.PROPERTY_' . $imgPropId,
                'PROPERTY_VOLUME' => 'OFFER_PROPS.PROPERTY_' . $volumePropId,
                'PROPERTY_SIZE'   => 'OFFER_PROPS.PROPERTY_' . $sizePropId,
//                'PROPERTY_BRAND'  => 'PRODUCT_PROPS.PROPERTY_' . $brandPropId,
            ])
            ->where('ORDER_ID', $orderId)
            ->registerRuntimeField(new ReferenceField('OFFER_PROPS',
                IblockPropEntityConstructor::getDataClass($iblockId)::getEntity(),
                Join::on('this.PRODUCT_ID', 'ref.IBLOCK_ELEMENT_ID')))
            ->registerRuntimeField(new ReferenceField('PRODUCT_PROPS',
                IblockPropEntityConstructor::getDataClass($iblockId)::getEntity(),
                Join::on('this.OFFER_PROPS.PROPERTY_' . $cml2LinkPropId, 'ref.IBLOCK_ELEMENT_ID')))
            ->setCacheTtl($queryCacheTtl)
            ->exec();
        $result = new ArrayCollection();
        $items = [];
        $allWeight = 0;
        $allSum = 0;
        while ($item = $basketRes->fetch()) {
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
                        }
                    }
                }
                unset($item['PROPERTY_SIZE'], $item['PROPERTY_VOLUME']);
                if (!empty($item['PROPERTY_BRAND'])) {
                    $res = HLBlockFactory::createTableObject('Maker')::query()
                        ->setSelect(['UF_NAME'])
                        ->where('ID', $item['PROPERTY_BRAND'])
                        ->setCacheTtl($queryCacheTtl)
                        ->exec();
                    if ($res->getSelectedRowsCount() > 0) {
                        $item['PROPERTY_BRAND'] = $res->fetch()['UF_NAME'];
                    }
                }
                $allWeight += $item['WEIGHT'] * $item['QUANTITY'];
                $allSum += $item['SUMMARY_PRICE'];
                $explode = explode('#', $item['PRODUCT_XML_ID']);
                $key = \is_array($explode) ? end($explode) : $item['PRODUCT_XML_ID'];
                if (\mb_strlen($key) <= 1) {
                    $key = $item['PRODUCT_ID'];
                }
                $items[$key] = $item;
            }
        }
        if ($basketRes->getSelectedRowsCount() > 0) {
            $result = new ArrayCollection($this->dataToEntity(
                $items, sprintf('array<string, %s>', OrderItem::class)));
        }
        return [$result, $allWeight, $allSum];
    }

    /**
     * @param int $paySystemId
     *
     * @return OrderPayment|BaseEntity
     * @throws EmptyEntityClass
     */
    public function getPayment(int $paySystemId): OrderPayment
    {
        return $this->dataToEntity(
            PaySystemActionTable::query()
                ->where('PAY_SYSTEM_ID', $paySystemId)
                ->setCacheTtl(360000)
                ->setLimit(1)
                ->setSelect([
                    'ID',
                    'NAME',
                ])->exec()->fetch(),
            OrderPayment::class);
    }

    /**
     * @param int $orderId
     *
     * @return OrderDelivery|BaseEntity
     * @throws EmptyEntityClass
     */
    public function getDelivery(int $orderId): OrderDelivery
    {
        return $this->dataToEntity(
            ShipmentTable::query()
                ->where('ORDER_ID', $orderId)
                ->where('SYSTEM', 'N')
                ->where('EXTERNAL_DELIVERY', 'N')
                ->setLimit(1)
                ->setCacheTtl(360000)
                ->setSelect([
                    'ID',
                    'DELIVERY_NAME',
                ])->exec()->fetch(),
            OrderDelivery::class);
    }

    /**
     * @param int $orderId
     *
     * @return ArrayCollection
     * @throws EmptyEntityClass
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
                sprintf('array<string, %s>', OrderProp::class)));
        }

        return new ArrayCollection();
    }
}