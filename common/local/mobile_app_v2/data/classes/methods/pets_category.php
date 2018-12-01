<?php

class pets_category extends APIServer
{

	public function get($arInput)
	{
		$arResult = null;

		CModule::IncludeModule('iblock');

		if (!$this->getUserId()) {
			$this->addError('user_not_authorized');
		}

		if (!$this->hasErrors()) {
			$arResult = array();

			$oBreeds = CIBlockElement::GetList(
				array(
					'NAME' => 'ASC'
				),
				array(
					'IBLOCK_ID' => CIBlockTools::GetIBlockId('kinds'),
					'ACTIVE' => 'Y',
					'!SECTION_ID' => false
				),
				false,
				false,
				array(
					'ID',
					'NAME',
					'IBLOCK_SECTION_ID'
				)
			);

			$arBreeds = array();

			while ($arBreed = $oBreeds->Fetch())
			{
				$arBreeds[$arBreed['IBLOCK_SECTION_ID']][] = array(
					'id' => $arBreed['ID'],
					'title' => trim($arBreed['NAME'])
				);
			}

			$cmp = function($a, $b) {
				if (isset($a['sort']) && $a['sort'] != $b['sort']) {
					return $a['sort'] > $b['sort'] ? 1 : -1;
				}
				if ($a['title'] == 'Другое') {
					return 1;
				} elseif ($b['title'] == 'Другое') {
					return -1;
				}
				return strcmp($a['title'], $b['title']);
			};

			foreach ($arBreeds as $categoryId => $arBreed)
			{
				usort($arBreeds[$categoryId], $cmp);
			}

			$arGenders = array();

			$oSex = CIBlockElement::GetList(
				array(
					'SORT' => 'ASC'
				),
				array(
					'IBLOCK_ID' => CIBlockTools::GetIBlockId('pets_sex'),
					'ACTIVE' => 'Y'
				),
				false,
				false,
				array(
					'ID',
					'NAME'
				)
			);

			while ($arSex = $oSex->Fetch())
			{
				$arGenders[$arSex['ID']] = trim($arSex['NAME']);
			}

			$oCategoryes = CIBlockSection::GetList(
				array(
					'LEFT_MARGIN' => 'ASC'
				),
				array(
					'IBLOCK_ID' => CIBlockTools::GetIBlockId('kinds'),
					'ACTIVE' => 'Y'
				),
				false,
				array(
					'ID',
					'NAME',
					'IBLOCK_SECTION_ID',
					'SORT',
					'UF_GENDER'
				)
			);

			while ($arCategory = $oCategoryes->Fetch())
			{
				$sid = $arCategory['ID'];
				$psid = (int)$arCategory['IBLOCK_SECTION_ID'];

				$arResult[$psid]['subcategoryes'][$sid] = array(
					'id' => $arCategory['ID'],
					'title' => $arCategory['NAME'],
					'gender' => array()
				);

				
				if (is_array($arCategory['UF_GENDER'])) {
					foreach ($arCategory['UF_GENDER'] as $sexId)
					{
						$arResult[$psid]['subcategoryes'][$sid]['gender'][] = array(
							'id' => (string)$sexId,
							'title' => $arGenders[$sexId]
						);
					}
				}

				if ($psid) {
					$arResult[$psid]['subcategoryes'][$sid]['gender'] = $arResult[$psid]['gender'];
					$arResult[$psid]['subcategoryes'][$sid]['breeds'] = (array)$arBreeds[$sid];
				} else {
					$arResult[$psid]['subcategoryes'][$sid]['sort'] = $arCategory['SORT'];
					$arResult[$psid]['gender'] = $arCategory['UF_GENDER'];
				}

				$arResult[$sid] = &$arResult[$psid]['subcategoryes'][$sid];
			}

			$arResult = array_shift($arResult);
			$arResult = array_shift($arResult);

			usort($arResult, $cmp);

			foreach ($arResult as $categoryId => $arCategory)
			{
				unset($arResult[$categoryId]['sort']);
				usort($arResult[$categoryId]['subcategoryes'], $cmp);
			}
		}

		return $arResult;
	}

}
