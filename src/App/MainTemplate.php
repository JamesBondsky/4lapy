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
        return $this->isOrderPage() ? 'b-page-wrapper--order' : '';
    }

    /**
     * @return string
     */
    public function getHeaderClass(): string
    {
        return $this->isShortHeaderFooter() ? 'b-header--short' : '';
    }

    /**
     * @return string
     */
    public function getFooterClass(): string
    {
        return $this->isShortHeaderFooter() ? 'b-footer--short' : '';
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
    public function isShortHeaderFooter(): bool
    {
        return $this->isOrderPage();
    }

    /**
     * @return bool
     */
    public function isListNews(): bool
    {
        return $this->isDir('/company/news');
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
        return $this->isPartitionDir('/company/news');
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

    public function isOrderPage(): bool
    {
        return $this->isPartitionDir('/sale/order');
    }

    /**
     * Нет основного враппера
     *
     * @return bool
     */
    public function hasMainWrapper()
    {
        return !$this->isForbidden() && !$this->is404();
    }
}
