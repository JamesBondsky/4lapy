<?
class captcha_base extends \stdClass
{
	private $captcha;

	function __construct()
	{
		include_once(\Bitrix\Main\Application::getDocumentRoot().'/bitrix/modules/main/classes/general/captcha.php');
		$this->captcha = new \CCaptcha();
		$this->captcha->SetCode();
	}

	public function getCode()
	{
		return $this->captcha->code;
	}

	public function getSid()
	{
		return $this->captcha->sid;
	}

	public function getImage()
	{
		$httpHost = \Bitrix\Main\Application::getInstance()->getContext()->getServer()->get('HTTP_HOST');
		return "http://{$httpHost}/bitrix/tools/captcha.php?captcha_sid={$this->captcha->sid}";
	}

	public static function checkCode($code, $sid)
	{
		return $GLOBALS['APPLICATION']->CaptchaCheckCode($code, $sid);
	}
}