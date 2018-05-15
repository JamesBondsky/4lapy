<?php

namespace FourPaws\Components;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Application;
use FourPaws\App\MainTemplate;
use FourPaws\BitrixOrm\Model\IblockElement;
use FourPaws\BitrixOrm\Model\IblockSection;
use FourPaws\BitrixOrm\Query\IblockElementQuery;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Query\CategoryQuery;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsBreadCrumbs extends \CBitrixComponent
{
    public function onPrepareComponentParams($params): array
    {
        if (!isset($params['CACHE_TIME'])) {
            $params['CACHE_TIME'] = 36000000;
        }

        $params['SHOW_LINK_TO_MAIN'] = ($params['SHOW_LINK_TO_MAIN'] === 'N') ? 'N' : 'Y';

        if (!$params['IBLOCK_SECTION'] && !empty($params['SECTION_CODE'])) {
            $params['IBLOCK_SECTION'] = $this->getSection($params['SECTION_CODE']);
        }

        if (!$params['IBLOCK_ELEMENT'] && !empty($params['ELEMENT_CODE'])) {
            $params['IBLOCK_ID'] = $params['IBLOCK_ID'] ?? null;
            $params['IBLOCK_ELEMENT'] = $this->getElement($params['ELEMENT_CODE'], $params['IBLOCK_ID']);
        }

        $params['IBLOCK_SECTION'] = $params['IBLOCK_SECTION'] instanceof IblockSection ? $params['IBLOCK_SECTION'] : null;
        $params['IBLOCK_ELEMENT'] = $params['IBLOCK_ELEMENT'] instanceof IblockElement ? $params['IBLOCK_ELEMENT'] : null;

        /**
         * @var MainTemplate $template
         */
        $template = MainTemplate::getInstance(Application::getInstance()->getContext());
        $params['IS_CATALOG'] = $template->isCatalog();

        return parent::onPrepareComponentParams($params);
    }

    public function executeComponent()
    {
        try {
            $this->prepareResult();

            $this->includeComponentTemplate();
        } catch (\Exception $e) {
            try {
                $logger = LoggerFactory::create('component');
                $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            } catch (\RuntimeException $e) {
            }
        }
    }

    protected function prepareResult()
    {
        /** @var IblockElement $iblockElement */
        $iblockElement = $this->arParams['IBLOCK_ELEMENT'];
        /** @var IblockSection $iblockSection */
        $iblockSection = $this->arParams['IBLOCK_SECTION'];
        $skipId = null;
        if ($this->arParams['IBLOCK_ELEMENT']) {
            $iblockId = $iblockElement->getIblockId();
            $iblockSectionId = $iblockElement->getIblockSectionId();
        } elseif ($this->arParams['IBLOCK_SECTION']) {
            $iblockId = $iblockSection->getIblockId();
            $iblockSectionId = $iblockSection->getId();
            // не показываем сам раздел в крошках
            $skipId = $iblockSectionId;
        } else {
            throw new \InvalidArgumentException('Invalid component parameters');
        }

        $this->arResult['SECTIONS'] = [];
        $navChain = \CIBlockSection::GetNavChain($iblockId, $iblockSectionId);
        while ($item = $navChain->GetNext()) {
            if ($item['ID'] == $skipId) {
                continue;
            }
            $this->arResult['SECTIONS'][] = $item;
        }

        return $this;
    }

    /**
     * @param string $code
     * @param int $iblockId
     *
     * @return null|Product
     */
    protected function getElement(string $code, $iblockId = null)
    {
        return (new IblockElementQuery($iblockId))
            ->withFilterParameter('CODE', $code)
            ->exec()
            ->first();
    }

    /**
     * @param string $code
     *
     * @return null|IblockSection
     */
    protected function getSection(string $code)
    {
        return (new CategoryQuery())
            ->withFilterParameter('CODE', $code)
            ->exec()
            ->first();
    }
}
