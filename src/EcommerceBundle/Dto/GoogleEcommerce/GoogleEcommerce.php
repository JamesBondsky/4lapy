<?php

namespace FourPaws\EcommerceBundle\Dto\GoogleEcommerce;
use JMS\Serializer\Annotation as Serializer;


/**
 * Class GoogleEcommerce
 *
 * @package FourPaws\EcommerceBundle\Dto\GoogleEcommerce
 */
class GoogleEcommerce
{
    /**
     * @Serializer\Type("FourPaws\EcommerceBundle\Dto\GoogleEcommerce\Ecommerce")
     *
     * @var Ecommerce
     */
    protected $ecommerce;

    /**
     * @return Ecommerce
     */
    public function getEcommerce(): Ecommerce
    {
        return $this->ecommerce;
    }

    /**
     * @param Ecommerce $ecommerce
     *
     * @return GoogleEcommerce
     */
    public function setEcommerce(Ecommerce $ecommerce): GoogleEcommerce
    {
        $this->ecommerce = $ecommerce;

        return $this;
    }
}
