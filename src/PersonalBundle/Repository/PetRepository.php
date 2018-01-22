<?php

namespace FourPaws\PersonalBundle\Repository;

use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Repository\BaseHlRepository;
use FourPaws\PersonalBundle\Entity\Pet;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class PetRepository
 *
 * @package FourPaws\PersonalBundle\Repository
 */
class PetRepository extends BaseHlRepository
{
    const HL_NAME = 'Pet';
    
    /** @var Pet $entity */
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
            $curUserService = App::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
            $this->entity->setUserId($curUserService->getCurrentUserId());
        }
        
        return parent::create();
    }
    
    /**
     * @return Pet[]|array
     * @throws InvalidIdentifierException
     * @throws ServiceNotFoundException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     */
    public function findByCurUser() : array
    {
        $curUserService = App::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
        
        return $this->findBy(
            [
                'filter' => ['UF_USER_ID' => $curUserService->getCurrentUserId()],
            ]
        );
    }
    
    /**
     * @param array $params
     *
     * @return Pet[]|array
     * @throws \Exception
     */
    public function findBy(array $params = []) : array
    {
        if (empty($params['entityClass'])) {
            $params['entityClass'] = Pet::class;
        }
        
        return parent::findBy($params);
    }
}
