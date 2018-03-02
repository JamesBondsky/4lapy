<?php

namespace FourPaws\PersonalBundle\Repository;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\Entity\DeleteResult;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\UpdateResult;
use Bitrix\Main\Error;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Internals\OrderTable;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\AppBundle\Repository\BaseHlRepository;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use FourPaws\PersonalBundle\Exception\InvalidArgumentException;
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
     * @throws ArgumentException
     * @throws SystemException
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
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Exception
     */
    public function getHlBlockId(): int
    {
        return $this->getHlBlock()['ID'];
    }

    /**
     * @return string
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Exception
     */
    public function getHlBlockTableName(): string
    {
        return $this->getHlBlock()['TABLE_NAME'];
    }

    /**
     * @return Base
     * @throws ArgumentException
     * @throws SystemException
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
     * @throws ArgumentException
     * @throws SystemException
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
     * @throws ArgumentException
     * @throws SystemException
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
     * @return AddResult
     * @throws ArgumentException
     * @throws InvalidArgumentException
     * @throws SystemException
     * @throws \Exception
     */
    public function createEx(): AddResult
    {
        $args = func_get_args();

        $entityClass = $this->getEntityClass();
        if (count($args) === 1 && ($args[0] instanceof $entityClass)) {
            $this->setEntity($args[0]);
            $result = $this->doCreateByEntity();
        } elseif (count($args) === 1 && is_array($args[0])) {
            $result = $this->doCreateByArray($args[0]);
        } else {
            throw new InvalidArgumentException('Wrong arguments');
        }

        return $result;
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
        if (!$this->entity->getDateStart()) {
            throw new ArgumentNullException('Start date not defined');
        }
        if (!$this->entity->getDeliveryFrequency()) {
            throw new ArgumentNullException('Delivery frequency not defined');
        }

        // дата создания всегда текущая
        $this->entity->setDateCreate((new DateTime()));
        // дата изменения всегда текущая
        $this->entity->setDateEdit((new DateTime()));

        // явная установка значения (по умолчанию null)
        if (!$this->entity->isActive()) {
            $this->entity->setActive(false);
        }

        return parent::create();
    }

    /**
     * @param array $fields
     * @return AddResult
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Exception
     */
    protected function doCreateByArray(array $fields): AddResult
    {
        $result = new AddResult();

        // дата создания всегда текущая
        $fields['UF_DATE_CREATE'] = new DateTime();
        // дата изменения всегда текущая
        $fields['UF_DATE_EDIT'] = new DateTime();

        $fields['UF_ACTIVE'] = isset($fields['UF_ACTIVE']) && $fields['UF_ACTIVE'] ? 1 : 0;

        try {
            /** @var OrderSubscribe $tmpEntity */
            $tmpEntity = $this->dataToEntity($fields, $this->getEntityClass(), 'create');
            if ($tmpEntity->getOrderId() <= 0) {
                $result->addError(
                    new Error('Order id not defined', 'argumentNullException')
                );
            } elseif (!$tmpEntity->getDateStart()) {
                $result->addError(
                    new Error('Start date not defined', 'argumentNullException')
                );
            } elseif (!$tmpEntity->getDeliveryFrequency()) {
                $result->addError(
                    new Error('Delivery frequency not defined', 'argumentNullException')
                );
            }
        } catch(\Exception $exception) {
            $result->addError(
                new Error($exception->getMessage(), 'setEntityException')
            );
        }

        if ($result->isSuccess()) {
            /** @var DataManager $entityClass */
            $entityClass = $this->getHlBlockEntityClass();
            $addResult = $entityClass::add(
                $fields
            );
            if ($addResult->isSuccess()) {
                $result->setId($addResult->getId());
            } else {
                $result->addErrors($addResult->getErrors());
            }
        }

        return $result;
    }

    protected function doCreateByEntity(): AddResult
    {
        $result = new AddResult();

        try {
            $res = $this->create();
            if ($res) {
                $result->setId($this->entity->getId());
            } else {
                $result->addError(
                    new Error('Неизвестная ошибка', 'createUnknownError')
                );
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

        return $result;
    }

    /**
     * @return UpdateResult
     * @throws ArgumentException
     * @throws InvalidArgumentException
     * @throws SystemException
     * @throws \Exception
     */
    public function updateEx(): UpdateResult
    {
        $args = func_get_args();

        $entityClass = $this->getEntityClass();
        if (count($args) === 1 && ($args[0] instanceof $entityClass)) {
            $this->setEntity($args[0]);
            $result = $this->doUpdateByEntity();
        } elseif (count($args) === 2 && (int)$args[0] > 0 && is_array($args[1])) {
            $result = $this->doUpdateByArray((int)$args[0], $args[1]);
        } else {
            throw new InvalidArgumentException('Wrong arguments');
        }

        return $result;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function update(): bool
    {
        // дата создания не обновляется
        $this->entity->setDateCreate(null);
        // дата изменения всегда текущая
        $this->entity->setDateEdit((new DateTime()));

        // явная установка значения (по умолчанию null)
        if (!$this->entity->isActive()) {
            $this->entity->setActive(false);
        }

        return parent::update();
    }

    /**
     * @param int $id
     * @param array $fields
     * @return UpdateResult
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Exception
     */
    protected function doUpdateByArray(int $id, array $fields): UpdateResult
    {
        $result = new UpdateResult();

        // дата создания не обновляется
        if (array_key_exists('UF_DATE_CREATE', $fields)) {
            unset($fields['UF_DATE_CREATE']);
        }
        // если дата изменения не задана, то устанавливаем текущую дату автоматически
        if (!isset($fields['UF_DATE_EDIT'])) {
            $fields['UF_DATE_EDIT'] = new DateTime();
        }

        try {
            $this->dataToEntity($fields, $this->getEntityClass(), 'read');
        } catch(\Exception $exception) {
            $result->addError(
                new Error($exception->getMessage(), 'setEntityException')
            );
        }

        if ($result->isSuccess()) {
            /** @var DataManager $entityClass */
            $entityClass = $this->getHlBlockEntityClass();
            $updateResult = $entityClass::update(
                $id,
                $fields
            );
            if (!$updateResult->isSuccess()) {
                $result->addErrors($updateResult->getErrors());
            }
        }

        return $result;
    }

    /**
     * @return UpdateResult
     */
    protected function doUpdateByEntity(): UpdateResult
    {
        $result = new UpdateResult();
        try {
            $res = $this->update();
            if (!$res) {
                $result->addError(
                    new Error('Неизвестная ошибка', 'updateUnknownError')
                );
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
