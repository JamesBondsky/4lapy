<?

class shop_reviews extends \APIServer
{
	public function post($arInput)
	{
		if (!$this->getUserId()) {
			$this->addError('user_not_authorized');
		}

		if (!isset($arInput['id'])
			|| !($shopCode = $arInput['id'])
			|| !isset($arInput['review'])
			|| !($arReview = $arInput['review'])
		) {
			$this->addError('required_params_missed');
		}

		if (!$this->hasErrors()) {
			\Bitrix\Main\Loader::includeModule('iblock');
			$oIbElement = new \CIBlockElement();

			$arShop = \shop::getByCode($shopCode);

			$arFields = array(
				'IBLOCK_ID' => \CIBlockTools::GetIBlockId('shop_reviews'),
				'NAME' => ($arReview['author'] ? htmlspecialchars($arReview['author']) : 'Отзыв пользователя '.$this->getUserId()),
				'ACTIVE' => 'N',
				'PROPERTY_VALUES' => array(
					'USER' => $this->getUserId(),
					'RATING' => \CIBlockTools::GetPropertyEnumValueId('shop_reviews', 'RATING', $arReview['rate']),
					'SHOP_CODE' => "{$arShop['title']}, {$arShop['id']}",
					'REVIEW_TYPE' => \CIBlockTools::GetPropertyEnumValueId('shop_reviews', 'REVIEW_TYPE', $arReview['reason']),
				),
				'DETAIL_TEXT' => htmlspecialchars($arReview['summary']),
			);

			if ($oIbElement->Add($arFields)) {
				$arResult['feedback_text'] = (new \message('shop_reviews_send_ok'))->getMessage();
			} else {
				$this->addError('add_review_error');
			}
		}

		return $arResult;
	}
}
