<?php
	class goods_reviews extends APIServer
	{
		protected $type='token';

		public function get($arInput)
		{
			CModule::IncludeModule("iblock");

			$iProductID = -1;

			// проверяем существование ключей и формат
			if (array_key_exists('id', $arInput))
			{
				if (is_numeric($arInput['id']) && $arInput['id'] > 0)
					$iProductID = $arInput['id'];
			}

			if ($iProductID > -1)
			{
				$arCacheReasonEnum = array();

				//получаем список товаров по заданным параметрам
				$oReviewsList = CIBlockElement::GetList(
					array(),
					array(
						'IBLOCK_ID' => CIBlockTools::GetIBlockId('reviews'),
						'PROPERTY_REV_PRODUCT' => $iProductID,
						'ACTIVE' => 'Y',
					),
					false,
					false,
					array(
						'ID',
						'NAME',
						'DETAIL_TEXT',
						'DATE_ACTIVE_FROM',
						'DATE_CREATE',
						'PROPERTY_REV_TYPE',
						'PROPERTY_USER',
						'PROPERTY_RATING',
						'PROPERTY_REASON',
					)
				);

				while ($arReview = $oReviewsList->Fetch())
				{
					if ($arReview['PROPERTY_REASON_ENUM_ID'] > 0) {
						if (!isset($arCacheReasonEnum[$arReview['PROPERTY_REASON_ENUM_ID']])) {
							$arPropertyEnum = \Bitrix\Iblock\PropertyEnumerationTable::getList(array(
								'filter' => array(
									'=ID' => $arReview['PROPERTY_REASON_ENUM_ID']
								),
								'select' => array('XML_ID'),
							))->fetch();
							$arCacheReasonEnum[$arReview['PROPERTY_REASON_ENUM_ID']] = ($arPropertyEnum ? $arPropertyEnum['XML_ID'] : '');
						}

						$reason = $arCacheReasonEnum[$arReview['PROPERTY_REASON_ENUM_ID']];
					} else {
						$reason = '';
					}

					$arResult['reviews'][] = array(
						'title' => ($arReview['PROPERTY_REV_TYPE_VALUE'])?:'',
						'author' => ($arReview['NAME'])?:'',
						'pros' => '',
						'cons' => '',
						'summary' => ($arReview['DETAIL_TEXT'])?:'',
						'rate' => ($arReview['PROPERTY_RATING_VALUE'])?:'',
						'date' => (date(API_DATE_FORMAT, strtotime($arReview['DATE_CREATE'])))?:'',
						'reason' => $reason,
					);
				}
			}
			else
				$this->res['errors']+=$this->ERROR['required_params_missed'];

			return($arResult);
		}

		public function post($arInput)
		{
			CModule::IncludeModule("iblock");

			if ($this->User['user_id'] > 0)
			{
				$iProductID = -1;

				// проверяем существование ключей и формат
				if (array_key_exists('id', $arInput))
				{
					if (is_numeric($arInput['id']) && $arInput['id'] > 0)
						$iProductID = $arInput['id'];
				}

				if ($iProductID > -1)
				{
					$oElement = new CIBlockElement;

					$arFields = array(
						'IBLOCK_ID' => CIBlockTools::GetIBlockId('reviews'),
						'NAME' => ($arInput['review']['author']) ? htmlspecialchars($arInput['review']['author']) : 'Отзыв пользователя '.$this->User['user_id'],
						'ACTIVE' => 'N',
						'PROPERTY_VALUES' => array(
							'USER' => $this->User['user_id'],
							'REV_PRODUCT' => $iProductID,
							'REV_TYPE' => htmlspecialchars($arInput['review']['title']),
							'RATING' => \CIBlockTools::GetPropertyEnumValueId('reviews', 'RATING', $arInput['review']['rate']),
							'REASON' => \CIBlockTools::GetPropertyEnumValueId('reviews', 'REASON', $arInput['review']['reason']),
						),
						'DETAIL_TEXT' => htmlspecialchars($arInput['review']['summary'])
					);

					if ($iReviewId = $oElement->Add($arFields))
						$arResult['feedback_text'] = 'После проверки модератором он будет опубликован';
					else
						$this->res['errors']+=$this->ERROR['add_review_error'];
				}
				else
					$this->res['errors']+=$this->ERROR['required_params_missed'];
			}
			else
				$this->addError('user_not_authorized');

			return($arResult);
		}
	}
?>