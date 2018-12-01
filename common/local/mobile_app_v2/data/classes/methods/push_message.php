<?
class push_message extends \APIServer
{
	function post($arInput)
	{
		$arResult = null;

		$token = $arInput['token'];
		$platform = $arInput['platform'];
		$pushToken = $arInput['push_token'];

		if (!$token || !$platform || !$pushToken) {
			$this->addError('required_params_missed');
		}

		if (!$this->hasErrors()) {
			if ($this->getPushParams()['platform'] != $platform || $this->getPushParams()['token'] != $pushToken) {
				$this->setPushParams(array(
					'platform' => $platform,
					'token' => $pushToken,
				));
			}
		}

		return $arResult;
	}
}