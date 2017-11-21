<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\SystemException;
use FourPaws\App\Application;

class FourPawsExpertsenderFormComponent extends \CBitrixComponent
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
        try {
            $userService            = Application::getInstance()->getContainer()->get('user.service');
            $this->arResult['user'] = $userService->getCurrentUser();
        } catch (\Exception $e) {
            throw new SystemException($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e);
        }
        
        return $this;
    }
}
