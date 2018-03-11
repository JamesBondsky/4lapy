<?php

namespace FourPaws\Migrator;

use FourPaws\Migrator\Client\Action;
use FourPaws\Migrator\Client\ArticlePull;
use FourPaws\Migrator\Client\Catalog;
use FourPaws\Migrator\Client\CityPhone;
use FourPaws\Migrator\Client\News;
use FourPaws\Migrator\Client\SaleBasePull;
use FourPaws\Migrator\Client\SalePull;
use FourPaws\Migrator\Client\Saveable;
use FourPaws\Migrator\Client\Store;
use FourPaws\Migrator\Client\UserPull;
use FourPaws\Migrator\Entity\Action as ActionEntity;
use FourPaws\Migrator\Entity\Catalog as CatalogEntity;
use FourPaws\Migrator\Entity\CityPhone as CityPhoneEntity;
use FourPaws\Migrator\Entity\EntityInterface;
use FourPaws\Migrator\Entity\News as NewsEntity;
use FourPaws\Migrator\Entity\Store as StoreEntity;
use FourPaws\Migrator\Exception\MigratorException;
use FourPaws\Migrator\Provider\Action as ActionProvider;
use FourPaws\Migrator\Provider\Catalog as CatalogProvider;
use FourPaws\Migrator\Provider\CityPhone as CityPhoneProvider;
use FourPaws\Migrator\Provider\News as NewsProvider;
use FourPaws\Migrator\Provider\Store as StoreProvider;
use FourPaws\Migrator\Provider\StoreLocation as StoreLocationProvider;
use Symfony\Component\Console\Exception\InvalidArgumentException;

final class Factory
{
    const AVAILABLE_TYPES = [
        'action',
        'article',
        'catalog',
        'news',
        'city_phone',
        'sale',
        'sale_base',
        'store',
        'store_location',
        'user',
    ];
    
    /**
     * @param string $type
     * @param array  $options
     *
     * @return \FourPaws\Migrator\Client\Saveable
     *
     * @throws InvalidArgumentException
     * @throws \RuntimeException
     * @throws \FourPaws\Migrator\IblockNotFoundException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function getClient(string $type, array $options = []) : Saveable
    {
        $client = null;
        
        if (!in_array($type, self::AVAILABLE_TYPES, true)) {
            throw new InvalidArgumentException('Client must have a compatibility type, one of this: ' . implode(', ',
                                                                                                                self::AVAILABLE_TYPES));
        }
        
        switch ($type) {
            case 'action':
                $client = new Action(new ActionProvider(new ActionEntity(Action::ENTITY_NAME)), $options);
                break;
            case 'article':
                $client = new ArticlePull($options);
                break;
            case 'catalog':
                $client = new Catalog(new CatalogProvider(new CatalogEntity(Catalog::ENTITY_NAME)), $options);
                break;
            case 'news':
                $client = new News(new NewsProvider(new NewsEntity(News::ENTITY_NAME)), $options);
                break;
            case 'city_phone':
                $client = new CityPhone(new CityPhoneProvider(new CityPhoneEntity(CityPhone::ENTITY_NAME)), $options);
                break;
            case 'sale':
                $client = new SalePull($options);
                break;
            case 'sale_base':
                $client = new SaleBasePull($options);
                break;
            case 'store':
                $client = new Store(new StoreProvider(new StoreEntity(Store::ENTITY_NAME)), $options);
                break;
            case 'store_location':
                $client = new Store(new StoreLocationProvider(new StoreEntity(Store::ENTITY_NAME)), $options);
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
     *
     * @throws \FourPaws\Migrator\Exception\MigratorException
     */
    public function getEntityByEntityName(string $entityName) : EntityInterface
    {
        $entityNameParts = explode('_', $entityName);
        $entityNameParts = array_map(function ($part) {
            return ucfirst($part);
        },
            $entityNameParts);
        
        $entityName = implode('', $entityNameParts);
        
        $client = '\FourPaws\Migrator\Client\\' . $entityName;
        $entity = '\FourPaws\Migrator\Entity\\' . $entityName;
        
        if (!(class_exists($client) && class_exists($entity))) {
            throw new MigratorException(sprintf('Classes to entity %s is not found.', $entityName));
        }
        
        /**
         * @var \FourPaws\Migrator\Client\ClientInterface $client
         */
        return new $entity($client::ENTITY_NAME);
    }
}
