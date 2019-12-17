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
use FourPaws\PersonalBundle\Entity\OrderSubscribeSingle;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


/**
 * Class OrderSubscribeSingleDeliveryRepository
 *
 * @package FourPaws\PersonalBundle\Repository
 */
class OrderSubscribeSingleRepository extends BaseHlRepository
{
    public const HL_NAME = 'OrderSubscribeSingle';

    /** @var OrderSubscribeSingle $entity */
    protected $entity;

    /**
     * OrderSubscribeSingleDeliveryRepository constructor.
     * @inheritdoc
     */
    public function __construct(
        ValidatorInterface $validator,
        ArrayTransformerInterface $arrayTransformer
    ) {
        parent::__construct($validator, $arrayTransformer);
        $this->setEntityClass(OrderSubscribeSingle::class);
    }

    /**
     * @param $subscribeId
     * @return ArrayCollection
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function findBySubscribe($subscribeId, bool $onlyActive = true): ArrayCollection
    {
        $filter = [
            'UF_SUBSCRIBE_ID' => $subscribeId,
        ];

        if($onlyActive){
            $filter['UF_ACTIVE'] = 1;
        }

        return $this->findBy(
            [
                'filter' => $filter
            ]
        );
    }
}