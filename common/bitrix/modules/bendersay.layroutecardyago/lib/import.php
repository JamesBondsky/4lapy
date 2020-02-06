<?php

namespace Bendersay\Exportimport;
use \Bitrix\Main\Localization\Loc;

/**
 * Description of import
 *
 * @author Asayants
 */
class Import {
	
	public function __construct() {
		Loc::loadMessages(__FILE__); 
		if (!\Bitrix\Main\Loader::includeModule('highloadblock')) {
			throw new SystemException(Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_HIGHLOADBLOCK'));
		}
		
	}
	
}
