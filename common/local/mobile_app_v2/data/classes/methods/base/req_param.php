<?
/**
*
*/
abstract class req_param extends \dictionary
{
	protected $errors;

	abstract function convApiToBx($value);
	abstract function verify($value);
	abstract function isRequired();

	public function defaultValue()
	{
		return array();
	}

	function __construct($value)
	{
		if ($this->isRequired() && !$this->verify($value)) {
			$this->addError('required params missed');
		} elseif ($this->verify($value)) {
			parent::__construct($this->convApiToBx($value));
		} else {
			parent::__construct($this->defaultValue());
		}
	}

	public function hasErrors()
	{
		return (bool)count($this->errors);
	}

	public function addError($text)
	{
		$this->errors[] = $text;
	}
}