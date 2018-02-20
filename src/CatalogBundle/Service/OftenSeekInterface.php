<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 20.02.18
 * Time: 10:51
 */

namespace FourPaws\CatalogBundle\Service;


use Doctrine\Common\Collections\ArrayCollection;

interface OftenSeekInterface
{
    /**
     * @param int $sectionId
     *
     * @return ArrayCollection
     */
    public function getItems(int $sectionId) : ArrayCollection;

    /**
     * @param int $sectionId
     *
     * @param int $countItems
     *
     * @return ArrayCollection
     */
    public function getItemsBySection(int $sectionId, int $countItems) : ArrayCollection;

    /**
     * @param int $sectionId
     *
     * @return ArrayCollection
     */
    public function getSectionsByCatalogSection(int $sectionId) : ArrayCollection;
}