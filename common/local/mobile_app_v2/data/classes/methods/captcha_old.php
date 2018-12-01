<?php
	class captcha extends APIServer
	{
		protected $type='token';

		public function get($arInput)
		{
			return($this->post($arInput));
		}

		public function post($arInput)
		{
			include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");
			$oCaptcha = new CCaptcha();
			$oCaptcha->SetCode();
			$sCaptchaId = $oCaptcha->GetSID();
			$sCaptchaCode = $oCaptcha->code;

			// проверяем существование ключей и формат
			if (array_key_exists('entity', $arInput) and !empty($arInput['entity']))
			{
				CModule::IncludeModule("bxmod.auth");
				$oBxmodAuth = new BxmodAuth;

				//проверяем чем являются пришедшие данные
				$validateRes = $oBxmodAuth->CheckLoginType($arInput['entity']);

				if ($validateRes)
				{
					//если номер телефона, то отправляем СМС (если пользователь существует)
					if ($validateRes == 'phone')
					{
						$arUser = $oBxmodAuth->GetUserByEmail($arInput['entity']);

						if ($arUser)
						{
							$sPhoneNum = $oBxmodAuth->CheckPhone($arInput['entity']);
							$bSendRes = MyCUtils::SendImmediateSMS($arInput['entity'], 'Код подтверждения: '.$oCaptcha->code);
						}
						else
							$this->addError('user_not_found');
					}
					
					//если email, то отправляем письмо (если пользователь существует)
					if ($validateRes == 'email')
					{
						$arUser = $oBxmodAuth->GetUserByEmail($arInput['entity']);

						if ($arUser)
						{
							$arSendFields = array(
								'EMAIL_TO' => $arInput['entity'],
								'VER_CODE' => $sCaptchaCode
							);

							//отправляем письмо
							$event = new CEvent;
							$bSendRes = $event->Send("SEND_VER_CODE_APP", SITE_ID, $arSendFields);
							$event->CheckEvents();
						}
						else
							$this->addError('user_not_found');
					}

					if ($bSendRes)
					{
						$oUser = new CUser;
                		$oUser->Update($arUser["ID"], array("CONFIRM_CODE" =>  $sCaptchaCode));

						$arResult = array(
							'picture_url' => (empty($arInput['entity'])) ? 'http://'.SITE_SERVER_NAME_API.'/bitrix/tools/captcha.php?captcha_sid='.$sCaptchaId : '' ,
							'captcha_id' => $sCaptchaId,
							'feedback_text' => 'Код подтверждения успешно отправлен',
						);
					}
					else
						$this->addError('send_captcha_error');
				}
				else
					$this->addError('bad_captcha_data');
			}
			else
			{
				$arResult = array(
					'picture_url' => 'http://'.SITE_SERVER_NAME_API.'/bitrix/tools/captcha.php?captcha_sid='.$sCaptchaId,
					'captcha_id' => $sCaptchaId,
					'feedback_text' => 'Код подтверждения успешно отправлен'
				);
			}

			return($arResult);
		}
	}
?>