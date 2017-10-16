<?php

namespace FourPaws\Migrator\Client;

use FourPaws\Migrator\Entity\Delivery as DeliveryEntity;
use FourPaws\Migrator\Entity\OrderProperty as OrderPropertyEntity;
use FourPaws\Migrator\Entity\Status as StatusEntity;
use FourPaws\Migrator\Provider\Delivery as DeliveryProvider;
use FourPaws\Migrator\Provider\OrderProperty as OrderPropertyProvider;
use FourPaws\Migrator\Provider\Status as StatusProvider;

class SalePull extends ClientPullAbstract
{
    protected $limit;
    
    protected $force;
    
    public function getBaseClientList() : array
    {
        return [
            new Status(new StatusProvider(new StatusEntity(Status::ENTITY_NAME)), ['force' => true]),
            new Delivery(new DeliveryProvider(new DeliveryEntity(Delivery::ENTITY_NAME))),
            new OrderProperty(new OrderPropertyProvider(new OrderPropertyEntity(OrderProperty::ENTITY_NAME))),
        ];
    }
    
    public function getClientList() : array
    {
        return [
        
        ];
    }
}
