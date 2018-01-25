<?php

namespace FourPaws\MobileApiBundle\Services;

use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\Context;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiRequestProcessor
{
    /**
     * @var ArrayTransformerInterface
     */
    private $arrayTransformer;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(ArrayTransformerInterface $arrayTransformer, ValidatorInterface $validator)
    {
        $this->arrayTransformer = $arrayTransformer;
        $this->validator = $validator;
    }

    /**
     * @param array $data
     * @param $class
     * @param null|Context $context
     *
     * @return object
     */
    public function convert(array $data, $class, Context $context = null)
    {
        return $this->arrayTransformer->fromArray(
            $data,
            $class,
            $context
        );
    }

    /**
     * @param object $data
     * @param null $constraint
     * @param null|array $groups
     *
     * @return \Symfony\Component\Validator\ConstraintViolationListInterface
     */
    public function validate($data, $constraint = null, array $groups = null)
    {
        return $this->validator->validate($data, $constraint, $groups);
    }
}
