<?
class mobile_version extends \APIServer
{
	protected $type = '';

	public function get($arInput)
	{
		$arResult = null;

		if (
			isset($arInput['v']) && strlen($arInput['v']) > 0 && 
			isset($arInput['os']) && strlen($arInput['os']) > 0 &&
			in_array($arInput['os'], array('ios','android'))
		) {
			$sBildVersion = $arInput['v'];
			$sOS = $arInput['os'];
		} else {
			$this->addError('required_params_missed');
		}

		// Android: 2.4.2_2.4.1_2.4_2.3.1_2.3_2.2_2.1_2.0_1.8
		// IOS: 1.3.7_1.3.6_1.3.5_1.3.4_1.3.3_1.3.2_1.3.1_1.3.0_1.2_1.1_1.0

		//Указанные в массивах билды РАЗРЕШЕНЫ к использованию
		$arIosVersions = array(
			'1.4.0',
			'1.3.9',
			'6440',
			'1.3.8',
			'1.3.7',
			'1.3.6',
			'1.3.5',
			'1.3.4',
			'1.3.3',
			'1.3.2',
			'1.3.1',
			'1.3.0',
			'1.2',
			'1.1',
			'1.0'
		);
		$arAndroidVersions = array(
			'beta',
			'2.4.5',
			'2.4.4',
			'2.4.3',
			'2.4.2',
			'2.4.1',
			'2.4',
			'2.3.1',
			'2.3',
			'2.2',
			'2.1',
			'2.0',
			'1.8'
		);

		if (!$this->hasErrors()) {
			if($sOS == 'ios' and !in_array($sBildVersion, $arIosVersions)){
				$arResult['blocked'] = true;
			}elseif($sOS == 'android' and !in_array($sBildVersion, $arAndroidVersions)){
				$arResult['blocked'] = true;
			}else{
				$arResult['blocked'] = false;
			}
		}

		return $arResult;
	}
}
