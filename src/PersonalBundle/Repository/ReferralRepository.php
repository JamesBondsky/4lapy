<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\Repository;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Application;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\AppBundle\Repository\BaseHlRepository;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\PersonalBundle\Entity\Referral;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ReferralRepository
 *
 * @package FourPaws\PersonalBundle\Repository
 */
class ReferralRepository extends BaseHlRepository
{
    public const HL_NAME = 'Referral';

    /**
     * @var UserService
     */
    public $curUserService;

    /** @var Referral $entity */
    protected $entity;

    /**
     * ReferralRepository constructor.
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
        $this->setEntityClass(Referral::class);
        $this->curUserService = $currentUserProvider;
    }

    /**
     * @throws ServiceNotFoundException
     * @throws ValidationException
     * @throws \Exception
     * @throws BitrixRuntimeException
     * @throws NotAuthorizedException
     * @return bool
     */
    public function create(): bool
    {
        if ($this->entity->getUserId() === 0) {
            $this->entity->setUserId($this->curUserService->getCurrentUserId());
        }

        return parent::create();
    }

    /**
     * @throws InvalidIdentifierException
     * @throws ServiceNotFoundException
     * @throws NotAuthorizedException
     * @return ArrayCollection|Referral[]
     * @throws ObjectPropertyException
     */
    public function findByCurUser(): ArrayCollection
    {
        $cacheTime = 360000;
        try {
            $instance = Application::getInstance();
        } catch (SystemException $e) {
            $logger = LoggerFactory::create('system');
            $logger->error('Ошибка получения инстанса'.$e->getMessage());
            return new ArrayCollection();
        }
        $curUserId = $this->curUserService->getCurrentUserId();
        $cache = $instance->getCache();
        $referrals = new ArrayCollection();

        if ($cache->initCache($cacheTime,
            serialize(['userId' => $curUserId]),
            __FUNCTION__)) {
            $result = $cache->getVars();
            $referrals = $result['referrals'];
        } elseif ($cache->startDataCache()) {
            $tagCache = null;
            if (\defined('BX_COMP_MANAGED_CACHE')) {
                $tagCache = $instance->getTaggedCache();
                $tagCache->startTagCache(__FUNCTION__);
            }

            $referrals = $this->findBy(
                [
                    'filter' => ['UF_USER_ID' => $this->curUserService->getCurrentUserId()],
                ]
            );

            if ($tagCache !== null) {
                TaggedCacheHelper::addManagedCacheTags([
                    'hlb:field:referral_user:'. $curUserId
                ], $tagCache);
                $tagCache->endTagCache();
            }

            $cache->endDataCache([
                'referrals' => $referrals,
            ]);
        }

        return $referrals;
    }
}
