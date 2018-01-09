<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\Service;

use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Exception\ContactUpdateException;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\PersonalBundle\Entity\Referral;
use FourPaws\PersonalBundle\Repository\ReferralRepository;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class ReferralService
 *
 * @package FourPaws\PersonalBundle\Service
 */
class ReferralService
{
    /**
     * @var ReferralRepository
     */
    private $referralRepository;
    
    /**
     * ReferralService constructor.
     *
     * @param ReferralRepository $referralRepository
     */
    public function __construct(ReferralRepository $referralRepository)
    {
        $this->referralRepository = $referralRepository;
    }
    
    /**
     * @throws InvalidIdentifierException
     * @throws ServiceNotFoundException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @throws NotAuthorizedException
     * @throws ServiceCircularReferenceException
     * @return array
     */
    public function getCurUserReferrals() : array
    {
        return $this->referralRepository->findByCurUser();
    }
    
    /**
     * @param array $data
     *
     * @return bool
     * @throws ServiceNotFoundException
     * @throws InvalidIdentifierException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws NotAuthorizedException
     * @throws ConstraintDefinitionException
     * @throws ContactUpdateException
     * @throws ManzanaServiceException
     * @throws ApplicationCreateException
     * @throws ValidationException
     * @throws \Exception
     * @throws BitrixRuntimeException
     */
    public function add(array $data) : bool
    {
        $res = $this->referralRepository->setEntityFromData($data, Referral::class)->create();
        if($res) {
            $this->updateManzanaReferral();
        }
        return $res;
    }
    
    /**
     * @param Client $client
     * @param array  $types
     */
    public function setClientReferral(&$client, array $types)
    {
        /** @todo set actual types*/
        $baseTypes        =
            [
                'bird',
                'cat',
                'dog',
                'fish',
                'rodent',
            ];
        $client->ffBird   = \in_array('bird', $types, true) ? 1 : 0;
        $client->ffCat    = \in_array('cat', $types, true) ? 1 : 0;
        $client->ffDog    = \in_array('dog', $types, true) ? 1 : 0;
        $client->ffFish   = \in_array('fish', $types, true) ? 1 : 0;
        $client->ffRodent = \in_array('rodent', $types, true) ? 1 : 0;
        $others           = 0;
        if (\is_array($types) && !empty($types)) {
            foreach ($types as $type) {
                if (!\in_array($type, $baseTypes, true)) {
                    $others = 1;
                    break;
                }
            }
            
        }
        $client->ffOthers = $others;
    }
}
