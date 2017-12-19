<?php

namespace FourPaws\Components;

use FourPaws\BitrixOrm\Model\IblockSection;

class CatalogBreadCrumbs extends \CBitrixComponent
{
    public function onPrepareComponentParams($params): array
    {
        $params['IBLOCK_SECTION'] = $params['IBLOCK_SECTION'] instanceof IblockSection ? $params['IBLOCK_SECTION'] : null;

        return parent::onPrepareComponentParams($params);
    }

    public function executeComponent()
    {
//        if (!$this->arParams['IBLOCK_SECTION']) {
            /**
             * @todo log
             */
//            return;
//        }

        if ($this->startResultCache()) {
            parent::executeComponent();

            /**
             * @todo implement logic
             */
            $this->includeComponentTemplate();
        }
    }
}
