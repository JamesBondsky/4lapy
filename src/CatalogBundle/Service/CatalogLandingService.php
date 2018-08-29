<?php

namespace FourPaws\CatalogBundle\Service;

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
    public const IS_LANDING_REQUEST_KEY = 'landing';

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function isLanding(Request $request): bool
    {
        return $request->get(self::IS_LANDING_REQUEST_KEY, false) !== false;
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

        return $request->get(self::IS_LANDING_REQUEST_KEY, '');
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
}
