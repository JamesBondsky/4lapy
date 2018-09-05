<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Catalog\PriceTable;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\Query\Join;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Internals\BasketTable;
use Bitrix\Sale\Internals\OrderTable;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Bitrix\FourPawsComponent;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsPersonalCabinetTopComponent extends FourPawsComponent
{
    /**
     * @var CurrentUserProviderInterface
     */
    private $curUserProvider;

    /**
     * AutoloadingIssuesInspection constructor.
     *
     * @param null|\CBitrixComponent $component
     *
     * @throws ServiceNotFoundException
     * @throws SystemException
     * @throws \RuntimeException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     */
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);
        $container = App::getInstance()->getContainer();
        $this->curUserProvider = $container->get(CurrentUserProviderInterface::class);
    }

    /**
     * @param $params
     *
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
        $params['FUSER_ID'] = $params['FUSER_ID'] ?? $this->curUserProvider->getCurrentFUserId();
        $params['USER_ID'] = $params['USER_ID'] ?? $this->curUserProvider->getCurrentUserId();
        $params['CACHE_TYPE'] = 'N';
        $params['COUNT_ITEMS'] = $params['COUNT_ITEMS'] ?? 10;

        return $params;
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     * @throws ConstraintDefinitionException
     * @throws InvalidIdentifierException
     * @throws ArgumentOutOfRangeException
     * @throws ArgumentException
     * @throws \Exception
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws NotAuthorizedException
     * @throws LoaderException
     */
    public function prepareResult(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        try {
            $userId = $this->curUserProvider->getCurrentUserId();
        } catch (NotAuthorizedException $e) {
            define('NEED_AUTH', true);

            return;
        }

        $offerIds = $this->getPopularOffers($userId);
        $offers = $this->getAllOffers($offerIds);

        /**
         * сортировка по убыванию цены
         */
        uasort($offers, function (Offer $offer1, Offer $offer2) {
            return $offer2->getPrice() <=> $offer1->getPrice();
        });

        $products = $this->getAllProducts($offers);

        if (!$offers || !$products) {
            $this->setTemplatePage('notItems');
        } else {
            $this->arResult['PRODUCTS'] = $products;
            $this->arResult['OFFERS'] = $offers;
        }
    }

    /**
     * @param int $userId
     *
     * @return int[]
     * @throws ArgumentException
     * @throws IblockNotFoundException
     * @throws SystemException
     * @throws ObjectPropertyException
     */
    protected function getPopularOffers(int $userId): array
    {
        $offersIblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS);
        /**
         * Все элементы корзины из заказов, принадлежащих данному пользователю,
         * у которых цена > 0 и активен оффер, оффер не является подарком
         */
        $query = BasketTable::query()
            ->setSelect([
                'PRODUCT_ID',
            ])
            ->setFilter([
                '>CATALOG_PRICE.PRICE' => 0,
                'ORDER.USER_ID'        => $userId,
                '!ELEMENT.XML_ID' => '3%'
            ])
            ->setGroup(['PRODUCT_ID'])
            ->registerRuntimeField(
                new ExpressionField('CNT', 'COUNT(*)')
            )
            ->registerRuntimeField(
                new ReferenceField(
                    'ORDER',
                    OrderTable::class,
                    ['=this.ORDER_ID' => 'ref.ID'],
                    ['join_type' => 'INNER']
                )
            )
            ->registerRuntimeField(
                new ReferenceField(
                    'ELEMENT', ElementTable::class,
                    Join::on('this.PRODUCT_ID', 'ref.ID')
                        ->where('ref.ACTIVE', BitrixUtils::BX_BOOL_TRUE)
                        ->where('ref.IBLOCK_ID', $offersIblockId),
                    ['join_type' => 'INNER']
                )
            )
            ->registerRuntimeField(
                new ReferenceField(
                    'CATALOG_PRICE', PriceTable::class,
                    Join::on('this.PRODUCT_ID', 'ref.PRODUCT_ID')->where('ref.CATALOG_GROUP_ID', 2),
                    ['join_type' => 'INNER']
                )
            )
            ->setOrder(['CNT' => 'DESC'])
            ->setLimit($this->arParams['COUNT_ITEMS'])
            ->exec();

        $result = [];
        while ($offerId = $query->fetch()) {
            $result[] = $offerId['PRODUCT_ID'];
        }

        return $result;
    }

    /**
     * @param int[] $offerIds
     *
     * @return Offer[]
     */
    protected function getAllOffers(array $offerIds): array
    {
        $result = [];
        foreach ($offerIds as $offerId) {
            if ($offer = OfferQuery::getById($offerId)) {
                $result[$offer->getId()] = $offer;
            }
        }

        return $result;
    }

    /**
     * @param Offer[] $offers
     *
     * @return Product[]
     */
    protected function getAllProducts(array $offers): array
    {
        $result = [];
        foreach ($offers as $offer) {
            if ($product = ProductQuery::getById($offer->getCml2Link())) {
                $result[$offer->getId()] = $product;
            }
        }

        return $result;
    }
}
