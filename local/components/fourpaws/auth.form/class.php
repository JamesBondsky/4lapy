<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;

class FourPawsUserComponent extends \CBitrixComponent
{
    
    protected $requiredModules = [];
    
    /** {@inheritdoc} */
    public function onPrepareComponentParams($params) : array
    {
        return $params;
    }
    
    /** {@inheritdoc} */
    public function onIncludeComponentLang()
    {
        $this->includeComponentLang();
        Loc::loadMessages(__FILE__);
    }
    
    /** {@inheritdoc} */
    public function executeComponent()
    {
        try {
            $this->prepareResult();
            
            $this->includeComponentTemplate();
        } catch (SystemException $e) {
        
        }
    }
    
    /**
     * @return $this
     */
    protected function prepareResult()
    {
        return $this;
    }
}