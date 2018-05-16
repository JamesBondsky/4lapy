<?php

namespace FourPaws\Migrator\Client;

use Bitrix\Main\LoaderException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Migrator\Entity\Catalog as CatalogEntity;
use FourPaws\Migrator\Entity\Delivery as DeliveryEntity;
use FourPaws\Migrator\Entity\Order as OrderEntity;
use FourPaws\Migrator\Entity\OrderProperty as OrderPropertyEntity;
use FourPaws\Migrator\Entity\Status as StatusEntity;
use FourPaws\Migrator\IblockNotFoundException;
use FourPaws\Migrator\Provider\Catalog as CatalogProvider;
use FourPaws\Migrator\Provider\Delivery as DeliveryProvider;
use FourPaws\Migrator\Provider\Order as OrderProvider;
use FourPaws\Migrator\Provider\OrderProperty as OrderPropertyProvider;
use FourPaws\Migrator\Provider\Status as StatusProvider;
use RuntimeException;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * Class SalePull
 *
 * @package FourPaws\Migrator\Client
 */
class SalePull extends ClientPullAbstract
{
    /**
     * @return ClientInterface[] array
     *
     * @throws IblockNotFoundException
     * @throws LoaderException
     * @throws InvalidArgumentException
     * @throws ApplicationCreateException
     * @throws RuntimeException
     */
    public function getBaseClientList(): array
    {
        return [
            new Status(new StatusProvider(new StatusEntity(Status::ENTITY_NAME)), ['force' => true]),
            new Delivery(new DeliveryProvider(new DeliveryEntity(Delivery::ENTITY_NAME))),
            new OrderProperty(new OrderPropertyProvider(new OrderPropertyEntity(OrderProperty::ENTITY_NAME))),
            new Catalog(new CatalogProvider(new CatalogEntity(Catalog::ENTITY_NAME)), ['force' => true]),
            new UserPull(['force' => true]),
        ];
    }

    /**
     * @return array
     *
     * @throws LoaderException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws ApplicationCreateException
     */
    public function getClientList(): array
    {
        return [
            new Order(new OrderProvider(new OrderEntity(Order::ENTITY_NAME)), [
                'limit' => $this->limit,
                'force' => $this->force,
            ]),
        ];
    }
}
