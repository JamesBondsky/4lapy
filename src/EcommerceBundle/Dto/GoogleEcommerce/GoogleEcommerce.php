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
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $event;

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

    /**
     * @return string
     */
    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @param string $event
     *
     * @return $this
     */
    public function setEvent(string $event): self
    {
        $this->event = $event;

        return $this;
    }
}
