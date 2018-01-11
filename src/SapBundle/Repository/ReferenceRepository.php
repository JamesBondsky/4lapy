<?php

namespace FourPaws\SapBundle\Repository;

use Bitrix\Highloadblock\DataManager;
use Bitrix\Main\Entity\AddResult;
use Doctrine\Common\Collections\Collection;
use FourPaws\BitrixOrm\Model\HlbReferenceItem;
use FourPaws\BitrixOrm\Query\HlbReferenceQuery;

class ReferenceRepository
{
    /**
     * @var DataManager
     */
    private $dataManager;

    public function __construct(DataManager $dataManager)
    {
        $this->dataManager = $dataManager;
    }

    /**
     * @param int $id
     *
     * @return null|HlbReferenceItem
     */
    public function find(int $id)
    {
        return $this->findBy(['ID' => $id], [], 1)->first();
    }

    /**
     * @param string $xmlId
     *
     * @return null|HlbReferenceItem
     */
    public function findByXmlId(string $xmlId)
    {
        return $this->findBy(['UF_XML_ID' => $xmlId], [], 1)->first();
    }

    /**
     * @param array $criteria
     * @param array $orderBy
     * @param int   $limit
     * @param int   $offset
     *
     * @return Collection|HlbReferenceItem[]
     */
    public function findBy(array $criteria = [], array $orderBy = [], int $limit = 0, int $offset = 0): Collection
    {
        return $this
            ->getQuery()
            ->withFilter($criteria)
            ->withOrder($orderBy)
            ->withLimit($limit)
            ->withOffset($offset)
            ->exec();
    }

    /**
     * @param HlbReferenceItem $item
     *
     * @return AddResult
     */
    public function add(HlbReferenceItem $item): AddResult
    {
        $item->withId(0);
        $fields = $item->toArray();
        unset($fields['ID']);

        $result = $this->dataManager::add($fields);
        if ($result->isSuccess()) {
            $item->withId($result->getId());
        }
        return $result;
    }

    /**
     * @return HlbReferenceQuery
     */
    protected function getQuery(): HlbReferenceQuery
    {
        return new HlbReferenceQuery($this->dataManager::query());
    }
}
