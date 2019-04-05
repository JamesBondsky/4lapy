<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 25.03.2019
 * Time: 17:59
 */

namespace FourPaws\PersonalBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\AppBundle\Repository\BaseHlRepository;
use FourPaws\PersonalBundle\Entity\OrderSubscribeItem;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrderSubscribeItemRepository extends BaseHlRepository
{
    public const HL_NAME = 'OrderSubscribeItems';

    /**@var UserService */
    public $curUserService;

    /** @var OrderSubscribeItem $entity */
    protected $entity;

    /**
     * OrderSubscribeItemRepository constructor.
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
        $this->setEntityClass(OrderSubscribeItem::class);
        $this->curUserService = $currentUserProvider;
    }

    /**
     * @param $subscribeId
     * @return ArrayCollection
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function findBySubscribe($subscribeId): ArrayCollection
    {
        return $this->findBy(
            [
                'filter' => ['UF_SUBSCRIBE_ID' => $subscribeId],
            ]
        );
    }
}