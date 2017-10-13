<?php

namespace FourPaws\Migrator\Client;

use FourPaws\Migrator\Entity\Status as StatusEntity;
use FourPaws\Migrator\Provider\Status as StatusProvider;

class SalePull extends ClientPullAbstract
{
    protected $limit;
    
    protected $force;
    
    public function getBaseClientList() : array
    {
        return [
            new Status(new StatusProvider(Status::ENTITY_NAME, new StatusEntity(Status::ENTITY_NAME)),
                       ['force' => true]),
        ];
    }
    
    public function getClientList() : array
    {
        return [
        
        ];
    }
}
