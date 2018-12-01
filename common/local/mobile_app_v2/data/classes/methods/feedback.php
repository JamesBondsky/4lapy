<?php
	class feedback extends APIServer
	{
		protected $type='token';

		public function get($arInput)
		{
			return($this->post($arInput));
		}

		public function post($arInput)
		{
			if (array_key_exists('city_id', $arInput) and !empty($arInput['city_id']) and array_key_exists('review', $arInput) and !empty($arInput['review']) and array_key_exists('type', $arInput) and !empty($arInput['type']))
			{

				switch ($arInput['type'])
				{
					case 'email':
						if (!empty($arInput['review']['email']) or !empty($arInput['review']['phone']))
						{
							CModule::IncludeModule("iblock");

							$oElement = new CIBlockElement;

							$arFields = array(
								'IBLOCK_ID' => CIBlockTools::GetIBlockId('faq'),
								'NAME' => ($arInput['review']['title']) ? htmlspecialchars($arInput['review']['title']) : 'Сообщение из мобильного приложения пользователя '.$this->User['user_id'],
								'ACTIVE' => 'N',
								'PROPERTY_VALUES' => array(
									'IS_MOBILE_APP' => CIBlockTools::GetPropertyEnumValueId('faq', 'IS_MOBILE_APP', 'Y'),
									'email' => ($arInput['review']['email']) ? htmlspecialchars($arInput['review']['email']) : '',
									'phone' => ($arInput['review']['phone']) ? htmlspecialchars($arInput['review']['phone']) : '',
									'name' => ($arInput['review']['title']) ? htmlspecialchars($arInput['review']['title']) : 'Сообщение из мобильного приложения пользователя '.$this->User['user_id'],
								),
								'PREVIEW_TEXT' => htmlspecialchars($arInput['review']['summary'])
							);

							if ($iReviewId = $oElement->Add($arFields))
								$arResult['feedback_text'] = 'Ваше обращение принято';
							else
								$this->addError('add_feedback_error');
						}
						else
							$this->addError('required_params_missed');

						break;

					case 'callback':
						if (!empty($arInput['review']['phone']))
						{
							CModule::IncludeModule("form");

							//найдем id нужной нам формы обратного звонка, дабы не использовать волшебные числа
							$arForm = CForm::GetBySID('FORM_CALLBACK')->Fetch();

							if ($arForm['ID'])
							{
								$arRequiredFields = array();

								//получаем идентификаторы вопросов, опять же дабы не использовать волшебные числа
								$oQuestions = CFormField::GetList(
								    $arForm['ID'], 
								    "N", 
								    $by="s_id", 
								    $order="asc", 
								    $arFilter, 
								    $bIsfiltered
								);
								while ($arQuestion = $oQuestions->Fetch())
								{
									$arQuestionId[$arQuestion['SID']] = $arQuestion['ID'];
								}
							
								$arFormValues = array(
								    'form_text_'.$arQuestionId['NAME'] => ($arInput['review']['title']) ? htmlspecialchars($arInput['review']['title']) : 'Запрос из мобильного приложения пользователя '.$this->User['user_id'],
								    'form_text_'.$arQuestionId['PHONE'] => $arInput['review']['phone'],
								    'form_text_'.$arQuestionId['TIME'] => 'IS_MOBILE_APP',
								);

								if ($iResultId = CFormResult::Add($arForm['ID'], $arFormValues))
								{
									//жесть конечно, но так отправляются письма при добавлении нового результата
									CFormResult::SetEvent($iResultId);
									CFormResult::Mail($iResultId);
									$arResult['feedback_text'] = 'Ваше обращение принято';
								}
								else
									$this->addError('add_feedback_error');
							}
							else
								$this->addError('add_feedback_error');
						}
						else
							$this->addError('required_params_missed');

						break;
					
					default:
						$this->addError('wrong_feedback_type');
						break;
				}
			}
			else
				$this->addError('required_params_missed');
		
			return($arResult);
		}
	}
?>