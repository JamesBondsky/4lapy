<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\SystemException;
use FourPaws\App\Application;

class FourPawsCitySelectorComponent extends \CBitrixComponent
{

    /** {@inheritdoc} */
    public function onPrepareComponentParams($params) : array
    {
        return $params;
    }

    /** {@inheritdoc} */
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

    /**
     * @return $this
     *
     * @throws SystemException
     */
    protected function prepareResult()
    {
        global $USER;
        $userService = Application::getInstance()->getContainer()->get('user.service');
        $availableCities = $userService->getAvailableCities();

        $this->arResult['POPULAR_CITIES'] = isset($availableCities['POPULAR']) ? $availableCities['POPULAR'] : [];
        $this->arResult['MOSCOW_CITIES']  = isset($availableCities['MOSCOW']) ? $availableCities['MOSCOW'] : [];
        $this->arResult['DEFAULT_CITY']   = reset($availableCities['DEFAULT']);

        $this->arResult['SELECTED_CITY'] = $this->arResult['DEFAULT_CITY'];
        if ($USER->isAuthorized()) {
            /* @todo set user selected city */
        }

        return $this;
    }
}
