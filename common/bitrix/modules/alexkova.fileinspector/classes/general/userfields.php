<?
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);


class CAFIUserFields{

	public static function getAllFileSectionProps(){

		$returnResult = array();

		$rsData = CUserTypeEntity::GetList(array(), array("USER_TYPE_ID"=>"file"));
		while ($FIELDS = $rsData->Fetch())
		{
			if (substr_count($FIELDS["ENTITY_ID"], "_SECTION")>0){
				$returnResult[] = $FIELDS;
			}
		}

		//echo "<pre>"; print_r($returnResult); echo "</pre>"; die();

		return $returnResult;

	}

}