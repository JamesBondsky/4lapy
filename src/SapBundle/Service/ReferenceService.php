<?php

namespace FourPaws\SapBundle\Service;

use Cocur\Slugify\SlugifyInterface;
use FourPaws\BitrixOrm\Model\HlbReferenceItem;
use FourPaws\SapBundle\Exception\CantCreateReferenceItem;
use FourPaws\SapBundle\Exception\LogicException;
use FourPaws\SapBundle\ReferenceDirectory\SapReferenceStorage;

class ReferenceService
{
    /**
     * @var SapReferenceStorage
     */
    private $referenceStorage;
    /**
     * @var SlugifyInterface
     */
    private $slugify;

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
     * @throws \FourPaws\SapBundle\Exception\LogicException
     * @throws \FourPaws\SapBundle\Exception\CantCreateReferenceItem
     * @throws \FourPaws\SapBundle\Exception\NotFoundDataManagerException
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
     * @return null|HlbReferenceItem
     */
    public function get(string $propertyCode, string $xmlId)
    {
        return $this->referenceStorage->findByXmlId($propertyCode, $xmlId);
    }

    /**
     * @param string $propertyCode
     * @param string $xmlId
     * @param string $name
     *
     * @throws \FourPaws\SapBundle\Exception\CantCreateReferenceItem
     * @throws \FourPaws\SapBundle\Exception\NotFoundDataManagerException
     * @return null|HlbReferenceItem
     */
    public function create(string $propertyCode, string $xmlId, string $name)
    {
        $dataManager = $this->referenceStorage->getReferenceRegistry()->get($propertyCode);
        $addResult = $dataManager::add([
            'UF_CODE'   => $this->getUniqueCode($dataManager, $name),
            'UF_XML_ID' => $xmlId,
            'UF_NAME'   => $name,
        ]);

        if ($addResult->isSuccess()) {
            return $this->get($propertyCode, $xmlId);
        }
        throw new CantCreateReferenceItem(implode(' ', $addResult->getErrorMessages()));
    }

    /**
     * @param string $propertyCode
     * @param string $name
     *
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
        } while ($result->count());
        return $resultCode;
    }
}
