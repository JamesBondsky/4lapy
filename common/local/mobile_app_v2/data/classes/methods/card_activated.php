<?php
	class card_activated extends APIServer
	{
		protected $type='token';

		public function get($arInput){
			return($this->post($arInput));
		}

		public function post($arInput)
		{
			$arResult = array(
				'activated' => false
			);

			$sNumber = isset($arInput['number']) ? $arInput['number'] : null;
			$sToken = isset($arInput['token']) ? $arInput['token'] : null;
			if ($sNumber and $sToken)
			{
				$arUser = CUser::GetList(
					($by="id"),
					($order="desc"),
					array(
						"UF_DISC" => $sNumber
					),
					array(
						"FIELDS" => array("ID", "EMAIL"),
					)
				)->Fetch();

				// проверяем существование юзера с такой картой в битриксе
				if ($arUser)
				{
					$arResult = array(
						'activated' => true,
				'feedback_text' => "Карта уже привязана к другому аккаунту. Пожалуйста, используйте другую карту"
					);
					$this->res['errors']+=$this->ERROR['card_already_added'];
				}
			}
			else
				$this->res['errors']+=$this->ERROR['required_params_missed'];

			return($arResult);
		}
	}
?>