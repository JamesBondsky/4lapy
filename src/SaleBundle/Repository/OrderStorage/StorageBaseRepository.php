<?php

namespace FourPaws\SaleBundle\Repository\OrderStorage;

use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class StorageBaseRepository implements StorageRepositoryInterface
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var ArrayTransformerInterface
     */
    protected $arrayTransformer;

    /**
     * @var CurrentUserProviderInterface
     */
    protected $currentUserProvider;

    public function __construct(
        ArrayTransformerInterface $arrayTransformer,
        ValidatorInterface $validator,
        CurrentUserProviderInterface $currentUserProviderInterface
    ) {
        $this->arrayTransformer = $arrayTransformer;
        $this->validator = $validator;
        $this->currentUserProvider = $currentUserProviderInterface;
    }
}
