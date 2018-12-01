<?php
	class vote extends APIServer
	{
		protected $type='token';

		public function get($arInput)
		{
			return $this->post($arInput);
		}

		public function post($arInput)
		{
			CModule::IncludeModule("iblock");

			if ($this->User['user_id'] > 0)
			{
				$bCheckCaptcha = true;

				if (!empty($arInput['captcha_id']) and !empty($arInput['captcha_value']))
				{
					include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");
					$oCaptcha = new CCaptcha();
					if (!$oCaptcha->CheckCode($arInput['captcha_value'], $arInput['captcha_id']))
					{
						$bCheckCaptcha = false;
						$this->addError('wrong_captcha');
					}
				}

				// проверяем существование ключей
				$iVoteElementID = (isset($arInput['id']) and !empty($arInput['id'])) ? $arInput['id'] : null;
				$iVoteRate = (isset($arInput['rate']) and !empty($arInput['rate'])) ? $arInput['rate'] : null;

				if ($bCheckCaptcha and $iVoteElementID  and $iVoteRate)
				{
					if ($iVoteRate >= 0 and $iVoteRate <= 5)
					{
						CModule::IncludeModule("askaron.ibvote");

						$oEvent = new CAskaronIbvoteEvent;

						//ограничение по времени для голосования и максимальная оценка нужны для правильного функционирования
						$sCheckTime = 86400000;
						$iMaxVote = 5;

						//проверяем голосовал ли пользователь (id и ip)
						if ($this->getUserId() > 0) {
							$bCheckVote = $oEvent->CheckVotingUserId($iVoteElementID, $this->getUserId(), $sCheckTime);
						} else {
							$bCheckVote = $oEvent->CheckVotingIP($iVoteElementID, $this->User['ip'], $sCheckTime);
						}

						//если не голосовал
						if (!$bCheckVote)
						{
							//устанавливаем id инфоблока с конкурсами
							$iIblockId = CIBlockTools::GetIBlockId('actions');

							//получаем текущие значения полей рейтинга
							$rsProperties = CIBlockElement::GetProperty($iIblockId, $iVoteElementID, "value_id", "asc", array("ACTIVE"=>"Y"));
							$arProperties = array();
							while($arProperty = $rsProperties->Fetch())
							{
								if($arProperty["CODE"]=="vote_count")
									$arProperties["vote_count"] = $arProperty;
								elseif($arProperty["CODE"]=="vote_sum")
									$arProperties["vote_sum"] = $arProperty;
								elseif($arProperty["CODE"]=="rating")
									$arProperties["rating"] = $arProperty;
							}

							$obProperty = new CIBlockProperty;
							$res = true;
							if(!array_key_exists("vote_count", $arProperties))
							{
								$res = $obProperty->Add(array(
									"IBLOCK_ID" => $iIblockId,
									"ACTIVE" => "Y",
									"PROPERTY_TYPE" => "N",
									"MULTIPLE" => "N",
									"NAME" => "vote_count",
									"CODE" => "vote_count",
								));
								if($res)
									$arProperties["vote_count"] = array("VALUE"=>0);
							}
							if($res && !array_key_exists("vote_sum", $arProperties))
							{
								$res = $obProperty->Add(array(
									"IBLOCK_ID" => $iIblockId,
									"ACTIVE" => "Y",
									"PROPERTY_TYPE" => "N",
									"MULTIPLE" => "N",
									"NAME" => "vote_sum",
									"CODE" => "vote_sum",
								));
								if($res)
									$arProperties["vote_sum"] = array("VALUE"=>0);
							}
							if($res && !array_key_exists("rating", $arProperties))
							{
								$res = $obProperty->Add(array(
									"IBLOCK_ID" => $iIblockId,
									"ACTIVE" => "Y",
									"PROPERTY_TYPE" => "N",
									"MULTIPLE" => "N",
									"NAME" => "rating",
									"CODE" => "rating",
								));
								if($res)
									$arProperties["rating"] = array("VALUE"=>0);
							}

							//обновляем значение полей рейтинга
							if($res)
							{
								$arProperties["vote_count"]["VALUE"] = intval($arProperties["vote_count"]["VALUE"])+1;
								$arProperties["vote_sum"]["VALUE"] = intval($arProperties["vote_sum"]["VALUE"])+$iVoteRate;
								$arProperties["rating"]["VALUE"] = round($arProperties["vote_sum"]["VALUE"]/$arProperties["vote_count"]["VALUE"],2);

								global $DB;
								$DB->StartTransaction();

								CIBlockElement::SetPropertyValuesEx($iVoteElementID, $iIblockId, array(
									"vote_count" => array(
										"VALUE" => $arProperties["vote_count"]["VALUE"],
										"DESCRIPTION" => $arProperties["vote_count"]["DESCRIPTION"],
									),
									"vote_sum" => array(
										"VALUE" => $arProperties["vote_sum"]["VALUE"],
										"DESCRIPTION" => $arProperties["vote_sum"]["DESCRIPTION"],
									),
									"rating" => array(
										"VALUE" => $arProperties["rating"]["VALUE"],
										"DESCRIPTION" => $arProperties["rating"]["DESCRIPTION"],
									),
								));
								$DB->Commit();

								//добавляем запись в таблицу голосований
								$arEventFields = array(
									'ELEMENT_ID' =>  $iVoteElementID,
									'ANSWER' => $iVoteRate,
									'USER_ID' => $this->User['user_id'],
									'IP' => $this->User['ip'],
								);

								if ($oEvent->add($arEventFields))
									$arResult['feedback_text'] = 'Спасибо за участие';
								else
									$this->addError('vote_error');

								//сбрасываем кеш на всякий случай
								$clear_cache=COption::GetOptionString("askaron.ibvote", "clear_cache");
								if ( $clear_cache !== "N" )
								{
									if(defined("BX_COMP_MANAGED_CACHE"))
									{
										$GLOBALS["CACHE_MANAGER"]->ClearByTag("iblock_id_".$iIblockId);
									}
								}
							}
						}
						else
							$this->addError('already_vote');
					}
					else
						$this->addError('wrong_rate');
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