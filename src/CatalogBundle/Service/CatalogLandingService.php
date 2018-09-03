<?php

namespace FourPaws\CatalogBundle\Service;

use Bitrix\Main\Application;
use FourPaws\App\Application as PawsApplication;
use FourPaws\CatalogBundle\Dto\RootCategoryRequest;
use FourPaws\CatalogBundle\Exception\LandingIsNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LandingService
 *
 * @package FourPaws\CatalogBundle\Service
 */
class CatalogLandingService
{
    public const        LANDING_REQUEST_KEY = 'landing';
    public const        LANDING_DOCROOT_KEY = 'HTTP_LANDING_DOCROOT';

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function isLanding(Request $request): bool
    {
        return $request->get(self::LANDING_REQUEST_KEY, false) !== false;
    }

    /**
     * @param Request $request
     *
     * @return string
     *
     * @throws LandingIsNotFoundException
     */
    public function getLandingName(Request $request): string
    {
        if (!$this->isLanding($request)) {
            throw new LandingIsNotFoundException('Landing is not defined');
        }

        return $request->get(self::LANDING_REQUEST_KEY, '');
    }

    /**
     * @param RootCategoryRequest $rootCategoryRequest
     *
     * @return string
     */
    public function getLandingDefaultPage(RootCategoryRequest $rootCategoryRequest): string
    {
        return \sprintf(
            '%s/',
            $rootCategoryRequest->getLanding()
                ->getCode()
        );
    }

    /**
     * @param Request $request
     *
     * @return string
     *
     * @throws LandingIsNotFoundException
     */
    public function getLandingDomain(Request $request): string
    {
        return \sprintf(
            'https://%s',
            $this->getLandingName($request)
        );
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    public function getLandingDocRoot(Request $request): string
    {
        return $request->server->get(self::LANDING_DOCROOT_KEY);
    }

    /**
     * @todo hardcode
     */
    protected static function getContext()
    {
        static $context;

        if (!$context) {
            $context = Application::getInstance()
                ->getContext();
        }

        return $context;
    }

    /**
     * @todo hardcode
     *
     * @return string
     */
    public static function getBackLink(): string {
        $referer = self::getContext()->getServer()->get('HTTP_REFERER');

        return $referer ?: PawsApplication::getInstance()->getSiteCurrentDomain();
    }

    /**
     * @todo hardcode
     *
     * @return bool
     */
    public static function isLandingPage(): bool
    {
        try {
            $isLanding = self::getContext()->getRequest()->get(self::LANDING_REQUEST_KEY);
        } catch (\Throwable $e) {
            $isLanding = false;
        }

        return (bool)$isLanding;
    }

    /**
     * @param string  $data
     * @param Request $request
     *
     * @return string
     */
    public function replaceLinksToLanding(string $data, Request $request): string
    {
        return $this->isLanding($request)
            ? \preg_replace(
                \sprintf(
                    '~%s~',
                    $this->getLandingDocRoot($request)
                ),
                $this->getLandingDomain($request),
                $data
            )
            : $data;
    }
}
