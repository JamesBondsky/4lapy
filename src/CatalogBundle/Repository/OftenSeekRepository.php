<?php

namespace FourPaws\CatalogBundle\Repository;

use Bitrix\Iblock\ElementTable;
use FourPaws\AppBundle\Repository\BaseRepository;
use FourPaws\CatalogBundle\Service\OftenSeek;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class OftenSeekRepository
 *
 * @package FourPaws\CatalogBundle\Repository
 */
class OftenSeekRepository extends BaseRepository
{
    /** @var OftenSeek $entity */
    protected $entity;

    /**
     * OrderRepository constructor.
     *
     * @inheritdoc
     *
     * @param CurrentUserProviderInterface $currentUserProvider
     */
    public function __construct(
        ValidatorInterface $validator,
        ArrayTransformerInterface $arrayTransformer,
        CurrentUserProviderInterface $currentUserProvider
    ) {
        parent::__construct($validator, $arrayTransformer);
        $this->setDataManager(new ElementTable());
        $this->setEntityClass(OftenSeek::class);
    }
}
