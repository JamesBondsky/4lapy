<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\SystemException;
use FourPaws\App\Application;

class FourPawsAuthFormComponent extends \CBitrixComponent
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
        
        $this->arResult['MODE'] = $USER->IsAuthorized() ? self::MODE_PROFILE : self::MODE_FORM;
        
        if ($this->arResult['MODE'] === self::MODE_FORM) {
            $this->setAuthSocialServices();
        } else {
            $this->setUser();
        }
        
        return $this;
    }
    
    /**
     * Set current user and user service
     *
     * @throws SystemException
     */
    protected function setUser()
    {
        try {
            $userService = Application::getInstance()->getContainer()->get('user.service');
    
            $this->arResult['userService'] = $userService;
            $this->arResult['user']        = $userService->getCurrentUser();
        } catch (\Exception $e) {
            throw new SystemException($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e);
        }
        
        return $this;
    }
    
    /**
     * Set active social services
     *
     * @return $this
     */
    protected function setAuthSocialServices()
    {
        $this->arResult['socialServices'] = (new CSocServAuthManager())->GetActiveAuthServices([]);
    
        return $this;
    }
}
