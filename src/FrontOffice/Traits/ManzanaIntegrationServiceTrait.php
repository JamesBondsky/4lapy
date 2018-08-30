<?php

namespace FourPaws\FrontOffice\Traits;

use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\FrontOffice\ManzanaIntegrationService;

trait ManzanaIntegrationServiceTrait
{
    /** @var ManzanaIntegrationService $manzanaIntegrationService */
    private $manzanaIntegrationService;

    /**
     * @return ManzanaIntegrationService
     * @throws ApplicationCreateException
     */
    protected function getManzanaIntegrationService()
    {
        if (!$this->manzanaIntegrationService) {
            $this->manzanaIntegrationService = Application::getInstance()->getContainer()->get(
                'front_office.manzana_integration.service'
            );
        }

        return $this->manzanaIntegrationService;
    }
}
