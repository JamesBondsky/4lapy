<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\App;

/**
 * Class MainTemplate
 *
 * Класс для основного шаблона
 *
 * @package FourPaws\App
 */
class MainTemplate extends TemplateAbstract
{
    /**
     * @return string
     */
    public function getIndexMainClass(): string
    {
        return $this->isIndex() ? ' b-wrapper--main' : '';
    }

    /**
     * @return string
     */
    public function getWrapperClass(): string
    {
        return $this->isOrderPage() ? ' b-page-wrapper--order ' : '';
    }

    /**
     * @return string
     */
    public function getHeaderClass(): string
    {
        return $this->hasShortHeaderFooter() ? ' b-header--short ' : '';
    }

    /**
     * @return string
     */
    public function getFooterClass(): string
    {
        return $this->hasShortHeaderFooter() ? ' b-footer--short ' : '';
    }

    /**
     * @return bool
     */
    public function isIndex(): bool
    {
        return $this->isPage('/');
    }

    /**
     * Страница 404
     *
     * @return bool
     */
    public function is404(): bool
    {
        return \defined('ERROR_404') && ERROR_404 === 'Y';
    }

    /**
     * Страница, недоступная для неавторизованных
     *
     * @return bool
     */
    public function isForbidden(): bool
    {
        /**
         * It's bitrix way
         */
        global $USER;

        return \defined('NEED_AUTH') && NEED_AUTH === true && !$USER->IsAuthorized();
    }

    /**
     * @return bool
     */
    public function hasHeaderPublicationListContainer(): bool
    {
        return $this->isListNews() || $this->isListArticles() || $this->isListShares() || $this->isListSharesFilter();
    }

    /**
     * @return bool
     */
    public function hasShortHeaderFooter(): bool
    {
        return $this->isOrderPage() || $this->isPaymentPage();
    }

    /**
     * @return bool
     */
    public function isNews(): bool
    {
        return $this->isPartitionDir('/services/news');
    }

    /**
     * @return bool
     */
    public function isListNews(): bool
    {
        return $this->isDir('/services/news');
    }

    /**
     * @return bool
     */
    public function isArticles(): bool
    {
        return $this->isPartitionDir('/services/articles');
    }

    /**
     * @return bool
     */
    public function isListArticles(): bool
    {
        return $this->isDir('/services/articles');
    }

    /**
     * @return bool
     */
    public function isShares(): bool
    {
        return $this->isPartitionDir('/customer/shares');
    }

    /**
     * @return bool
     */
    public function isPublications(): bool
    {
        return $this->isShares() || $this->isNews() || $this->isArticles();
    }

    /**
     * @return bool
     */
    public function isCatalog(): bool
    {
        return $this->isPartitionDir('/catalog');
    }

    /**
     * return bool
     */
    public function isCatalogDetail(): bool
    {
        return $this->isCatalog() && $this->isPartitionPage('.html');
    }

    /**
     * @return bool
     */
    public function isListShares(): bool
    {
        return $this->isDir('/customer/shares');
    }

    /**
     * @return bool
     */
    public function isListSharesFilter(): bool
    {
        return $this->isPartitionDir('/customer/shares/by_pet');
    }

    /**
     * @return bool
     */
    public function hasHeaderDetailPageContainer(): bool
    {
        return $this->isDetailNews() || $this->isDetailArticles() || $this->isDetailShares();
    }

    /**
     * @return bool
     */
    public function isDetailNews(): bool
    {
        return $this->isPartitionDir('/services/news');
    }

    /**
     * @return bool
     */
    public function isDetailArticles(): bool
    {
        return $this->isPartitionDir('/services/articles');
    }

    /**
     * @return bool
     */
    public function isDetailShares(): bool
    {
        return $this->isPartitionDir('/customer/shares') && !$this->isListShares() && !$this->isListSharesFilter();
    }

    /**
     * @return bool
     */
    public function hasHeaderPersonalContainer(): bool
    {
        return ($this->isPersonalDirectory() || $this->isPersonal()) && !$this->isRegister()
            && !$this->isForgotPassword();
    }

    /**
     * @return bool
     */
    public function isPersonalDirectory(): bool
    {
        return $this->isPartitionDir('/personal');
    }

    /**
     * @return bool
     */
    public function isPersonal(): bool
    {
        return $this->isDir('/personal');
    }

    /**
     * @return bool
     */
    public function isRegister(): bool
    {
        return $this->isDir('/personal/register');
    }

    /**
     * @return bool
     */
    public function isForgotPassword(): bool
    {
        return $this->isDir('/personal/forgot-password');
    }

    /**
     * @return bool
     */
    public function hasHeaderBlockShopList(): bool
    {
        return $this->isShopList();
    }

    /**
     * @return bool
     */
    public function isShopList(): bool
    {
        return $this->isDir('/company/shops');
    }

    /**
     * @return bool
     */
    public function hasPersonalProfile(): bool
    {
        return $this->isPersonal();
    }

    /**
     * @return bool
     */
    public function hasPersonalAddress(): bool
    {
        return $this->isPersonalAddress();
    }

    /**
     * @return bool
     */
    public function isPersonalAddress(): bool
    {
        return $this->isDir('/personal/address');
    }

    /**
     * @return bool
     */
    public function hasPersonalPet(): bool
    {
        return $this->isPersonalPet();
    }

    /**
     * @return bool
     */
    public function isPersonalPet(): bool
    {
        return $this->isDir('/personal/pets');
    }

    /**
     * @return bool
     */
    public function hasPersonalReferral(): bool
    {
        return $this->isPersonalReferral();
    }

    /**
     * @return bool
     */
    public function isPersonalReferral(): bool
    {
        return $this->isDir('/personal/referral');
    }

    /**
     * @return bool
     */
    public function isOrderPage(): bool
    {
        return $this->isDir('/sale/order') || $this->isPartitionDir('/sale/order');
    }

    /**
     * @return bool
     */
    public function isPaymentPage(): bool
    {
        return $this->isDir('/sale/payment') || $this->isPartitionDir('/sale/payment');
    }

    /**
     * @return bool
     */
    public function isOrderDeliveryPage(): bool
    {
        return $this->isDir('/sale/order/delivery');
    }

    /**
     * @return bool
     */
    public function hasOrderDeliveryPage(): bool
    {
        return $this->isOrderDeliveryPage();
    }

    /**
     * Нет основного враппера
     *
     * @return bool
     */
    public function hasMainWrapper(): bool
    {
        return !$this->isForbidden() && !$this->is404();
    }

    /**
     * @return bool
     */
    public function isFeedback(): bool
    {
        return $this->isPage('/company/feedback');
    }

    /**
     * @return bool
     */
    public function isPaymentAndDelivery(): bool
    {
        return $this->isPage('/customer/payment-and-delivery/');
    }

    /**
     * @return bool
     */
    public function hasContent(): bool
    {
        return !$this->isPersonal() && !$this->isIndex() && !$this->isOrderPage() && !$this->isPersonalDirectory()
            && !$this->isShopList()
            && !$this->isListShares()
            && !$this->is404()
            && !$this->isCatalog()
            && !$this->isPublications()
            && !$this->isPaymentAndDelivery()
            && !$this->isFeedback();
    }

    /**
     * @return bool
     */
    public function isBasket(): bool
    {
        return $this->isDir('/cart');
    }

    /**
     * @return bool
     */
    public function hasFastOrder(): bool
    {
        return $this->isCatalogDetail() || $this->isBasket();
    }
}
