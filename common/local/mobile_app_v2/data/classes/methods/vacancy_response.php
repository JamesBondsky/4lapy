<?php
	class vacancy_response extends APIServer
	{
		protected $type='token';

		public function get($arInput){
			return($this->post($arInput));
		}

		public function post($arInput)
		{
			CModule::IncludeModule("iblock");

			$sVacancyID = -1;

			// проверяем существование ключей и формат
			if (array_key_exists('id', $arInput) and array_key_exists('phone', $arInput) and array_key_exists('email', $arInput) and array_key_exists('city_id', $arInput))
			{
				if (strlen($arInput['id']) > 0)
					$sVacancyID = $arInput['id'];
			}

			if ($sVacancyID > -1)
			{
				$oElement = new CIBlockElement;

				$arFields = array(
					'IBLOCK_ID' => CIBlockTools::GetIBlockId('resume'),
					'NAME' => ($this->User['NAME']) ? $this->User['NAME'] : 'Отклик из мобильного приложения '.htmlspecialchars($arInput['phone']),
					'ACTIVE' => 'Y',
					'PROPERTY_VALUES' => array(
						'EMAIL' => $arInput['email'],
						'PHONE' => $arInput['phone'],
						'IDS' => $sVacancyID,
						'CITY' => \city::convGeo2toGeo1($arInput['city_id']),
						'IS_MOBILE_APP' => CIBlockTools::GetPropertyEnumValueId('resume', 'IS_MOBILE_APP', 'Y'),
					),
					'DETAIL_TEXT' => (isset($arInput['text'])) ? htmlspecialchars($arInput['text']) : ''
				);

				if ($iResponseId = $oElement->Add($arFields))
					$arResult['feedback_text'] = 'Отклик на вакансию успешно добавлен';
				else
					$this->addError('add_response_error');
			}
			else
				$this->addError('required_params_missed');

			return($arResult);
		}
	}
?>