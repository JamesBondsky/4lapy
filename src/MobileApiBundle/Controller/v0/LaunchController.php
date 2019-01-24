<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\External\ManzanaService as AppManzanaService;
use FourPaws\UserBundle\Service\UserService as AppUserService;
use FourPaws\MobileApiBundle\Services\Api\UserService as ApiUserService;

class LaunchController extends FOSRestController
{
    /**
     * @var AppManzanaService
     */
    private $appManzanaService;

    /**
     * @var AppUserService
     */
    private $appUserService;

    /**
     * @var ApiUserService
     */
    private $apiUserService;

    public function __construct(
        AppManzanaService $appManzanaService,
        AppUserService $appUserService,
        ApiUserService $apiUserService
    )
    {
        $this->appManzanaService = $appManzanaService;
        $this->appUserService = $appUserService;
        $this->apiUserService = $apiUserService;
    }

    /**
     * @Rest\Get("/app_launch/")
     * @Rest\View()
     */
    public function launchAction()
    {
        $this->apiUserService->actualizeUserGroupsForApp();
        // toDo записываем в манзану наличие у юзера приложения и дату последнего входа в него

        /*
         * // log_(array('Кидаем в нужные группы', $userId));
		if ($cardNumber = $this->User['UF_DISC']) {
			// записываем в манзану наличие у юзера приложения и дату последнего входа в него
			try {
				ini_set('default_socket_timeout', 5);

				$oSoapClient = new \SoapClient(API_ML_WSDL, array(
					'trace' => 1,
					'exceptions'=> 1,
					"connection_timeout" => 5
				));

				$oAuth = $oSoapClient->Authenticate(array(
					'login' => API_ML_LOGIN,
					'password' => API_ML_PASSWORD,
					'ip' => IP_ADDRESS,
					'innerLogin' => 'mob_api',
				));
				$sessionId = $oAuth->AuthenticateResult->SessionId;
			} catch(Exception $e) {
			}

			if ($sessionId) {
				$oResponse = new \CDataXML();

				try {
					$oResponse->LoadString(
						$oSoapClient->Execute(array(
							'sessionId' => $sessionId,
							'contractName' => 'search_cards_by_number',
							'parameters' => array(
								array('Name' => 'cardnumber', 'Value' => $cardNumber),
							)
						))->ExecuteResult->Value
					);
					$contactId = $oResponse->SelectNodes('/Cards/Card/contactid')->textContent();
				} catch (Exception $e) {
				}
				// log_(array('К манзане вроде подключились', $contactId));
				if ($contactId) {
					$oDateTime = new \Bitrix\Main\Type\DateTime();

					try {
						$oSoapClient->Execute(array(
							'sessionId' => $sessionId,
							'contractName' => 'contact_update',
							'parameters' => array(
								array('Name' => 'contactid', 'Value' => $contactId),
								array('Name' => 'ff_mobile_app', 'Value' => 1),
								array('Name' => 'ff_mobile_app_date', 'Value' => $oDateTime->format('c')),
							)
						));
					} catch (Exception $e) {
					}
				}
				//тут будем запрашивать чеки по карте
				CModule::IncludeModule("iblock");
				$arLoadProductArray = Array(
					"IBLOCK_ID"      => 54,
					"NAME"           => $cardNumber,
					"CODE"           => $cardNumber,
					"ACTIVE"         => "Y",
					);

				$REQUEST_ID = $el->Add($arLoadProductArray);
				//!тут будем запрашивать чеки по карте
			}
		}
         */

        return (new Response())
            ->setData([]);
    }
}
