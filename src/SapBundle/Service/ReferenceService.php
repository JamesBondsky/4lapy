<?php

namespace FourPaws\SapBundle\Service;

use Bitrix\Highloadblock\DataManager;
use Cocur\Slugify\SlugifyInterface;
use FourPaws\BitrixOrm\Model\HlbReferenceItem;
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
     * @throws \FourPaws\SapBundle\Exception\NotFoundDataManagerException
     * @return null|HlbReferenceItem
     */
    public function getOrCreate(string $propertyCode, string $xmlId, string $name)
    {
        return $this->get($propertyCode, $xmlId) ?: $this->create($propertyCode, $xmlId, $name);
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

        return $addResult->isSuccess() ? $this->get($propertyCode, $xmlId) : null;
    }

    /**
     * @param DataManager $dataManager
     * @param string      $name
     *
     * @return string
     */
    protected function getUniqueCode(DataManager $dataManager, string $name): string
    {
        $i = 0;
        $code = $this->slugify->slugify($name);
        do {
            if ($i > 10) {
                return md5($code . microtime());
            }
            $result = $dataManager::query()
                ->addFilter('=UF_CODE', $code . ($i > 0 ? $i : ''))
                ->exec();
            $i++;
        } while ($result->fetch());
        return $code . ($i > 0 ? $i : '');
    }
}
