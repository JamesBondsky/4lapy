<?php

namespace FourPaws\Migrator;

use FourPaws\Migrator\Client\Articles;
use FourPaws\Migrator\Client\SalePull;
use FourPaws\Migrator\Client\Saveable;
use FourPaws\Migrator\Client\ShopPull;
use FourPaws\Migrator\Entity\NewsEntity;
use FourPaws\Migrator\Provider\Articles as ArticlesProvider;
use FourPaws\Migrator\Client\News;
use FourPaws\Migrator\Provider\News as NewsProvider;
use FourPaws\Migrator\Client\UserPull;
use Symfony\Component\Console\Exception\InvalidArgumentException;

final class Factory
{
    const AVAILABLE_TYPES = [
        'user',
        'news',
        'articles',
        'shops',
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
        
        /**
         * @todo move to configuration
         */
        $iblockIdList = [
            'news'     => 0,
            'articles' => 0,
        ];

        switch ($type) {
            case 'user':
                $client = new UserPull($options);
                break;
            case 'news':
                $client =
                    new News(new NewsProvider(News::ENTITY_NAME, new NewsEntity(News::ENTITY_NAME, $iblockIdList['news'])), $options);
                break;
            case 'articles':
                $client = new Articles(new ArticlesProvider(Articles::ENTITY_NAME, $iblockIdList['articles']), $options);
                break;
            case 'shops':
                $client = new ShopPull($options);
                break;
            case 'sale':
                $client = new SalePull($options);
                break;
        }
        
        return $client;
    }
}