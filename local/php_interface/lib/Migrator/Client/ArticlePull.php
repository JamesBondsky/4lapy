<?php

namespace FourPaws\Migrator\Client;

use FourPaws\Migrator\Entity\ArticleSection as ArticleSectionEntity;
use FourPaws\Migrator\Entity\Article as ArticleEntity;
use FourPaws\Migrator\Provider\ArticleSection as ArticleSectionProvider;
use FourPaws\Migrator\Provider\Article as ArticleProvider;

/**
 * Class ArticlePull
 *
 * @package FourPaws\Migrator\Client
 */
class ArticlePull extends ClientPullAbstract
{
    /**
     * @return \FourPaws\Migrator\Client\ClientInterface[] array
     */
    public function getBaseClientList() : array
    {
        $entity = new ArticleSectionEntity(ArticleSection::ENTITY_NAME);

        return [
            new ArticleSection(new ArticleSectionProvider(ArticleSection::ENTITY_NAME, $entity),
                               ['force' => $this->force]),
        ];
    }
    
    /**
     * @return \FourPaws\Migrator\Client\ClientInterface[] array
     */
    public function getClientList() : array
    {
        $entity = new ArticleEntity(Article::ENTITY_NAME);

        return [
            new Article(new ArticleProvider(Article::ENTITY_NAME, $entity), [
                'limit' => $this->limit,
                'force' => $this->force,
            ]),
        ];
    }
    
}