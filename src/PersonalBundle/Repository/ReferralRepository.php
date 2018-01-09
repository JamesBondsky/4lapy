<?php

namespace FourPaws\PersonalBundle\Repository;

use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Repository\BaseHlRepository;
use FourPaws\PersonalBundle\Entity\Referral;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ReferralRepository
 *
 * @package FourPaws\PersonalBundle\Repository
 */
class ReferralRepository extends BaseHlRepository
{
    const HL_NAME = 'Referral';
    
    /** @var Referral $entity */
    protected $entity;
    
    private   $manzanaService;
    
    /**
     * ReferralRepository constructor.
     *
     * @param ArrayTransformerInterface $arrayTransformer
     * @param ValidatorInterface        $validator
     *
     * @throws \Exception
     */
    public function __construct(
        ArrayTransformerInterface $arrayTransformer,
        ValidatorInterface $validator
    )
    {
        parent::__construct($arrayTransformer, $validator);
        $this->manzanaService = App::getInstance()->getContainer()->get('manzana.service');
    }
    
    /**
     * @return bool
     * @throws ServiceNotFoundException
     * @throws ValidationException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @throws BitrixRuntimeException
     * @throws NotAuthorizedException
     * @throws ServiceCircularReferenceException
     */
    public function create() : bool
    {
        if ($this->entity->getUserId() === 0) {
            $curUserService = App::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
            $this->entity->setUserId($curUserService->getCurrentUserId());
        }
        
        return parent::create();
    }
    
    /**
     * @return Referral[]|array
     * @throws InvalidIdentifierException
     * @throws ServiceNotFoundException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @throws NotAuthorizedException
     * @throws ServiceCircularReferenceException
     */
    public function findByCurUser() : array
    {
        $curUserService = App::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
        
        $referals = $this->findBy(
            [
                'filter' => ['UF_USER_ID' => $curUserService->getCurrentUserId()],
                'entityClass' => Referral::class,
            ]
        );
        
        if(!empty($referals)) {
            $this->manzanaService->getUserReferralList($curUserService->getCurrentUser());
        }
        
        return $referals;
    }
}
