<?
abstract class message_base extends \Bitrix\Main\Error
{
	function __construct($messageCode)
	{
		if ($messageCode && isset($this->getMessages()[$messageCode])) {
			parent::__construct($this->getMessages()[$messageCode], $messageCode);
		} else {
			return null;
		}
	}

	abstract function getMessages();
}