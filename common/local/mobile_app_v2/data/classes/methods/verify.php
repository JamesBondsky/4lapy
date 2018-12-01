<?
class verify extends \APIServer
{
	public function post($arInput)
	{
		$arResult = null;

		//
		if (!isset($arInput['entity']) || !strlen($arInput['entity'])
			|| !isset($arInput['captcha_id']) || !strlen($arInput['captcha_id'])
			|| !isset($arInput['captcha_value']) || !strlen($arInput['captcha_value'])
		) {
			$this->addError('required_params_missed');
		} else {
			$entity = $arInput['entity'];
			$captchaSid = $arInput['captcha_id'];
			$captchaCode = $arInput['captcha_value'];
		}

		//
		if (!$this->hasErrors()) {
			\Bitrix\Main\Loader::includeModule('bxmod.auth');
			$oBxmodAuth = new \BxmodAuth;
			$typeEntity = $oBxmodAuth->CheckLoginType($entity);

			if ($typeEntity && in_array($typeEntity, array('phone', 'email'))) {
				$sendResult = false;
				$oCaptcha = new \captcha_base;

				if (\captcha_base::checkCode($captchaCode, $captchaSid)) {
					$oCaptcha = new \captcha_base;

					$arResult = array(
						'captcha_id' => "{$oCaptcha->getCode()}:{$oCaptcha->getSid()}"
					);

					if ($typeEntity == 'phone') {
						$arResult['feedback_text'] = 'Номер телефона подтвержден';
					} else {
						$arResult['feedback_text'] = 'E-mail подтвержден';
					}
				} else {
					$this->addError('send_wrong_code');
				}
			} else {
				$this->addError('bad_captcha_data');
			}
		}

		return $arResult;
	}
}
