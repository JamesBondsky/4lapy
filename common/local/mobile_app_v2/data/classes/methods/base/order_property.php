<?
class order_property extends \stdClass
{
	private $fields;
	private $value;

	function __construct($argument)
	{
		if (is_array($argument)) {
			$this->fields = $argument;
		}
	}

	public function getField($fieldName)
	{
		return $this->fields[$fieldName];
	}

	public function getValue()
	{
		return $this->value;
	}

	public function setValue($propValue)
	{
		$this->value = $propValue;
	}
}