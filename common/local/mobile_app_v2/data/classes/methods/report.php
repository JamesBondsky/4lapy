<?

class report extends \APIServer
{
	public function post($arInput)
	{
		$arResult = null;

		if (!isset($arInput['summary']) || !$arInput['summary']) {
			$this->addError('required_params_missed');
		} else {
			$textReport = $arInput['summary'];
			$deviceInfo = (isset($arInput['device_info']) ? $arInput['device_info'] : '');
		}

		if (!$this->hasErrors()) {
			$oUser = ($this->getUserId() > 0 ? new \user($this->getUserId()) : null);

			\Bitrix\Main\Mail\Event::sendImmediate(array(
				'EVENT_NAME' => 'MOBILE_APP_ADD_REPORT',
				'LID' => \Bitrix\Main\Application::getInstance()->getContext()->getSite(),
				'DUPLICATE' => 'N',
				'C_FIELDS' => array(
					'USER_EMAIL' => (is_object($oUser) ? $oUser->getField('EMAIL') : ''),
					'USER_PHONE' => (is_object($oUser) ? $oUser->getField('PERSONAL_PHONE') : ''),
					'USER_FIRST_NAME' => (is_object($oUser) ? $oUser->getField('NAME') : ''),
					'USER_LAST_NAME' => (is_object($oUser) ? $oUser->getField('LAST_NAME') : ''),
					'TEXT_REPORT' => $textReport,
					'DEVICE_INFO' => ($deviceInfo ?: 'информация отсутствует'),
				),
			));

			$arResult['feedback_text'] = (new \message('report_send_ok'))->getMessage();
		}

		return $arResult;
	}
}
