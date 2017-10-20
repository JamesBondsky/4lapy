<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\App\Application;
use Bitrix\Main\SystemException;

class FourPawsUserComponent extends \CBitrixComponent
{
    const MODE_PROFILE = 0;
    
    const MODE_FORM    = 1;
    
    /** {@inheritdoc} */
    public function onPrepareComponentParams($params) : array
    {
        return $params;
    }
    
    /** {@inheritdoc} */
    public function executeComponent()
    {
        try {
            $this->_prepareResult();
            
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
     * @throws \Bitrix\Main\SystemException
     */
    protected function _prepareResult()
    {
        global $USER;
        
        $this->arResult['MODE'] = $USER->IsAuthorized() ? self::MODE_PROFILE : self::MODE_FORM;
        
        if ($this->arResult['MODE'] === self::MODE_FORM) {
            $this->_setAuthSocialServices();
        } else {
            $this->_setUser();
        }
        
        return $this;
    }
    
    /**
     * Set current user and user service
     *
     * @throws \Bitrix\Main\SystemException
     */
    protected function _setUser()
    {
        /**
         * @var \FourPaws\User\UserService $userService
         */
        try {
            $userService = Application::getInstance()->getContainer()->get('user.service');
    
            $this->arResult['userService'] = $userService;
            $this->arResult['user']        = $userService->getCurrentUser();
        } catch (\Exception $e) {
            throw new SystemException($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e);
        }
    }
    
    /**
     * @todo implement this
     */
    protected function _setAuthSocialServices() {
        $this->arResult['socialServices'] = [];
    }
}
