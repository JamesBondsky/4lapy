<?

use FourPaws\MobileApiOldBundle\Utils;

class captcha extends \APIServer
{
	// public $check_sign_post = false; // отключаем проверку хеша для метода post

	public function post($arInput)
	{
		$arResult = null;

		//
		if (!isset($arInput['entity']) || !strlen($arInput['entity'])
			|| !isset($arInput['sender']) || !in_array($arInput['sender'], array('user_registration', 'card_activation', 'edit_info'))
		) {
			$this->addError('required_params_missed');
		} else {
			$entity = $arInput['entity'];
			$sender = $arInput['sender'];
		}

		//
		if (!$this->hasErrors()) {
			\Bitrix\Main\Loader::includeModule('bxmod.auth');
			$oBxmodAuth = new \BxmodAuth;
			$typeEntity = $oBxmodAuth->CheckLoginType($entity);

			if ($typeEntity && in_array($typeEntity, array('phone', 'email'))) {
				$sendResult = false;

				if ($typeEntity == 'phone') {
					if ($sender == 'user_registration'
						|| ($sender == 'edit_info' && !($arUser = $oBxmodAuth->GetUserByPhone($entity)))
						|| ($sender == 'card_activation' && ($arUser = $oBxmodAuth->GetUserByPhone($entity)))
					) {
						$oCaptcha = new \captcha_base;
						$sendResult = Utils::SendImmediateSMS($entity, "Код подтверждения: {$oCaptcha->getCode()}");
					}
				} elseif (in_array($sender, array('edit_info', 'card_activation'))) {
					$arUser = $oBxmodAuth->GetUserByEmail($entity);

					if ($sender == 'edit_info' && $arUser) {
						$this->addError('captcha__email_is_used');
					} elseif ($sender == 'card_activation' && $arUser && $arUser['ID'] != $this->getUserId()) {
						$this->addError('captcha__email_is_used');
					}

					if (!$this->hasErrors()) {
						$oCaptcha = new \captcha_base;
						$sendResult = \Bitrix\Main\Mail\Event::sendImmediate(array(
							'EVENT_NAME' => 'SEND_VER_CODE_APP',
							'LID' => \Bitrix\Main\Application::getInstance()->getContext()->getSite(),
							'DUPLICATE' => 'N',
							'C_FIELDS' => array(
								'EMAIL_TO' => $entity,
								'VER_CODE' => $oCaptcha->getCode(),
							),
						));
						$sendResult = ($sendResult === \Bitrix\Main\Mail\Event::SEND_RESULT_SUCCESS);
					}
				}

				if (!$this->hasErrors()) {
					if ($sendResult) {
						if ($arUser) {
							$oUser = new \CUser;
	        		$oUser->Update($arUser['ID'], array('CONFIRM_CODE' => $oCaptcha->getCode()));
	        	}

						$arResult = array(
							'picture' => '',
							'captcha_id' => $oCaptcha->getSid(),
							'feedback_text' => 'Код подтверждения успешно отправлен',
						);
					} else {
						$this->addError('send_captcha_error');
					}
				}
			} else {
				$this->addError('bad_captcha_data');
			}
		}

		return $arResult;
	}
}
