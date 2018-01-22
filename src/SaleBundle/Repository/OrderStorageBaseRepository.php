<?php

namespace FourPaws\SaleBundle\Repository;

use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class OrderStorageBaseRepository implements OrderStorageRepositoryInterface
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var ArrayTransformerInterface
     */
    protected $arrayTransformer;

    public function __construct(ArrayTransformerInterface $arrayTransformer, ValidatorInterface $validator)
    {
        $this->arrayTransformer = $arrayTransformer;
        $this->validator = $validator;
    }
}
