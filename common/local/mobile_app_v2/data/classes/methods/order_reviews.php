<?

class order_reviews extends APIServer
{
	public function post($arInput)
	{
		if (!$this->getUserId()) {
			$this->addError('user_not_authorized');
		}

		if (!isset($arInput['id'])
			|| !($orderId = intval($arInput['id']))
			|| !isset($arInput['review'])
			|| !($arReview = $arInput['review'])
		) {
			$this->addError('required_params_missed');
		}

		if (!$this->hasErrors() && \order::hasReviews($orderId)) {
			$this->addError('twice_review_error');
		}

		if (!$this->hasErrors()) {
			\Bitrix\Main\Loader::includeModule('iblock');
			$oIbElement = new \CIBlockElement();

			$arFields = array(
				'IBLOCK_ID' => \CIBlockTools::GetIBlockId('orders_reviews'),
				'NAME' => "Заказ № {$orderId}",
				'ACTIVE' => 'N',
				'PROPERTY_VALUES' => array(
					'USER' => $this->getUserId(),
					'RATING' => \CIBlockTools::GetPropertyEnumValueId('orders_reviews', 'RATING', $arReview['rate']),
					'REV_TYPE' => htmlspecialchars($arReview['title']),
					'ORDER_ID' => $orderId,
					'REASON' => \CIBlockTools::GetPropertyEnumValueId('orders_reviews', 'REASON', $arReview['reason']),
				),
				'DETAIL_TEXT' => htmlspecialchars($arReview['summary']),
			);

			if ($oIbElement->Add($arFields)) {
				$arResult['feedback_text'] = (new \message('order_reviews_send_ok'))->getMessage();

				// отмечаем, что отзыв по заказу оставлен
				\Disable_check($orderId, 5);
				\Disable_check($orderId, 8);
			} else {
				$this->addError('add_review_error');
			}
		}

		return $arResult;
	}
}
