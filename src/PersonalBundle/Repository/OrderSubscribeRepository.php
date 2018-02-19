<?php

namespace FourPaws\PersonalBundle\Repository;

use FourPaws\AppBundle\Repository\BaseHlRepository;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class OrderSubscribeRepository
 *
 * @package FourPaws\PersonalBundle\Repository
 */
class OrderSubscribeRepository extends BaseHlRepository
{
    const HL_NAME = 'OrderSubscribe';

    /** @var OrderSubscribe $entity */
    protected $entity;

    /**
     * ReferralRepository constructor.
     *
     * @inheritdoc
     */
    public function __construct(
        ValidatorInterface $validator,
        ArrayTransformerInterface $arrayTransformer
    ) {
        parent::__construct($validator, $arrayTransformer);
        $this->setEntityClass(OrderSubscribe::class);
    }
}
