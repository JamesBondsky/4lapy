<?php

namespace FourPaws\LandingBundle\Service;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Bitrix\Main\Context;
use Bitrix\Main\UserTable;

/**
 * Class ActionLanding
 * @package FourPaws\LandingBundle\Service
 */
class ActionLanding extends Controller
{
    /**
     * @var array|string|null
     */
    private $disposableToken = '';
    
    public function __construct()
    {
        $request               = Context::getCurrent()->getRequest();
        $this->disposableToken = $request->get("disposableToken");
    }
    
    public function auth()
    {
        global $USER;
        
        if (!$USER->IsAuthorized() && !empty($this->disposableToken)) {
            try {
                $userId = UserTable::query()
                    ->setSelect(['ID'])
                    ->setFilter(['=UF_DISPONSABLE_TOKEN' => $this->disposableToken])
                    ->exec()
                    ->fetch()['ID'];
                
                $USER->Authorize($userId);
            } catch (\Exception $e) {
            
            }
        }
    }
}
