<?php

namespace FourPaws\PersonalBundle\Repository;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Application;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Type\DateTime;
use FourPaws\AppBundle\Repository\BaseHlRepository;
use FourPaws\PersonalBundle\Entity\CouponPoolCoupon;
use FourPaws\PersonalBundle\Exception\InvalidArgumentException;
use FourPaws\PersonalBundle\Exception\RuntimeException;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class CouponPoolRepository
 *
 * @package FourPaws\PersonalBundle\Repository
 */
class CouponPoolRepository extends BaseHlRepository
{
    public const HL_NAME = 'CouponPool';
    public const HL_TABLE_NAME = 'b_hlbd_coupon_pool';

    /** @var CouponPoolCoupon $entity */
    protected $entity;

    /**
     * CouponPoolRepository constructor.
     * @inheritdoc
     */
    public function __construct(
        ValidatorInterface $validator,
        ArrayTransformerInterface $arrayTransformer
    )
    {
        parent::__construct($validator, $arrayTransformer);
        $this->setEntityClass(CouponPoolCoupon::class);
    }

    /**
     * @param int $offerId
     * @param array $promoCodes
     * @throws \Bitrix\Main\ObjectException
     * @throws InvalidArgumentException
     */
    public function add(int $offerId, array $promoCodes)
    {
        if ($offerId <=0) {
            throw new InvalidArgumentException('$offerId <= 0. $offerId: ' . $offerId);
        }

        foreach ($promoCodes as $promoCode) {
            $coupon = (new CouponPoolCoupon())
                ->setPromoCode($promoCode)
                ->setOfferId($offerId)
                ->setDateCreated(new DateTime())
                ->setDateChanged(new DateTime());

            $this->setEntity($coupon)->create();
        }
    }

    /**
     * Выдает свободный промокод из пула купонов по id персонального предложения.
     * Выданный промокод отмечается, как забранный из пула.
     * @param $offerId
     * @return string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\Db\SqlQueryException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws RuntimeException
     */
    public function getFreePromoCode($offerId): string
    {
        $connection = Application::getConnection();

        $randNumber = random_int(2, 99999999);

        $sql = 'UPDATE ' . self::HL_TABLE_NAME . ' SET UF_TAKEN=' . $randNumber . ' WHERE UF_TAKEN=0 LIMIT 1';
        $updateResult = $connection->query($sql);

        if ($connection->getAffectedRowsCount()) {
            $result = $this->findBy([
                'filter' => ['=UF_TAKEN' => $randNumber],
                'select' => [
                    'ID',
                    'UF_PROMO_CODE',
                ],
                'limit' => 1,
            ]);

            /** @var CouponPoolCoupon $coupon */
            $coupon = $result->get(0);

            $logger = LoggerFactory::create('CouponPoolRepository', '20-20');
            $logger->info(__FUNCTION__ . '. Id: ' . print_r($coupon->getId(), true));

            $this->setEntity(
                $coupon
                    ->setTaken(1)
                    ->setDateCreated(new DateTime())
                    ->setDateChanged(new DateTime())
            )->update();


            return $coupon->getPromoCode();
        } else {
            throw new RuntimeException('Нет свободных купонов по акции ' . $offerId);
        }
    }
}
