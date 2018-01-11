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
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

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
        
        $referrals = $this->findBy(
            [
                'filter' => ['UF_USER_ID' => $curUserService->getCurrentUserId()],
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
