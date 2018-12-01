<?php
	class settings extends APIServer
	{
		protected $type = 'token';

		public function get($arInput)
		{
			if ($this->User['user_id'] > 0)
			{
				$arResult['settings'] = array(
					'interview_messaging_enabled' => ($this->User['UF_INTERVIEW_MES']) ? true : false,
					'bonus_messaging_enabled' => ($this->User['UF_BONUS_MES']) ? true : false,
					'feedback_messaging_enabled' => ($this->User['UF_FEEDBACK_MES']) ? true : false,
					'push_order_status' => ($this->User['UF_PUSH_ORD_STAT']) ? true : false,
					'push_news' => ($this->User['UF_PUSH_NEWS']) ? true : false,
					'push_account_change' => ($this->User['UF_PUSH_ACC_CHANGE']) ? true : false,
					'sms_messaging_enabled' => ($this->User['UF_SMS_MES']) ? true : false,
					'email_messaging_enabled' => ($this->User['UF_EMAIL_MES']) ? true : false,
					'gps_messaging_enabled' => ($this->User['UF_GPS_MESS']) ? true : false,
				);
			}
			else
				$this->res['errors']+=$this->ERROR['user_not_authorized'];

			return $arResult;
		}

		public function post($arInput)
		{
			if (!empty($arInput['settings'])) {
				if ($this->User['user_id'] > 0) {
					$arFields = array(
						'UF_INTERVIEW_MES' => (int)(isset($arInput['settings']['interview_messaging_enabled'])
							? filter_var($arInput['settings']['interview_messaging_enabled'], FILTER_VALIDATE_BOOLEAN)
							: $this->User['UF_INTERVIEW_MES']
						),
						'UF_BONUS_MES' => (int)(isset($arInput['settings']['bonus_messaging_enabled'])
							? filter_var($arInput['settings']['bonus_messaging_enabled'], FILTER_VALIDATE_BOOLEAN)
							: $this->User['UF_BONUS_MES']
						),
						'UF_FEEDBACK_MES' => (int)(isset($arInput['settings']['feedback_messaging_enabled'])
							? filter_var($arInput['settings']['feedback_messaging_enabled'], FILTER_VALIDATE_BOOLEAN)
							: $this->User['UF_FEEDBACK_MES']
						),
						'UF_SMS_MES' => (int)(isset($arInput['settings']['sms_messaging_enabled'])
							? filter_var($arInput['settings']['sms_messaging_enabled'], FILTER_VALIDATE_BOOLEAN)
							: $this->User['UF_SMS_MES']
						),
						'UF_EMAIL_MES' => (int)(isset($arInput['settings']['email_messaging_enabled'])
							? filter_var($arInput['settings']['email_messaging_enabled'], FILTER_VALIDATE_BOOLEAN)
							: $this->User['UF_EMAIL_MES']
						),
						'UF_GPS_MESS' => (int)(isset($arInput['settings']['gps_messaging_enabled'])
							? filter_var($arInput['settings']['gps_messaging_enabled'], FILTER_VALIDATE_BOOLEAN)
							: $this->User['UF_GPS_MESS']
						),
						'UF_PUSH_ORD_STAT' => (int)(isset($arInput['settings']['push_order_status'])
							? filter_var($arInput['settings']['push_order_status'], FILTER_VALIDATE_BOOLEAN)
							: $this->User['UF_PUSH_ORD_STAT']
						),
						'UF_PUSH_NEWS' => (int)(isset($arInput['settings']['push_news'])
							? filter_var($arInput['settings']['push_news'], FILTER_VALIDATE_BOOLEAN)
							: $this->User['UF_PUSH_NEWS']
						),
						'UF_PUSH_ACC_CHANGE' => (int)(isset($arInput['settings']['push_account_change'])
							? filter_var($arInput['settings']['push_account_change'], FILTER_VALIDATE_BOOLEAN)
							: $this->User['UF_PUSH_ACC_CHANGE']
						)
					);

					if ($GLOBALS['USER_FIELD_MANAGER']->Update('USER', $this->User['user_id'], $arFields)) {
						$arResult['feedback_text'] = 'Настройки приложения успешно сохранены';
					} else {
						$this->res['errors']+=$this->ERROR['settings_update_error'];
					}
				} else {
					$this->res['errors']+=$this->ERROR['user_not_authorized'];
				}
			} else {
				$this->res['errors']+=$this->ERROR['required_params_missed'];
			}

			return $arResult;
		}
	}
?>