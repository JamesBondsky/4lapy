<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Service;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Cocur\Slugify\SlugifyInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\BitrixOrm\Model\HlbReferenceItem;
use FourPaws\SapBundle\Dto\In\Offers\Material;
use FourPaws\SapBundle\Dto\In\Offers\Property;
use FourPaws\SapBundle\Dto\In\Offers\PropertyValue;
use FourPaws\SapBundle\Enum\SapProductField;
use FourPaws\SapBundle\Exception\CantCreateReferenceItem;
use FourPaws\SapBundle\Exception\LogicException;
use FourPaws\SapBundle\Exception\NotFoundDataManagerException;
use FourPaws\SapBundle\Exception\NotFoundReferenceRepositoryException;
use FourPaws\SapBundle\ReferenceDirectory\SapReferenceStorage;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;

/**
 * Class ReferenceService
 *
 * @package FourPaws\SapBundle\Service
 */
class ReferenceService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var SapReferenceStorage
     */
    private $referenceStorage;
    /**
     * @var SlugifyInterface
     */
    private $slugify;

    /**
     * ReferenceService constructor.
     *
     * @param SapReferenceStorage $referenceStorage
     * @param SlugifyInterface $slugify
     */
    public function __construct(SapReferenceStorage $referenceStorage, SlugifyInterface $slugify)
    {
        $this->referenceStorage = $referenceStorage;
        $this->slugify = $slugify;
    }

    /**
     * @param string $propertyCode
     * @param string $xmlId
     * @param string $name
     *
     * @throws RuntimeException
     * @throws NotFoundReferenceRepositoryException
     * @throws LogicException
     * @throws CantCreateReferenceItem
     * @throws NotFoundDataManagerException
     * @return HlbReferenceItem
     */
    public function getOrCreate(string $propertyCode, string $xmlId, string $name): HlbReferenceItem
    {
        $result = $this->get($propertyCode, $xmlId);

        if (!$result) {
            $result = $this->create($propertyCode, $xmlId, $name);
        }

        if (!$result) {
            throw new LogicException('For some reason created item was not get from dataManager');
        }

        return $result;
    }

    /**
     * @param string $propertyCode
     * @param string $xmlId
     *
     * @throws RuntimeException
     * @throws NotFoundReferenceRepositoryException
     * @return null|HlbReferenceItem
     */
    public function get(string $propertyCode, string $xmlId): ?HlbReferenceItem
    {
        return $this->referenceStorage->findByXmlId($propertyCode, $xmlId) ?: null;
    }

    /**
     * @param string $propertyCode
     * @param string $xmlId
     * @param string $name
     *
     * @throws RuntimeException
     * @throws CantCreateReferenceItem
     * @throws NotFoundReferenceRepositoryException
     * @return null|HlbReferenceItem
     */
    public function create(string $propertyCode, string $xmlId, string $name): ?HlbReferenceItem
    {
        $item = new HlbReferenceItem();
        $item
            ->withCode($this->getUniqueCode($propertyCode, $name))
            ->withXmlId($xmlId)
            ->withName($name);

        $referenceRepository = $this->referenceStorage->getReferenceRepositoryRegistry()->get($propertyCode);
        $addResult = $referenceRepository->add($item);

        if ($addResult->isSuccess()) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $this->log()->info(
                sprintf('Создано значение справочника для свойства %s: %s', $propertyCode, $xmlId),
                $item->toArray()
            );
            return $this->get($propertyCode, $xmlId);
        }
        throw new CantCreateReferenceItem(implode(' ', $addResult->getErrorMessages()));
    }

    /**
     * @param Property $property
     *
     * @throws RuntimeException
     * @throws NotFoundReferenceRepositoryException
     * @throws NotFoundDataManagerException
     * @throws LogicException
     * @throws CantCreateReferenceItem
     * @return Collection|HlbReferenceItem[]
     */
    public function getPropertyValueHlbElement(Property $property): Collection
    {
        $values = $property->getValues()->filter(function (PropertyValue $propertyValue) {
            return $propertyValue->getName();
        });
        $collection = new ArrayCollection();
        foreach ($values as $value) {
            $collection->add($this->getOrCreate($property->getCode(), $value->getCode(), $value->getName()));
        }

        return $collection;
    }

    /**
     * @param Material $material
     *
     * @throws RuntimeException
     * @throws NotFoundReferenceRepositoryException
     * @throws NotFoundDataManagerException
     * @throws LogicException
     * @throws CantCreateReferenceItem
     *
     * @deprecated use \FourPaws\SapBundle\Service\ReferenceService::fillFromMaterials instead
     */
    public function fillFromMaterial(Material $material): void
    {
        foreach ($material->getProperties()->getProperties() as $property) {
            if ($this->referenceStorage->getReferenceRepositoryRegistry()->has($property->getCode())) {
                $this->getPropertyValueHlbElement($property);
            }
        }

        /**
         * create or update country
         */
        $isSetCountry = $material->getCountryOfOriginCode() &&
            $material->getCountryOfOriginName() &&
            $this->referenceStorage->getReferenceRepositoryRegistry()->has(SapProductField::COUNTRY);
        if ($isSetCountry) {
            $this->getOrCreate(
                SapProductField::COUNTRY,
                $material->getCountryOfOriginCode(),
                $material->getCountryOfOriginName()
            );
        }
    }

    /**
     * @param ArrayCollection $materials
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function fillFromMaterials(ArrayCollection $materials): void
    {
        $properties = $this->getPropertiesFromMaterials($materials);

        foreach ($properties as $propertyCode => $propertyValues) {
            if (!$this->referenceStorage->getReferenceRepositoryRegistry()->has($propertyCode)) {
                unset($properties[$propertyCode]);
            }
        }

        if ($properties) {
            $this->createIfNotExistMultiple($properties);
        }

        unset($properties);


        /**
         * create or update country
         */
        /** @var Material $material */
        $countries = [];
        foreach ($materials as $material) {
            if ($this->referenceStorage->getReferenceRepositoryRegistry()->has(SapProductField::COUNTRY)) {
                $isSetCountry = $material->getCountryOfOriginCode() &&
                    $material->getCountryOfOriginName();

                if ($isSetCountry) {
                    $countries[$material->getCountryOfOriginCode()] = (new PropertyValue())
                        ->setCode($material->getCountryOfOriginCode())
                        ->setName($material->getCountryOfOriginName());
                }
            }
        }
        if ($countries) {
            $this->createIfNotExistMultiple([SapProductField::COUNTRY => $countries]);
        }
    }

    /**
     * @param ArrayCollection $materials
     * @return array
     */
    private function getPropertiesFromMaterials(ArrayCollection $materials): array
    {
        $properties = [];
        /** @var Material $material */
        foreach ($materials as $material) {
            foreach ($material->getProperties()->getProperties() as $property) {
                $propertyValues = $property->getValues();
                foreach ($propertyValues as $propertyValue) {
                    if (trim($propertyValue->getName())) {
                        $properties[$property->getCode()][$propertyValue->getCode()] = $propertyValue;
                    }
                }
            }
        }

        return $properties;
    }

    /**
     * @param array $properties
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private function createIfNotExistMultiple(array $properties): void
    {
        foreach ($properties as $propertyCode => $propertyValues) {
            $propertyValuesXmlIds = array_keys($propertyValues);
            if ($propertyValuesXmlIds) {
                $notExistingValuesXmlIds = $this->referenceStorage->getNotExistingXmlIdList($propertyCode, $propertyValuesXmlIds);

                foreach ($notExistingValuesXmlIds as $valueXmlId) {
                    $result = $this->create($propertyCode, $valueXmlId, $propertyValues[$valueXmlId]->getName());

                    if (!$result) {
                        throw new LogicException('For some reason created item was not created. $propertyCode: ' . $propertyCode . '. $valueXmlId: ' . $valueXmlId);
                    }
                }
            }
        }
    }

    /**
     * @param string $code
     * @param Material $material
     * @param bool $multiple
     *
     * @throws NotFoundReferenceRepositoryException
     * @throws NotFoundDataManagerException
     * @throws LogicException
     * @throws RuntimeException
     * @throws CantCreateReferenceItem
     * @return array|string
     */
    public function getPropertyBitrixValue(string $code, Material $material, bool $multiple = false)
    {
        $property = $material->getProperties()->getProperty($code);
        $result = $multiple ? [] : '';
        if ($property) {
            $hlbElements = $this
                ->getPropertyValueHlbElement($material->getProperties()->getProperty($code));

            $xmlIds = $hlbElements->map(function (HlbReferenceItem $item) {
                return $item->getXmlId();
            });

            if (!$multiple && $xmlIds->count() > 1) {
                $this
                    ->log()
                    ->error(
                        sprintf('Get more than one value for not multiple property %s.', $code),
                        $xmlIds->toArray()
                    );
            }
            $result = $multiple ? $xmlIds->toArray() : $xmlIds->first();
        }
        return $result;
    }

    /**
     * @param string $propertyCode
     * @param string $name
     *
     * @throws RuntimeException
     * @throws NotFoundReferenceRepositoryException
     * @return string
     */
    protected function getUniqueCode(string $propertyCode, string $name): string
    {
        $i = 0;
        $code = $this->slugify->slugify($name);
        do {
            if ($i > 10) {
                $resultCode = md5($code . microtime());
                break;
            }
            $resultCode = $code . ($i > 0 ? $i : '');
            $result = $this->referenceStorage->findByCallable(
                $propertyCode,
                function (HlbReferenceItem $item) use ($resultCode) {
                    return $item->getCode() === $resultCode;
                }
            );
            $i++;
        } while ($result->count());
        return $resultCode;
    }
}
