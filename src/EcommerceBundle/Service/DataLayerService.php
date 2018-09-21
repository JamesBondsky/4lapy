<?php

namespace FourPaws\EcommerceBundle\Service;

use FourPaws\EcommerceBundle\Dto\DataLayer\DataLayer;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;

/**
 * Class RetailRocketService
 *
 * @todo    add render configuration
 * @todo    add parter id into configuration
 *
 * @package FourPaws\EcommerceBundle\Service
 */
final class DataLayerService implements ScriptRenderedInterface, LoggerAwareInterface
{
    use InlineScriptRendererTrait;

    private const EVENT_FEEDBACK       = 'feedback_success';
    private const EVENT_CALLBACK       = 'callback_success';
    private const EVENT_AUTH           = 'authorization';
    private const EVENT_REGISTER       = 'registration';
    private const EVENT_FOOD_SELECTION = 'selection_food';
    private const EVENT_SOCIAL_BIND    = 'snap_social';
    private const EVENT_SORT           = 'sort_filter';
    private const EVENT_CATALOG_FILTER = 'catalog_filter';
    private const EVENT_SHOP_FILTER    = 'shop_filter';

    private const EVENT_CATEGORY_FEEDBACK       = 'feedback_success';
    private const EVENT_CATEGORY_CALLBACK       = 'callback_success';
    private const EVENT_CATEGORY_AUTH           = 'authorization';
    private const EVENT_CATEGORY_REGISTER       = 'registration';
    private const EVENT_CATEGORY_SOCIAL_BIND    = 'snap_social';
    private const EVENT_CATEGORY_SORT           = 'sort_filter';
    private const EVENT_CATEGORY_CATALOG_FILTER = 'catalog_filter';
    private const EVENT_CATEGORY_SHOP_FILTER    = 'shop_filter';

    /**
     * @param string $topic
     *
     * @return string
     *
     * @throws RuntimeException
     */
    public function renderFeedback(string $topic): string
    {
        return $this->renderScript(
            (new DataLayer())
                ->setEvent(self::EVENT_FEEDBACK)
                ->setEventCategory(self::EVENT_CATEGORY_FEEDBACK)
                ->setEventLabel($topic)
        );
    }

    /**
     * @return string
     *
     * @throws RuntimeException
     */
    public function renderCallback(): string
    {
        return $this->renderScript(
            (new DataLayer())
                ->setEvent(self::EVENT_CALLBACK)
                ->setEventCategory(self::EVENT_CATEGORY_CALLBACK)
        );
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public function renderAuth(string $type): string
    {
        return $this->renderScript(
            (new DataLayer())
                ->setEvent(self::EVENT_AUTH)
                ->setEventCategory(self::EVENT_CATEGORY_AUTH)
                ->setEventLabel($type)
        );
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public function renderRegister(string $type): string
    {
        return $this->renderScript(
            (new DataLayer())
                ->setEvent(self::EVENT_REGISTER)
                ->setEventCategory(self::EVENT_CATEGORY_REGISTER)
                ->setEventLabel($type)
        );
    }

    /**
     * @param string $petType
     * @param string $foodType
     * @param string $foodSpecification
     *
     * @return string
     */
    public function renderFoodSelection(string $petType, string $foodType, string $foodSpecification): string
    {
        return $this->renderScript(
            (new DataLayer())
                ->setEvent(self::EVENT_FOOD_SELECTION)
                ->setEventCategory($petType)
                ->setEventAction($foodType)
                ->setEventLabel($foodSpecification)
        );
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public function renderBindSocials(string $type): string
    {
        return $this->renderScript(
            (new DataLayer())
                ->setEvent(self::EVENT_SOCIAL_BIND)
                ->setEventCategory(self::EVENT_CATEGORY_SOCIAL_BIND)
                ->setEventLabel($type)
        );
    }

    /**
     * @param string $type
     * @param string $category
     *
     * @return string
     */
    public function renderSort(string $type, string $category): string
    {
        return $this->renderScript(
            (new DataLayer())
                ->setEvent(self::EVENT_SORT)
                ->setEventCategory(self::EVENT_CATEGORY_SORT)
                ->setEventAction($type)
                ->setEventLabel($category)
        );
    }

    /**
     * @param string $filterType
     * @param string $filterLabel
     *
     * @return string
     */
    public function renderCatalogFilter(string $filterType, string $filterLabel): string
    {
        return $this->renderScript(
            (new DataLayer())
                ->setEvent(self::EVENT_CATALOG_FILTER)
                ->setEventCategory(self::EVENT_CATEGORY_CATALOG_FILTER)
                ->setEventAction($filterType)
                ->setEventLabel($filterLabel)
        );
    }

    /**
     * @param string $city
     * @param string $filterSummary
     *
     * @return string
     */
    public function renderShopFilter(string $city, string $filterSummary): string
    {
        return $this->renderScript(
            (new DataLayer())
                ->setEvent(self::EVENT_SHOP_FILTER)
                ->setEventCategory(self::EVENT_CATEGORY_SHOP_FILTER)
                ->setEventAction($city)
                ->setEventLabel($filterSummary)
        );
    }
}
