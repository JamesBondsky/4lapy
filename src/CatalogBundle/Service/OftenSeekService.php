<?php

namespace FourPaws\CatalogBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Catalog\Model\OftenSeekSection;
use FourPaws\CatalogBundle\Repository\OftenSeekRepository;
use FourPaws\CatalogBundle\Repository\OftenSeekSectionRepository;

class OftenSeekService implements OftenSeekInterface
{

    private $oftenSeekRepository;
    private $oftenSeekSectionRepository;

    public function __construct(OftenSeekRepository $oftenSeekRepository, OftenSeekSectionRepository $oftenSeekSectionRepository)
    {
        $this->oftenSeekRepository = $oftenSeekRepository;
        $this->oftenSeekSectionRepository = $oftenSeekSectionRepository;
    }

    /**
     * @param int $sectionId
     *
     * @return ArrayCollection|void
     */
    public function getItemsBySection(int $sectionId) : ArrayCollection
    {
        $result = new ArrayCollection();
        return $result;
    }

    /**
     * @param int $sectionId
     *
     * @return ArrayCollection|void
     */
    public function getSectionsByCatalogSection(int $sectionId) : ArrayCollection
    {
        $result = new ArrayCollection();
        $this->oftenSeekSectionRepository->findBy(['filter'=>['UF_SECTION'=>$sectionId], 'select'=>['ID', 'UF_SECTION', 'UF_COUNT', 'NAME', 'ACTIVE', 'DEPTH_LEVEL', 'LEFT_MARGIN', 'RIGHT_MARGIN']]);
        return $result;
    }

    /**
     * @param int $sectionId
     *
     * @return ArrayCollection
     */
    public function getItems(int $sectionId): ArrayCollection
    {
        $result = new ArrayCollection();
        $sections = $this->getSectionsByCatalogSection();
        if(!$sections->isEmpty()){
            /** @var OftenSeekSection $section */
            foreach ($sections as $section) {
                $items = $this->getItemsBySection($section->getId());
                if(!$items->isEmpty()){
                    $result = $items;
                    break;
                }
            }
        }
        return $result;
    }
}
