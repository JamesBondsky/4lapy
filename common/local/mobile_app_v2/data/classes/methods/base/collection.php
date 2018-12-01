<?
/**
*
*/
class collection extends \Bitrix\Main\Type\Dictionary
{
	public function addItem($name = null, $item)
	{
		parent::offsetSet($name, $item);
	}
}