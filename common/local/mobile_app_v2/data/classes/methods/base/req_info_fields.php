<?
/**
* параметр fields запроса АПИ /info
*/
class req_info_fields extends \req_param
{
	public function isRequired()
	{
		return false;
	}

	public function getMap()
	{
		return array(
			'id' => 'ID',
			'type' => 'PAGE_TYPE',
			'date' => 'DATE_ACTIVE_FROM',
			'end_date' => 'DATE_ACTIVE_TO',
			'title' => 'NAME',
			'details' => 'PREVIEW_TEXT',
			'icon' => 'PREVIEW_PICTURE',
			'subitems' => 'SUB_ITEMS',
			'html' => 'DETAIL_TEXT',
			'web_url' => 'DETAIL_PAGE_URL',
		);
	}

	public function defaultValue()
	{
		return array_values($this->getMap());
	}

	public function convApiToBx($arParams)
	{
		if ($this->verify($arParams)) {
			$arResult = array();

			foreach (explode(',', $arParams['fields']) as $fieldName) {
				$fieldName = trim($fieldName);

				if ($this->getMap()[$fieldName]) {
					$arResult[] = $this->getMap()[$fieldName];
				}
			}

			return $arResult;
		}
	}

	public function verify($arParams)
	{
		return (
			isset($arParams['fields'])
			&& strlen($arParams['fields']) > 0
		);
	}
}