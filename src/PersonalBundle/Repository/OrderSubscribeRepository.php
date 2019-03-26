<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 25.03.2019
 * Time: 17:15
 */

namespace FourPaws\PersonalBundle\Repository;


use Bitrix\Main\Type\DateTime;
use FourPaws\AppBundle\Repository\BaseHlRepository;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrderSubscribeRepository extends BaseHlRepository
{
    public const HL_NAME = 'OrderSubscribe';

    /**@var UserService */
    public $curUserService;

    /** @var OrderSubscribe $entity */
    protected $entity;

    /**
     * PetRepository constructor.
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
        $this->setEntityClass(OrderSubscribe::class);
        $this->curUserService = $currentUserProvider;
    }

    /**
     * @return bool
     * @throws ServiceNotFoundException
     * @throws ValidationException
     * @throws NotAuthorizedException
     * @throws BitrixRuntimeException
     * @throws ServiceCircularReferenceException
     * @throws \Exception
     */
    public function create(): bool
    {
        if ($this->entity->getUserId() === 0) {
            try {
                $this->entity->setUserId($this->curUserService->getCurrentUserId());
            } catch (NotAuthorizedException $e) {
                return false;
            }
        }

        $this->entity->setDateCreate(new DateTime());

        return parent::create();
    }
}