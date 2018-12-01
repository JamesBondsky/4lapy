<?

class utils extends \stdClass
{
	public static function formatPhone($phone)
	{
		return substr(preg_replace('/\D/', '', $phone), -10);
	}
}