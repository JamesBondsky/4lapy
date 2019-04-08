<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 25.03.2019
 * Time: 17:15
 */

namespace FourPaws\PersonalBundle\Repository;


use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Entity\Base;
use Bitrix\Sale\OrderTable;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\AppBundle\Repository\BaseHlRepository;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrderSubscribeRepository extends BaseHlRepository
{
    public const HL_NAME = 'OrderSubscribe';

    /** @var OrderSubscribe $entity */
    protected $entity;

    /** @var array */
    private $hlBlockData;

    /** @var Base */
    private $hlEntity;

    /** @var array */
    private $hlEntityFields;

    /**@var UserService */
    public $curUserService;

    /**
     * PetRepository constructor.
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
        $this->setEntityClass(OrderSubscribe::class);
        $this->curUserService = $currentUserProvider;
    }

    /**
     * @return bool
     * @throws ServiceNotFoundException
     * @throws ValidationException
     * @throws NotAuthorizedException
     * @throws BitrixRuntimeException
     * @throws ServiceCircularReferenceException
     * @throws \Exception
     */
    public function create(): bool
    {
        if ($this->entity->getUserId() === 0) {
            try {
                $this->entity->setUserId($this->curUserService->getCurrentUserId());
            } catch (NotAuthorizedException $e) {
                return false;
            }
        }

        $this->entity->setDateCreate(new DateTime());

        return parent::create();
    }

    /**
     * @return array
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Exception
     */
    protected function getHlBlock(): array
    {
        if (!$this->hlBlockData) {
            $dataManager = $this->getDataManager();
            if(!$dataManager) {
                throw new \Exception('Highloadblock '.static::HL_NAME.' not found');
            }
            if(!method_exists($dataManager, 'getHighloadBlock')) {
                throw new \Exception('It is not highloadblock entity');
            }
            $this->hlBlockData = $dataManager->getHighloadBlock();
        }

        return $this->hlBlockData;
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
        if (!$this->hlEntity) {
            $dataManager = $this->getDataManager();
            if(!$dataManager) {
                throw new \Exception('Highloadblock '.static::HL_NAME.' not found');
            }
            $this->hlEntity = $dataManager::getEntity();
        }

        return $this->hlEntity;
    }

    /**
     * @return string
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Exception
     */
    public function getHlBlockEntityClass(): string
    {
        return $this->getHlBlockEntity()->getDataClass();
    }

    /**
     * @return array
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Exception
     */
    public function getHlBlockEntityFields(): array
    {
        if (!$this->hlEntityFields) {
            $this->hlEntityFields = $GLOBALS['USER_FIELD_MANAGER']->GetUserFields('HLBLOCK_'.$this->getHlBlockId());
        }

        return $this->hlEntityFields;
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
        $params['filter']['=UF_ORDER'] = $orderId;

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
                '=this.UF_ORDER' => 'ref.ID'
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
}