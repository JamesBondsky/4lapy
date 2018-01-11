<?php

namespace FourPaws\SapBundle\Repository;

use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Entity\UpdateResult;
use Bitrix\Main\Error;
use Doctrine\Common\Collections\Collection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;

class OfferRepository
{
    /**
     * @var \CIBlockElement
     */
    private $iblockElement;

    public function __construct()
    {
        $this->iblockElement = new \CIBlockElement();
    }

    /**
     * @param int $id
     *
     * @return Offer|null
     */
    public function find(int $id)
    {
        return $this->findBy(['=ID' => $id], [], 1)->first();
    }

    /**
     * @param string $xmlId
     *
     * @return Offer|null
     */
    public function findByXmlId(string $xmlId)
    {
        return $this->findBy(['XML_ID' => $xmlId], [], 1)->first();
    }

    /**
     * @param array $criteria
     * @param array $orderBy
     * @param int   $limit
     *
     * @return Collection|Offer[]
     */
    public function findBy(array $criteria = [], array $orderBy = [], int $limit = 0): Collection
    {
        $query = $this->getQuery();
        return $query
            ->withFilter(array_merge($query->getBaseFilter(), $criteria))
            ->withOrder($orderBy)
            ->withNav($limit > 0 ? ['nTopCount' => $limit] : [])
            ->exec();
    }

    /**
     * @param Offer $offer
     *
     * @return AddResult
     */
    public function add(Offer $offer)
    {
        $offer->withId(0);
        $data = $offer->toArray();
        unset($data['ID']);

        $result = new AddResult();
        if ($id = $this->iblockElement->Add($data)) {
            $result->setId($id);
            $offer->withId($id);
        } elseif ($this->iblockElement->LAST_ERROR) {
            $result->addError(new Error($this->iblockElement->LAST_ERROR));
            $this->iblockElement->LAST_ERROR = null;
        } else {
            $result->addError(new Error('Неизвестная ошибка'));
        }
        return $result;
    }

    /**
     * @param Offer $offer
     *
     * @return UpdateResult
     */
    public function update(Offer $offer)
    {
        $data = $offer->toArray();
        $properties = $data['PROPERTY_VALUES'];
        unset($data['PROPERTY_VALUES']);

        $updateResult = new UpdateResult();
        if ($this->iblockElement->Update($offer->getId(), $data)) {
            $this->setProperties($offer->getId(), $properties);
        } elseif ($this->iblockElement->LAST_ERROR) {
            $updateResult->addError(new Error($this->iblockElement->LAST_ERROR));
            $this->iblockElement->LAST_ERROR = null;
        } else {
            $updateResult->addError(new Error('Неизвестная ошибка'));
        }
        return $updateResult;
    }

    /**
     * @param int  $id
     * @param bool $active
     *
     * @return bool
     */
    public function setActive(int $id, bool $active = true)
    {
        return $this->iblockElement->Update($id, ['ACTIVE' => $active ? 'Y' : 'N']);
    }

    /**
     * @param int   $elementId
     * @param array $properties
     */
    public function setProperties(int $elementId, array $properties)
    {
        if ($properties) {
            \CIBlockElement::SetPropertyValuesEx($elementId, $this->getQuery()->getSelect()['IBLOCK_ID'], $properties);
        }
    }

    /**
     * @return OfferQuery
     */
    protected function getQuery()
    {
        return new OfferQuery();
    }
}
