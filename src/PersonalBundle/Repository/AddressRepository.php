<?php

namespace FourPaws\PersonalBundle\Repository;

use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Repository\BaseHlRepository;
use FourPaws\PersonalBundle\Entity\Address;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class AddressRepository
 *
 * @package FourPaws\PersonalBundle\Repository
 */
class AddressRepository extends BaseHlRepository
{
    const HL_NAME = 'Address';
    
    /** @var Address $entity */
    protected $entity;
    
    /**
     * @return bool
     * @throws ServiceNotFoundException
     * @throws ValidationException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @throws BitrixRuntimeException
     * @throws ServiceCircularReferenceException
     */
    public function create() : bool
    {
        if ($this->entity->getUserId() === 0) {
            $this->entity->setUserId(
                App::getInstance()->getContainer()->get(CurrentUserProviderInterface::class)->getCurrentUserId()
            );
        }
        
        return parent::create();
    }
    
    /**
     * @param array $params
     *
     * @return Address[]|array
     * @throws \Exception
     */
    public function findBy(array $params = []) : array
    {
        if(empty($params['entityClass'])){
            $params['entityClass'] = Address::class;
        }
        return parent::findBy($params);
    }
    
    /**
     * @param int $userId
     *
     * @return array
     * @throws ServiceNotFoundException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     */
    public function findByCurUser(int $userId = 0) : array
    {
        if ($userId === 0) {
            $userId = App::getInstance()->getContainer()->get(CurrentUserProviderInterface::class)->getCurrentUserId();
        }
        
        return $this->findBy(['filter' => ['UF_USER_ID' => $userId]]);
    }
}
