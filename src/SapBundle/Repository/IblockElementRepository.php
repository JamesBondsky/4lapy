<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Repository;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Entity\UpdateResult;
use Bitrix\Main\Error;
use Cocur\Slugify\SlugifyInterface;
use Doctrine\Common\Collections\Collection;
use FourPaws\AppBundle\Service\ToBitrixDataArrayConverter;
use FourPaws\BitrixOrm\Model\IblockElement;
use FourPaws\BitrixOrm\Model\Interfaces\ToArrayInterface;
use FourPaws\BitrixOrm\Query\IblockElementQuery;

abstract class IblockElementRepository
{
    /**
     * @var ToBitrixDataArrayConverter
     */
    private $converter;
    /**
     * @var SlugifyInterface
     */
    private $slugify;

    /**
     * @var \CIBlockElement
     */
    private $iblockElement;

    public function __construct(ToBitrixDataArrayConverter $converter, SlugifyInterface $slugify)
    {
        $this->converter = $converter;
        $this->iblockElement = new \CIBlockElement();
        $this->slugify = $slugify;
    }

    /**
     * @param int $id
     *
     * @return null|IblockElement
     */
    public function find(int $id)
    {
        return $this->findBy(['=ID' => $id], [], 1)->first();
    }

    /**
     * @param string $xmlId
     *
     * @return null|IblockElement
     */
    public function findByXmlId(string $xmlId)
    {
        return $this->findBy(['=XML_ID' => $xmlId], [], 1)->first();
    }

    /**
     * @param string $code
     *
     * @return null|IblockElement
     */
    public function findByCode(string $code)
    {
        return $this->findBy(['=CODE' => $code], [], 1)->first();
    }

    /**
     * @param array $criteria
     * @param array $orderBy
     * @param int   $limit
     *
     * @return Collection|IblockElement[]
     */
    public function findBy(array $criteria = [], array $orderBy = [], int $limit = 0): Collection
    {
        return $this->getQuery()
            ->withFilter($criteria)
            ->withOrder($orderBy)
            ->withNav($limit > 0 ? ['nTopCount' => $limit] : [])
            ->exec();
    }

    /**
     * @param string $xmlId
     *
     * @return null|int
     */
    public function findIdByXmlId(string $xmlId)
    {
        $data = $this->getQuery()
            ->withFilter(['=XML_ID' => $xmlId])
            ->withSelect(['ID'])
            ->withNav(['nTopCount' => 1])
            ->doExec()
            ->Fetch();
        $id = $data['ID'] ?? null;
        return $id ? (int)$id : null;
    }

    /**
     * @param int  $id
     * @param bool $active
     *
     * @return bool
     */
    public function setActive(int $id, bool $active = true): bool
    {
        return $this->iblockElement->Update($id, ['ACTIVE' => $active ? 'Y' : 'N']);
    }

    /**
     * @param IblockElement $iblockElement
     *
     * @return AddResult
     */
    public function create(IblockElement $iblockElement): AddResult
    {
        $iblockElement
            ->withId(0)
            ->withIblockId($this->getIblockId())
            ->withCode($this->generateUniqueCode($iblockElement->getName(), $iblockElement->getCode()));
        $data = $this->toArray($iblockElement);
        unset($data['ID']);
        $result = new AddResult();
        if ($id = $this->iblockElement->Add($data)) {
            $result->setId($id);
            $iblockElement->withId($id);
        } elseif ($this->iblockElement->LAST_ERROR) {
            $result->addErrors($this->convertIblockBitrixErrors($this->iblockElement->LAST_ERROR));
            $this->iblockElement->LAST_ERROR = '';
        } else {
            $result->addError(new Error('Неизвестная ошибка'));
        }
        return $result;
    }

    /**
     * @param IblockElement $iblockElement
     *
     * @return UpdateResult
     */
    public function update(IblockElement $iblockElement): UpdateResult
    {
        $updateResult = new UpdateResult();
        if (!$iblockElement->getId()) {
            $updateResult->addError(new Error('Не указан идентификатор продукта'));
            return $updateResult;
        }
        $iblockElement->withIblockId($this->getIblockId());

        $data = $this->toArray($iblockElement);
        $properties = $data['PROPERTY_VALUES'];
        unset($data['PROPERTY_VALUES'], $data['IBLOCK_ID']);

        if ($this->iblockElement->Update($iblockElement->getId(), $data)) {
            $this->setProperties($iblockElement->getId(), $properties);
        } elseif ($this->iblockElement->LAST_ERROR) {
            $updateResult->addErrors($this->convertIblockBitrixErrors($this->iblockElement->LAST_ERROR));
            $this->iblockElement->LAST_ERROR = '';
        } else {
            $updateResult->addError(new Error('Неизвестная ошибка'));
        }
        return $updateResult;
    }


    /**
     * @return int
     */
    abstract public function getIblockId(): int;

    /**
     * @param int   $elementId
     * @param array $properties
     *
     */
    protected function setProperties(int $elementId, array $properties)
    {
        if ($properties) {
            \CIBlockElement::SetPropertyValuesEx($elementId, $this->getIblockId(), $properties);
        }
    }

    /**
     * @param ToArrayInterface $object
     *
     * @return array
     */
    protected function toArray(ToArrayInterface $object): array
    {
        $data = $this->converter->convert(
            $object->toArray(),
            ElementTable::getEntity(),
            ['PROPERTY_VALUES']
        );

        /**
         * @todo check property type
         */
        $data['PROPERTY_VALUES'] = array_map(function ($value) {
            if (\is_bool($value)) {
                return (int)$value;
            }
            return $value;
        }, $data['PROPERTY_VALUES'] ?? []);
        return $data;
    }

    protected function generateUniqueCode(string $name = '', string $code = '')
    {
        $iblockId = $this->getIblockId();
        $i = 0;
        $name = $name ?: md5(microtime());
        $code = $code ?: $this->slugify->slugify($name);
        while ($i < 10) {
            $tmpCode = $i > 0 ? $code . $i : $code;
            $r = ElementTable::query()
                ->setSelect(['ID'])
                ->addFilter('IBLOCK_ID', $iblockId)
                ->addFilter('=CODE', $tmpCode)
                ->setLimit(1)
                ->exec()
                ->getSelectedRowsCount();
            if ($r) {
                $i++;
                continue;
            }
            return $tmpCode;
        }
        return md5($code . microtime());
    }

    /**
     * @param string $lastError
     * @param string $delimiter
     *
     * @return Error[]
     */
    protected function convertIblockBitrixErrors(string $lastError, string $delimiter = '<br>'): array
    {
        return array_map(function ($text) {
            return new Error(trim($text));
        }, explode($delimiter, $lastError) ?? []);
    }

    abstract protected function getQuery(): IblockElementQuery;
}
