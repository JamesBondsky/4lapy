<?php

namespace FourPaws\CatalogBundle\Dto;

use FourPaws\CatalogBundle\Collection\SortsCollection;
use FourPaws\Search\Model\Navigation;
use Symfony\Component\Validator\Constraints as Assert;

abstract class AbstractCatalogRequest implements CatalogSearchRequestInterface
{
    /**
     * @Assert\NotBlank()
     * @Assert\Valid()
     * @var Navigation
     */
    protected $navigation;

    /**
     * @Assert\Expression(
     *     "this.getSorts().count() > 0"
     * )
     * @var SortsCollection
     */
    protected $sorts;

    /**
     * @Assert\Length(min="0", max="100")
     * @var string
     */
    protected $searchString = '';

    /**
     * @return SortsCollection
     */
    public function getSorts(): SortsCollection
    {
        return $this->sorts;
    }

    /**
     * @param SortsCollection $sorts
     *
     * @return static
     */
    public function setSorts(SortsCollection $sorts): CatalogSearchRequestInterface
    {
        $this->sorts = $sorts;
        return $this;
    }

    /**
     * @return Navigation
     */
    public function getNavigation(): Navigation
    {
        return $this->navigation;
    }

    /**
     * @param Navigation $navigation
     *
     * @return static
     */
    public function setNavigation(Navigation $navigation): CatalogSearchRequestInterface
    {
        $this->navigation = $navigation;
        return $this;
    }


    /**
     * @return string
     */
    public function getSearchString(): string
    {
        return $this->searchString;
    }

    /**
     * @param string $searchString
     *
     * @return static
     */
    public function setSearchString(string $searchString): CatalogSearchRequestInterface
    {
        $this->searchString = $searchString;
        return $this;
    }
}
