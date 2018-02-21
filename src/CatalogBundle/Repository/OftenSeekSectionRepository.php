<?php

namespace FourPaws\CatalogBundle\Repository;

use Bitrix\Sale\SectionTable;
use FourPaws\AppBundle\Repository\BaseRepository;
use FourPaws\Catalog\Model\OftenSeekSection;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class OftenSeekSectionRepository
 *
 * @package FourPaws\CatalogBundle\Repository
 */
class OftenSeekSectionRepository extends BaseRepository
{
    /** @var OftenSeekSection $entity */
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
        $this->setDataManager(new SectionTable());
        $this->setEntityClass(OftenSeekSection::class);
    }
}
