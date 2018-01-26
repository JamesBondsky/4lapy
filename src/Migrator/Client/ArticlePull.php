<?php

namespace FourPaws\Migrator\Client;

use FourPaws\Migrator\Entity\Article as ArticleEntity;
use FourPaws\Migrator\Entity\ArticleSection as ArticleSectionEntity;
use FourPaws\Migrator\Provider\Article as ArticleProvider;
use FourPaws\Migrator\Provider\ArticleSection as ArticleSectionProvider;

/**
 * Class ArticlePull
 *
 * @package FourPaws\Migrator\Client
 */
class ArticlePull extends ClientPullAbstract
{
    /**
     * @return \FourPaws\Migrator\Client\ClientInterface[] array
     *
     * @throws \FourPaws\Migrator\IblockNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \RuntimeException
     */
    public function getBaseClientList() : array
    {
        return [
            new ArticleSection(new ArticleSectionProvider(new ArticleSectionEntity(ArticleSection::ENTITY_NAME)),
                               ['force' => true]),
        ];
    }
    
    /**
     * @return \FourPaws\Migrator\Client\ClientInterface[] array
     *
     * @throws \FourPaws\Migrator\IblockNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \RuntimeException
     */
    public function getClientList() : array
    {
        return [
            new Article(new ArticleProvider(new ArticleEntity(Article::ENTITY_NAME)), [
                'limit' => $this->limit,
                'force' => $this->force,
            ]),
        ];
    }
    
}
