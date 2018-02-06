<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\ParamConverter;

use FourPaws\MobileApiBundle\Dto\Request\GetRequest;
use FourPaws\MobileApiBundle\Dto\Request\PostRequest;
use FourPaws\MobileApiBundle\Dto\Request\SimpleUnserializeRequest;
use FourPaws\MobileApiBundle\Exception\SystemException;
use FourPaws\MobileApiBundle\Exception\ValidationException;
use JMS\Serializer\ArrayTransformerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SimpleUnserializeRequestConverter implements ParamConverterInterface
{
    const VALIDATION_ATTRIBUTE = 'validationErrors';

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
     * Stores the object in the request.
     *
     * @param Request        $request
     * @param ParamConverter $configuration Contains the name, class and options of the object
     *
     * @throws SystemException
     * @throws ValidationException
     * @return bool True if the object has been successfully set, else false
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        if (!$configuration->getClass()) {
            return false;
        }

        $request->attributes->has($configuration->getName());

        $params = [];
        if (is_a($configuration->getClass(), GetRequest::class, true)) {
            $params = array_merge($params, $request->query->all());
        }
        if (is_a($configuration->getClass(), PostRequest::class, true)) {
            $params = array_merge($params, $request->request->all());
        }

        $object = $this->arrayTransformer->fromArray($params, $configuration->getClass());
        if (!$object) {
            throw new SystemException('Cant converte request to object');
        }

        $validationResult = $this->validator->validate($object);
        if ($validationResult->count() > 0) {
            if ($configuration->getOptions()['throw_validation_exception'] ?? false) {
                throw new ValidationException('Cant converte request to object');
            }
        }

        $request->attributes->set(static::VALIDATION_ATTRIBUTE, $validationResult);
        $request->attributes->set($configuration->getName(), $object);

        return true;
    }

    /**
     * Checks if the object is supported.
     *
     * @param ParamConverter $configuration
     *
     * @return bool True if the object is supported, else false
     */
    public function supports(ParamConverter $configuration)
    {
        return
            $configuration->getClass()
            && is_a($configuration->getClass(), SimpleUnserializeRequest::class, true);
    }
}
