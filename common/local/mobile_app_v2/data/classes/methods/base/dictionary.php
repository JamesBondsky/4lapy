<?
/**
*
*/
abstract class dictionary extends \Bitrix\Main\Type\Dictionary
{
	public function getField($name)
	{
		return parent::offsetGet($name);
	}

	public function getFields()
	{
		return parent::toArray();
	}

	public function setField($name, $value)
	{
		parent::offsetSet($name, $value);
	}
}