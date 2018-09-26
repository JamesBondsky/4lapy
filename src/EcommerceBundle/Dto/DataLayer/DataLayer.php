<?php

namespace FourPaws\EcommerceBundle\Dto\DataLayer;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class DataLayer
 *
 * @package FourPaws\EcommerceBundle\Dto\DataLayer
 */
class DataLayer
{
    /**
     * Событие
     *
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $event;
    /**
     * Категория(тип) события
     *
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $eventCategory;
    /**
     * Action события
     *
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $eventAction;
    /**
     * Метка события
     *
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $eventLabel;

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
     * @return DataLayer
     */
    public function setEvent(string $event): DataLayer
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventCategory(): string
    {
        return $this->eventCategory;
    }

    /**
     * @param string $eventCategory
     *
     * @return DataLayer
     */
    public function setEventCategory(string $eventCategory): DataLayer
    {
        $this->eventCategory = $eventCategory;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventAction(): string
    {
        return $this->eventAction;
    }

    /**
     * @param string $eventAction
     *
     * @return DataLayer
     */
    public function setEventAction(string $eventAction): DataLayer
    {
        $this->eventAction = $eventAction;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventLabel(): string
    {
        return $this->eventLabel;
    }

    /**
     * @param string $eventLabel
     *
     * @return DataLayer
     */
    public function setEventLabel(string $eventLabel): DataLayer
    {
        $this->eventLabel = $eventLabel;

        return $this;
    }
}
