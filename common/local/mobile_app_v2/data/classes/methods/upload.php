<?php
	class upload extends APIServer
	{
		protected $type='token';

		public function get($arInput){
			return($this->post($arInput));
		}

		public function post($arInput)
		{
			if ($this->User['user_id'] > 0)
			{
				$iUploadSourceId = (isset($arInput['id']) and !empty($arInput['id'])) ? $arInput['id'] : null;
				$sUploadType = (isset($arInput['type']) and !empty($arInput['type'])) ? $arInput['type'] : null;
				$sText = (isset($arInput['text']) and !empty($arInput['text'])) ? $arInput['text'] : null;

				if ($iUploadSourceId and $sUploadType and $sText)
				{
					if (isset($arInput['img']) and !empty($arInput['img']))
					{
						//проверяем куда хотят загрузить файл
						switch ($sUploadType)
						{
							//если это конкурс
							case 'competition':
								CModule::IncludeModule("iblock");

								//проверяем существование и активность конкурса
								$arSection = CIBlockSection::GetList(
									array(),
									array(
										'ID' => $iUploadSourceId,
										'ACTIVE' => 'Y'
									),
									false,
									array(
										'ID',
										'NAME',
									)
								)->Fetch();

								if ($arSection)
								{
									//проверяем тип файла
									$sRes = CFile::CheckFile($arInput['img'], 0, "image/", CFile::GetImageExtensions());
									if (strlen($sRes) == 0)
									{
										//сохраняем файл в системе
										$iFileId = CFile::SaveFile($arInput['img']);
										if ($iFileId)
										{
											$oElement = new CIBlockElement;

											$arFields = array(
												'IBLOCK_SECTION_ID' => $arSection['ID'],
											  	'IBLOCK_ID' => CIBlockTools::GetIBlockId('actions'),
											  	'NAME' => $sText,
											  	'CODE' => \CUtil::translit($sText, 'ru'),
											  	'ACTIVE' => 'N',
											  	'DATE_ACTIVE_FROM' => ConvertTimeStamp(time(), "FULL"),
											  	'DETAIL_PICTURE' => CFile::MakeFileArray($iFileId),
											  	'PROPERTY_VALUES'=> array(
											  		'fio' => $this->User['NAME'],
											  		'email' => $this->User['EMAIL'],
											  		'phone' => $this->User['PERSONAL_PHONE'],
											  		'IS_MOBILE_APP' => CIBlockTools::GetPropertyEnumValueId('actions', 'IS_MOBILE_APP', 'Y'),
											  	)
											);

											if($iElementId = $oElement->Add($arFields, false, false, true))
												$arResult['feedback_text'] = 'Файл успешно загружен';
											else
											 	$this->addError('add_file_error');
										}
										else
											$this->addError('add_file_error');
									}
									else
										$this->addError('file_valid_fail');
								}
								else
									$this->addError('add_file_error');

								break;

							default:
								$this->addError('wrong_input_file_type');
								break;
						}
					}
					else
						$this->addError('input_file_error');
				}
				else
					$this->addError('required_params_missed');
			}
			else
				$this->addError('user_not_authorized');

			return($arResult);
		}
	}
?>