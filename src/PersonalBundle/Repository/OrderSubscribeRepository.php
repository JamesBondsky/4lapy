<?php

namespace FourPaws\PersonalBundle\Repository;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\Entity\DeleteResult;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\UpdateResult;
use Bitrix\Main\Error;
use Bitrix\Sale\Internals\OrderTable;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\AppBundle\Repository\BaseHlRepository;
use FourPaws\AppBundle\Repository\BaseRepository;
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

    /**
     * @param array $params
     * @return ArrayCollection
     * @throws \Exception
     */
    public function findByParams(array $params): ArrayCollection
    {
        if (empty($params['setKey'])) {
            //$params['setKey'] = 'UF_ORDER_ID';
            $params['setKey'] = 'ID';
        }

        $collect = $this->findBy($params);

        return $collect;
    }

    /**
     * @param int|array $orderId
     * @param array $params
     * @return ArrayCollection
     * @throws \Exception
     */
    public function findByOrder($orderId, array $params = []): ArrayCollection
    {
        if (empty($params['order'])) {
            $params['order'] = [
                'ID' => 'ASC'
            ];
        }
        $params['filter']['=UF_ORDER_ID'] = $orderId;

        return $this->findByParams($params);
    }

    /**
     * @param int|array $userId
     * @param array $params
     * @return ArrayCollection
     * @throws \Exception
     */
    public function findByUser($userId, array $params = []): ArrayCollection
    {
        $params['runtime'] = $params['runtime'] ?? [];
        $params['runtime'][] = new ReferenceField(
            'ORDER',
            OrderTable::class,
            [
                '=this.UF_ORDER_ID' => 'ref.ID'
            ]
        );

        if (empty($params['order'])) {
            $params['order'] = [
                'UF_DATE_CREATE' => 'DESC',
                'ID' => 'DESC',
            ];
        }
        $params['filter']['=ORDER.USER_ID'] = $userId;

        return $this->findByParams($params);
    }

    /**
     * @return bool
     * @throws ArgumentNullException
     * @throws \Exception
     */
    public function create(): bool
    {
        if ((int)$this->entity->getOrderId() <= 0) {
            throw new ArgumentNullException('Order id not defined');
        }

        // явная установка значения - по умолчанию может быть null
        if (!$this->entity->isActive()) {
            $this->entity->setActive(false);
        }

        // дата создания всегда текущая
        $this->entity->setDateCreate('');
        // дата обновления всегда текущая
        $this->entity->setDateEdit('');

        if (!$this->entity->getDateStart()) {
            throw new ArgumentNullException('Start date not defined');
        }

        if (!$this->entity->getDeliveryFrequency()) {
            throw new ArgumentNullException('Delivery frequency not defined');
        }

        return parent::create();
    }

    /**
     * @param array $data
     * @return AddResult
     */
    public function createEx(array $data): AddResult
    {
        $result = new AddResult();

        try {
            $this->setEntity(
                $this->dataToEntity($data, $this->getEntityClass(), 'create')
            );
        } catch(\Exception $exception) {
            $result->addError(
                new Error($exception->getMessage(), 'setEntityException')
            );
        }

        if ($result->isSuccess()) {
            try {
                $res = $this->create();
                if ($res) {
                    $result->setId($this->entity->getId());
                }
            } catch(ArgumentNullException $exception) {
                $result->addError(
                    new Error($exception->getMessage(), 'argumentNullException')
                );
            } catch(\Exception $exception) {
                $result->addError(
                    new Error($exception->getMessage(), $exception->getCode())
                );
            }
        }

        return $result;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function update(): bool
    {
        // явная установка значения - по умолчанию может быть null
        if (!$this->entity->isActive()) {
            $this->entity->setActive(false);
        }

        // дата создания не обновляется
        $this->entity->setDateCreate(null);
        // дата обновления всегда текущая
        $this->entity->setDateEdit('');

        return parent::update();
    }

    /**
     * @param array $data
     * @return UpdateResult
     */
    public function updateEx(array $data): UpdateResult
    {
        $result = new UpdateResult();

        try {
            $this->setEntity(
                $this->dataToEntity($data, $this->getEntityClass(), 'update')
            );
        } catch(\Exception $exception) {
            $result->addError(
                new Error($exception->getMessage(), 'setEntityException')
            );
        }

        if ($result->isSuccess()) {
            try {
                $res = $this->update();
            } catch(ArgumentNullException $exception) {
                $result->addError(
                    new Error($exception->getMessage(), 'argumentNullException')
                );
            } catch(\Exception $exception) {
                $result->addError(
                    new Error($exception->getMessage(), $exception->getCode())
                );
            }
        }

        return $result;
    }

    /**
     * @param int $id
     * @return DeleteResult
     */
    public function deleteEx(int $id): DeleteResult
    {
        $result = new DeleteResult();
        if ($result->isSuccess()) {
            try {
                $this->delete($id);
            } catch(\Exception $exception) {
                $result->addError(
                    new Error($exception->getMessage(), $exception->getCode())
                );
            }
        }

        return $result;
    }
}
