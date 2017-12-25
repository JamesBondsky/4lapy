<?php

namespace FourPaws\CatalogBundle\Dto;

use FourPaws\CatalogBundle\Collection\SortsCollection;
use FourPaws\Search\Model\Navigation;

interface CatalogSearchRequestInterface
{
    /**
     * @return SortsCollection
     */
    public function getSorts(): SortsCollection;

    /**
     * @param SortsCollection $sorts
     *
     * @return static
     */
    public function setSorts(SortsCollection $sorts): CatalogSearchRequestInterface;

    /**
     * @return Navigation
     */
    public function getNavigation(): Navigation;

    /**
     * @param Navigation $navigation
     *
     * @return static
     */
    public function setNavigation(Navigation $navigation): CatalogSearchRequestInterface;

    /**
     * @return string
     */
    public function getSearchString(): string;

    /**
     * @param string $searchString
     *
     * @return static
     */
    public function setSearchString(string $searchString): CatalogSearchRequestInterface;
}
