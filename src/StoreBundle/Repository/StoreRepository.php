<?php

namespace FourPaws\StoreBundle\Repository;

use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use Bitrix\Catalog\StoreTable;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Sale\Location\LocationTable;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\BitrixRuntimeException;
use FourPaws\StoreBundle\Exception\ConstraintDefinitionException;
use FourPaws\StoreBundle\Exception\InvalidIdentifierException;
use FourPaws\StoreBundle\Exception\ValidationException;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class StoreRepository
{
    /**
     * @var ArrayTransformerInterface
     */
    private $arrayTransformer;
    
    /**
     * @var ValidatorInterface
     */
    private $validator;
    
    public function __construct(ArrayTransformerInterface $arrayTransformer, ValidatorInterface $validator)
    {
        $this->arrayTransformer = $arrayTransformer;
        $this->validator        = $validator;
    }
    
    /**
     * @param Store $store
     *
     * @return bool
     * @throws \Exception
     * @throws \FourPaws\StoreBundle\Exception\BitrixRuntimeException
     * @throws \FourPaws\StoreBundle\Exception\ValidationException
     */
    public function create(Store $store) : bool
    {
        $validationResult = $this->validator->validate($store, null, ['create']);
        if ($validationResult->count() > 0) {
            throw new ValidationException('Wrong entity passed to create');
        }
        
        $result = StoreTable::add(
            $this->arrayTransformer->toArray($store, SerializationContext::create()->setGroups(['create']))
        );
        
        if ($result->isSuccess()) {
            $store->setId((int)$result);
            
            return true;
        }
        
        throw new BitrixRuntimeException($result->getErrorMessages());
    }
    
    /**
     * @param int $id
     *
     * @return null|Store
     * @throws \Exception
     * @throws \FourPaws\StoreBundle\Exception\ConstraintDefinitionException
     * @throws \FourPaws\StoreBundle\Exception\InvalidIdentifierException
     */
    public function find(int $id)
    {
        $this->checkIdentifier($id);
        
        return $this->findBy(['ID' => $id], [], 1)->first();
    }
    
    /**
     * @param int $id
     *
     * @throws ConstraintDefinitionException
     * @throws InvalidIdentifierException
     */
    protected function checkIdentifier(int $id)
    {
        try {
            $result = $this->validator->validate(
                $id,
                [
                    new NotBlank(),
                    new GreaterThanOrEqual(['value' => 1]),
                    new Type(['type' => 'integer']),
                ],
                ['delete']
            );
        } catch (ValidatorException $exception) {
            throw new ConstraintDefinitionException('Wrong constraint configuration');
        }
        if ($result->count()) {
            throw new InvalidIdentifierException(sprintf('Wrong identifier %s passed', $id));
        }
    }
    
    /**
     * @param array    $criteria
     * @param array    $orderBy
     * @param null|int $limit
     * @param null|int $offset
     *
     * @return StoreCollection
     * @throws \Exception
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = [],
        int $limit = null,
        int $offset = null
    ) : StoreCollection
    {
        if (empty($orderBy)) {
            $orderBy = [
                'SORT' => 'ASC',
                'ID'   => 'ASC',
            ];
        }
        
        $query = StoreTable::query();
        $query->setSelect(
            [
                '*',
                'UF_*',
            ]
        )->setFilter($criteria)->setOrder($orderBy)->setLimit($limit)->setOffset($offset);
        /** @todo сделать универсальные условия */
        if (array_key_exists('LOCATION.NAME.NAME', $orderBy)) {
            $query->registerRuntimeField(
                'LOCATION',
                new ReferenceField(
                    'CATALOG', LocationTable::getEntity(), ['=this.UF_LOCATION' => 'ref.ID']
                )
            );
        }
        if (array_key_exists('METRO.UF_NAME', $orderBy) || isset($criteria[0]['%METRO.UF_NAME'])) {
            $query->registerRuntimeField(
                'METRO',
                new ReferenceField(
                    'CATALOG',
                    HLBlockFactory::createTableObject('MetroStations')::getEntity(),
                    ['=this.UF_METRO' => 'ref.ID']
                )
            );
        }
        $stores = $query->exec();
        
        $result = [];
        while ($store = $stores->fetch()) {
            $result[$store['ID']] = $store;
        }
        
        /**
         * todo change group name to constant
         */
        return new StoreCollection(
            $this->arrayTransformer->fromArray(
                $result,
                sprintf('array<%s>', Store::class),
                DeserializationContext::create()->setGroups(['read'])
            )
        );
    }
    
    /**
     * @param Store $store
     *
     * @return bool
     * @throws \Exception
     * @throws \FourPaws\StoreBundle\Exception\BitrixRuntimeException
     * @throws \FourPaws\StoreBundle\Exception\ConstraintDefinitionException
     * @throws \FourPaws\StoreBundle\Exception\InvalidIdentifierException
     * @throws \FourPaws\StoreBundle\Exception\ValidationException
     */
    public function update(Store $store)
    {
        $this->checkIdentifier($store->getId());
        $validationResult = $this->validator->validate($store, null, ['update']);
        if ($validationResult->count() > 0) {
            throw new ValidationException('Wrong entity passed to update');
        }
        $result = StoreTable::update(
            $store->getId(),
            $this->arrayTransformer->toArray($store, SerializationContext::create()->setGroups(['update']))
        );
        
        if ($result->isSuccess()) {
            return true;
        }
        
        throw new BitrixRuntimeException($result->getErrorMessages());
    }
    
    /**
     * @param int $id
     *
     * @return bool
     * @throws \Exception
     * @throws \FourPaws\StoreBundle\Exception\BitrixRuntimeException
     * @throws \FourPaws\StoreBundle\Exception\ConstraintDefinitionException
     * @throws \FourPaws\StoreBundle\Exception\InvalidIdentifierException
     */
    public function delete(int $id)
    {
        $this->checkIdentifier($id);
        $result = StoreTable::delete($id);
        if ($result->isSuccess()) {
            return true;
        }
        
        throw new BitrixRuntimeException($result->getErrorMessages());
    }
}
