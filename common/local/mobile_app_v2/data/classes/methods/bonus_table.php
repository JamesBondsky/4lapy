<?php
	class bonus_table extends APIServer
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
						$iCardSum = ($arCardInfo['SUMM'] > 0) ? round($arCardInfo['SUMM']) : 0;

						$arDiscount = array(
							'3' => '0',
						 	'4' => '9000',
						 	'5' => '19000',
						 	'6' => '39000',
						 	'7' => '59000',
						);

						foreach ($arDiscount as $sPercent => $sSum)
						{
							$arTable[] = array(
								"{$sPercent}%",
								"{$sSum}",
								($sSum - $iCardSum <= 0) ? '-' : ($sSum - $iCardSum),
							);
						}

						$arResult['table'] = array(
							array('Размер бонуса', 'Сумма начислений', 'До следующего порога осталось'),
							$arTable
						);
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