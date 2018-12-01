<?php
	class personal_bonus extends APIServer
	{
		protected $type = 'token';

		public function get($arInput)
		{
			if ($this->User['user_id'] > 0)
			{
				if ($this->User['UF_DISC'] and $this->User['UF_DISC'] != '')
				{
					$arCardInfo = MyCCard::UpdateDataCard_ml($this->User['UF_DISC'], $bReturnData = true);
					if ($arCardInfo)
					{
						$arCardInfoToApp = array(
							'amount' => $arCardInfo['DISCOUNT'],
							'total_income' => round($arCardInfo['DEBET'], 2),
							'total_outgo' => round($arCardInfo['CREDIT'], 2),
							'next_stage' => 0
						);

						$arDiscount = array(
							'3' => '0',
						 	'4' => '9000',
						 	'5' => '19000',
						 	'6' => '39000',
						 	'7' => '59000',
						);
						
						$iCardSum = ($arCardInfo['SUMM'] > 0) ? round($arCardInfo['SUMM']) : 0;

						foreach ($arDiscount as $sPercent => $sSum)
						{
							if(($sSum - $iCardSum) > 0)
							{
								$arCardInfoToApp['next_stage'] = $sSum - $iCardSum;
								break;
							}
						}

						$arResult['bonus'] = $arCardInfoToApp;
					}
					else
						$this->addError('get_card_info_error');
				}
				else
					$this->addError('user_no_card');
			}
			else
				$this->addError('user_not_authorized');

			return $arResult;
		}
	}
?>