<?php

namespace FourPaws\Migrator;

use FourPaws\Migrator\Client\Article;
use FourPaws\Migrator\Client\SalePull;
use FourPaws\Migrator\Client\Saveable;
use FourPaws\Migrator\Client\ShopPull;
use FourPaws\Migrator\Entity\News as NewsEntity;
use FourPaws\Migrator\Entity\Article as ArticleEntity;
use FourPaws\Migrator\Provider\Article as ArticlesProvider;
use FourPaws\Migrator\Client\News;
use FourPaws\Migrator\Provider\News as NewsProvider;
use FourPaws\Migrator\Client\UserPull;
use Symfony\Component\Console\Exception\InvalidArgumentException;

final class Factory
{
    const AVAILABLE_TYPES = [
        'user',
        'news',
        'article',
        'shop',
        'sale',
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
        
        if (!in_array($type, self::AVAILABLE_TYPES)) {
            throw new InvalidArgumentException('Client must have a compatibility type, one of this: ' . implode(', ',
                                                                                                                self::AVAILABLE_TYPES));
        }
        
        switch ($type) {
            case 'user':
                $client = new UserPull($options);
                break;
            case 'news':
                $entity = new NewsEntity(News::ENTITY_NAME, Utils::getIblockId('publications', 'news'));
                
                $client = new News(new NewsProvider(News::ENTITY_NAME, $entity), $options);
                break;
            case 'article':
                $entity = new ArticleEntity(Article::ENTITY_NAME, Utils::getIblockId('publications', 'articles'));
                
                $client = new Article(new ArticlesProvider(Article::ENTITY_NAME, $entity), $options);
                break;
            case 'shop':
                $client = new ShopPull($options);
                break;
            case 'sale':
                $client = new SalePull($options);
                break;
        }
        
        return $client;
    }
}