<?php

namespace FourPaws\PersonalBundle\Repository;

use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Repository\BaseHlRepository;
use FourPaws\PersonalBundle\Entity\Referral;
use FourPaws\External\Manzana\Model\Referral as ManzaReferal;
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
        
        $arCards = [];
        if(\is_array($referals) && !empty($referals)) {
            /** @var Referral $item */
            foreach ($referals as $key => $item) {
                $arCards[$item->getCard()] = $key;
            }
        }
        
        $manzanaReferrals = $this->manzanaService->getUserReferralList($curUserService->getCurrentUser());
        if(\is_array($manzanaReferrals) && !empty($manzanaReferrals)) {
            /** @var ManzaReferal $item */
            foreach ($manzanaReferrals as $item) {
                if(!array_key_exists($item->cardNumber, $arCards)){
                
                }
                else{
                    /** @var Referral $referral */
                    $referral =& $referals[$arCards[$item->cardNumber]];
                    $referral->setBonus((float)$item->sumReferralBonus);
                    $referral->setModerate($item->isQuestionnaireActual === 20000);
                }
            }
    
        }
        
        return $referals;
    }
}
