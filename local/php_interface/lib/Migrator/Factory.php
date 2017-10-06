<?php

namespace FourPaws\Migrator;

use FourPaws\Migrator\Client\ArticlePull;
use FourPaws\Migrator\Client\SalePull;
use FourPaws\Migrator\Client\Saveable;
use FourPaws\Migrator\Client\ShopPull;
use FourPaws\Migrator\Entity\EntityInterface;
use FourPaws\Migrator\Entity\News as NewsEntity;
use FourPaws\Migrator\Entity\Catalog as CatalogEntity;
use FourPaws\Migrator\Client\News;
use FourPaws\Migrator\Client\Catalog;
use FourPaws\Migrator\Provider\News as NewsProvider;
use FourPaws\Migrator\Provider\Catalog as CatalogProvider;
use FourPaws\Migrator\Client\UserPull;
use Symfony\Component\Console\Exception\InvalidArgumentException;

final class Factory
{
    const AVAILABLE_TYPES = [
        'article',
        'catalog',
        'news',
        'sale',
        'shop',
        'user',
    ];
    
    /**
     * @param string $type
     * @param array  $options
     *
     * @return \FourPaws\Migrator\Client\Saveable
     */
    public function getClient(string $type, array $options = []) : Saveable
    {
        $client = null;
        
        if (!in_array($type, self::AVAILABLE_TYPES, true)) {
            throw new InvalidArgumentException('Client must have a compatibility type, one of this: ' . implode(', ',
                                                                                                                self::AVAILABLE_TYPES));
        }
        
        switch ($type) {
            case 'article':
                $client = new ArticlePull($options);
                break;
            case 'catalog':
                $entity = new CatalogEntity(Catalog::ENTITY_NAME);
                
                $client = new Catalog(new CatalogProvider(Catalog::ENTITY_NAME, $entity), $options);
                break;
            case 'news':
                $entity = new NewsEntity(News::ENTITY_NAME);
                
                $client = new News(new NewsProvider(News::ENTITY_NAME, $entity), $options);
                break;
            case 'sale':
                $client = new SalePull($options);
                break;
            case 'shop':
                $client = new ShopPull($options);
                break;
            case 'user':
                $client = new UserPull($options);
                break;
        }
        
        return $client;
    }
    
    /**
     * @param string $entityName
     *
     * @return \FourPaws\Migrator\Entity\EntityInterface
     * @throws \Exception
     */
    public function getEntityByEntityName(string $entityName) : EntityInterface
    {
        $entityNameParts = explode('_', $entityName);
        $entityNameParts = array_map(function($part) {
            return ucfirst($part);
        }, $entityNameParts);
        
        $entityName = implode('', $entityNameParts);
        
        $client = '\FourPaws\Migrator\Client\\' . $entityName;
        $entity = '\FourPaws\Migrator\Entity\\' . $entityName;
        
        if (!(class_exists($client) && class_exists($entity))) {
            throw new \Exception("Classes to entity {$entityName} is not found.");
        }
    
        /**
         * @var \FourPaws\Migrator\Client\ClientInterface $client
         */
        
        return new $entity($client::ENTITY_NAME);
    }
}