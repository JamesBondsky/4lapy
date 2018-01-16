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
use FourPaws\UserBundle\Service\UserService;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\Exception\RuntimeException;
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
    
    /**
     * @var UserService
     */
    public $curUserService;
    
    /**
     * ReferralRepository constructor.
     *
     * @param ValidatorInterface $validator
     *
     * @throws RuntimeException
     * @throws ServiceNotFoundException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(
        ValidatorInterface $validator
    )
    {
        parent::__construct($validator);
        $this->curUserService = App::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
    }
    
    /**
     * @return bool
     * @throws ServiceNotFoundException
     * @throws ValidationException
     * @throws \Exception
     * @throws BitrixRuntimeException
     * @throws NotAuthorizedException
     */
    public function create() : bool
    {
        if ($this->entity->getUserId() === 0) {
            $this->entity->setUserId($this->curUserService->getCurrentUserId());
        }
        
        return parent::create();
    }
    
    /**
     * @return Referral[]|array
     * @throws InvalidIdentifierException
     * @throws ServiceNotFoundException
     * @throws \Exception
     * @throws NotAuthorizedException
     */
    public function findByCurUser() : array
    {
        
        $referrals = $this->findBy(
            [
                'filter' => ['UF_USER_ID' => $this->curUserService->getCurrentUserId()],
                'ttl'    => 360000,
            ]
        );
        
        return $referrals;
    }
    
    /**
     * @param array $params
     *
     * @return Referral[]|array
     * @throws \Exception
     */
    public function findBy(array $params = []) : array
    {
        if (empty($params['entityClass'])) {
            $params['entityClass'] = Referral::class;
        }
        
        return parent::findBy($params);
    }
}
