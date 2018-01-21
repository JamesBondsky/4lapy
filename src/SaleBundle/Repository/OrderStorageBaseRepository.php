<?php

namespace FourPaws\SaleBundle\Repository;

use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class OrderStorageBaseRepository implements OrderStorageRepositoryInterface
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }
}
