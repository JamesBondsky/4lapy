<?php

namespace FourPaws\EcommerceBundle\Service;

use FourPaws\EcommerceBundle\Dto\RetailRocket\Transaction;
use RuntimeException;

/**
 * Class RetailRocketService
 *
 * @todo    add render configuration
 * @todo    add parter id into configuration
 *
 * @package FourPaws\EcommerceBundle\Service
 */
final class RetailRocketService implements ScriptRenderedInterface
{
    use InlineScriptRendererTrait;

    public const METHOD_DETAIL_VIEW   = 'view';
    public const METHOD_CATEGORY_VIEW = 'categoryView';
    public const METHOD_EMAIL_SET     = 'setEmail';
    public const METHOD_ORDER         = 'order';
    public const METHOD_ADD_TO_BASKET = 'addToBasket';

    /**
     * @return string
     *
     * @throws RuntimeException
     */
    public function renderTracking(): string
    {
        /**
         * @todo to config
         */
        $partnerId = '5b151eb597a528b658db601e';

        return \trim($this->renderer->render('EcommerceBundle:RetailRocket:tracking.inline.php', \compact('partnerId')));
    }

    /**
     * @param string $action
     * @param string $value
     * @param bool   $isPush
     *
     * @return string
     *
     * @throws RuntimeException
     */
    public function renderTrackAction(string $action, string $value, bool $isPush = true): string
    {
        return \trim($this->renderer->render('EcommerceBundle:RetailRocket:action.inline.php', \compact('action', 'value', 'isPush')));
    }

    /**
     * @param string $email
     *
     * @return string
     *
     * @throws RuntimeException
     */
    public function renderSendEmail(string $email): string
    {
        return $this->renderTrackAction(self::METHOD_EMAIL_SET, $email);
    }

    /**
     * @param string $xmlId
     *
     * @return string
     *
     * @throws RuntimeException
     */
    public function renderDetailView(string $xmlId): string
    {
        return $this->renderTrackAction(self::METHOD_DETAIL_VIEW, $xmlId);
    }

    /**
     * @param string $xmlId
     *
     * @return string
     *
     * @throws RuntimeException
     */
    public function renderAddToBasket(string $xmlId): string
    {
        return $this->renderTrackAction(self::METHOD_ADD_TO_BASKET, $xmlId, false);
    }

    /**
     * @param string $categoryId
     *
     * @return string
     *
     * @throws RuntimeException
     */
    public function renderCategoryView(string $categoryId): string
    {
        return $this->renderTrackAction(self::METHOD_CATEGORY_VIEW, $categoryId);
    }

    /**
     * @param Transaction $transaction
     *
     * @return string
     *
     * @throws RuntimeException
     */
    public function renderOrderTransaction(Transaction $transaction): string
    {
        return $this->renderTrackAction(self::METHOD_ORDER, $this->serializer->serialize($transaction, 'json'));
    }
}
