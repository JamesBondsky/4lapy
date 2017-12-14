<?php

namespace FourPaws\Test\AppManager;

class ApplicationManager
{
    /**
     * @var CatalogHelper
     */
    private $catalogHelper;

    /**
     * @return CatalogHelper
     */
    public function getCatalogHelper(): CatalogHelper
    {
        if (is_null($this->catalogHelper)) {
            $this->catalogHelper = new CatalogHelper();
        }

        return $this->catalogHelper;
    }
}
