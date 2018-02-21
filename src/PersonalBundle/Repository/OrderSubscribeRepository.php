<?php

namespace FourPaws\PersonalBundle\Repository;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Entity\Base;
use FourPaws\AppBundle\Repository\BaseHlRepository;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class OrderSubscribeRepository
 *
 * @package FourPaws\PersonalBundle\Repository
 */
class OrderSubscribeRepository extends BaseHlRepository
{
    const HL_NAME = 'OrderSubscribe';

    /** @var OrderSubscribe $entity */
    protected $entity;

    /**
     * ReferralRepository constructor.
     *
     * @inheritdoc
     */
    public function __construct(
        ValidatorInterface $validator,
        ArrayTransformerInterface $arrayTransformer
    ) {
        parent::__construct($validator, $arrayTransformer);
        $this->setEntityClass(OrderSubscribe::class);
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    protected function getHlBlock(): array
    {
        static $hlBlock = [];
        if (!$hlBlock) {
            $hlBlock = HighloadBlockTable::getList(
                array(
                    'filter' => array(
                        '=NAME' => static::HL_NAME
                    )
                )
            )->fetch();
            if(!$hlBlock) {
                throw new \Exception('Highloadblock '.static::HL_NAME.' not found');
            }
        }

        return $hlBlock;
    }

    /**
     * @return int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function getHlBlockId(): int
    {
        return $this->getHlBlock()['ID'];
    }

    /**
     * @return string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function getHlBlockTableName(): string
    {
        return $this->getHlBlock()['TABLE_NAME'];
    }

    /**
     * @return Base
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function getHlBlockEntity(): Base
    {
        static $hlEntity = null;
        if (!$hlEntity) {
            $hlEntity = HighloadBlockTable::compileEntity($this->getHlBlock());
        }

        return $hlEntity;
    }

    /**
     * @return string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function getHlBlockEntityClass(): string
    {
        static $hlEntityClass = '';
        if (!$hlEntityClass) {
            $hlEntityClass = $this->getHlBlockEntity()->getDataClass();
        }

        return $hlEntityClass;
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function getHlBlockEntityFields(): array
    {
        static $hlEntityFields = [];
        if (!$hlEntityFields) {
            $hlEntityFields = $GLOBALS['USER_FIELD_MANAGER']->GetUserFields('HLBLOCK_'.$this->getHlBlockId());
        }

        return $hlEntityFields;
    }
}
