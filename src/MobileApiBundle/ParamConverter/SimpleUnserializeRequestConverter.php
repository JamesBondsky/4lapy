<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\ParamConverter;

use FourPaws\MobileApiBundle\Dto\Request\CreateRequest;
use FourPaws\MobileApiBundle\Dto\Request\GetRequest;
use FourPaws\MobileApiBundle\Dto\Request\PostRequest;
use FourPaws\MobileApiBundle\Dto\Request\PutRequest;
use FourPaws\MobileApiBundle\Dto\Request\SimpleUnserializeRequest;
use FourPaws\MobileApiBundle\Dto\Request\UpdateRequest;
use FourPaws\MobileApiBundle\Exception\SystemException;
use FourPaws\MobileApiBundle\Exception\ValidationException;
use FourPaws\MobileApiBundle\Services\ErrorsFormatterService;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\DeserializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SimpleUnserializeRequestConverter implements ParamConverterInterface
{
    public const SYMFONY_ERRORS = 'symfonyErrors';
    public const API_ERRORS = 'apiErrors';

    /**
     * @var ArrayTransformerInterface
     */
    private $arrayTransformer;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var ErrorsFormatterService
     */
    private $errorsFormatterService;

    public function __construct(
        ArrayTransformerInterface $arrayTransformer,
        ValidatorInterface $validator,
        ErrorsFormatterService $errorsFormatterService
    ) {
        $this->arrayTransformer = $arrayTransformer;
        $this->validator = $validator;
        $this->errorsFormatterService = $errorsFormatterService;
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


        $groups = $this->getGroups($configuration);
        $params = $this->getParams($request, $configuration);
        $object = $this->convertToObject($configuration, $params, $groups);

        $this->processValidation($request, $configuration, $object, $groups);

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
    public function supports(ParamConverter $configuration): bool
    {
        return
            $configuration->getClass()
            && is_a($configuration->getClass(), SimpleUnserializeRequest::class, true);
    }

    /**
     * @param Request        $request
     * @param ParamConverter $configuration
     * @return array
     */
    protected function getParams(Request $request, ParamConverter $configuration): array
    {
        $params = [];
        if (is_a($configuration->getClass(), GetRequest::class, true)) {
            $params = array_merge($params, $request->query->all());
        }
        if (
            is_a($configuration->getClass(), PostRequest::class, true)
            || is_a($configuration->getClass(), PutRequest::class, true)
        ) {
            $params = array_merge($params, $request->request->all());
        }
        return $params;
    }

    /**
     * @param ParamConverter $configuration
     * @return array
     */
    protected function getGroups(ParamConverter $configuration): array
    {
        $groups = ['Default'];
        if (is_a($configuration->getClass(), CreateRequest::class, true)) {
            $groups[] = 'create';
        }
        if (is_a($configuration->getClass(), UpdateRequest::class, true)) {
            $groups[] = 'update';
        }
        return $groups;
    }

    /**
     * @param Request        $request
     * @param ParamConverter $configuration
     * @param                $object
     * @param                $groups
     * @throws ValidationException
     */
    protected function processValidation(Request $request, ParamConverter $configuration, $object, $groups): void
    {
        $notThrowException = $configuration->getOptions()['not_throw_exception'] ?? false;

        $validationResult = $this->validator->validate($object, null, $groups);
        if (!$notThrowException && $validationResult->count() > 0) {
            $validationError = $validationResult[0];
            if ($validationError instanceof ConstraintViolation) {
                if ($validationError->getConstraint() instanceof NotBlank) {
                    $message = $validationError->getPropertyPath() . ' не может быть пустым';
                } else {
                    $message = $validationError->getMessage();
                    if ($propertyPath = $validationError->getPropertyPath()) {
                        $message = 'Некорректное значение параметра ' . $propertyPath . ': ' . $message;
                    }
                }
                throw new ValidationException($message);
            } else {
                throw new ValidationException($validationResult);
            }
        }

        if (!$request->attributes->has(static::API_ERRORS)) {
            $request->attributes->set(
                static::API_ERRORS,
                $this->errorsFormatterService->covertList($validationResult)
            );
        }

        if (!$request->attributes->has(static::SYMFONY_ERRORS)) {
            $request->attributes->set(static::SYMFONY_ERRORS, $validationResult);
        }
    }

    /**
     * @param ParamConverter $configuration
     * @param                $params
     * @param                $groups
     * @throws \FourPaws\MobileApiBundle\Exception\SystemException
     * @return mixed
     */
    protected function convertToObject(ParamConverter $configuration, $params, $groups)
    {
        $object = $this->arrayTransformer->fromArray(
            $params,
            $configuration->getClass(),
            DeserializationContext::create()->setGroups($groups)
        );
        if (!$object) {
            throw new SystemException('Cant convert request to object');
        }
        return $object;
    }
}
