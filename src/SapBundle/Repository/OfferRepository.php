<?php

namespace FourPaws\SapBundle\Repository;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Entity\UpdateResult;
use Bitrix\Main\Error;
use Doctrine\Common\Collections\Collection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

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
     * @return null|Offer
     */
    public function find(int $id)
    {
        return $this->findBy(['=ID' => $id], [], 1)->first();
    }

    /**
     * @param string $xmlId
     *
     * @return null|Offer
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
    public function add(Offer $offer): AddResult
    {
        $offer->withIblockId($this->getIblockId());
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
    public function update(Offer $offer): UpdateResult
    {
        $offer->withIblockId($this->getIblockId());
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
    public function setActive(int $id, bool $active = true): bool
    {
        return $this->iblockElement->Update($id, ['ACTIVE' => $active ? 'Y' : 'N']);
    }

    /**
     * @param int   $elementId
     * @param array $properties
     *
     */
    public function setProperties(int $elementId, array $properties)
    {
        if ($properties) {
            \CIBlockElement::SetPropertyValuesEx($elementId, $this->getIblockId(), $properties);
        }
    }

    /**
     * @return OfferQuery
     */
    protected function getQuery(): OfferQuery
    {
        return new OfferQuery();
    }

    /** @noinspection PhpDocMissingThrowsInspection */
    /**
     * @return int
     */
    protected function getIblockId(): int
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS);
    }
}
