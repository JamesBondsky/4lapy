<?php

namespace FourPaws\EcommerceBundle\Dto\GoogleEcommerce;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;


/**
 * Class Action
 *
 * @package FourPaws\EcommerceBundle\Dto\GoogleEcommerce
 */
class Action
{
    /**
     * @Serializer\Type("ArrayCollection<FourPaws\EcommerceBundle\Dto\GoogleEcommerce\ActionField>")
     * @Serializer\SkipWhenEmpty()
     *
     * @var ActionField
     */
    protected $actionField;

    /**
     * @Serializer\Type("ArrayCollection<FourPaws\EcommerceBundle\Dto\GoogleEcommerce\Product>")
     * @Serializer\SkipWhenEmpty()
     *
     * @var ArrayCollection|Product[]
     */
    protected $products;

    /**
     * @return ActionField
     */
    public function getActionField(): ActionField
    {
        return $this->actionField;
    }

    /**
     * @param ActionField $actionField
     * @return Action
     */
    public function setActionField(ActionField $actionField): Action
    {
        $this->actionField = $actionField;

        return $this;
    }

    /**
     * @return ArrayCollection|Product[]
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param ArrayCollection|Product[] $products
     * @return Action
     */
    public function setProducts($products): Action
    {
        $this->products = $products;

        return $this;
    }
}
