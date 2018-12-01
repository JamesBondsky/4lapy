<?php

class user_pets extends APIServer
{
	const MAX_USER_PETS_COUNT = 10;
	const PREVIEW_WIDTH = 166;
	const PREVIEW_HEIGHT = 166;
	const PREVIEW_QUALITY = 85;

	public function get($arInput)
	{
		$arResult = null;

		if ($this->getUserId()) {
			$userId = $this->getUserId();
		} else {
			$this->addError('user_not_authorized');
		}

		if (!$this->hasErrors()) {
			$arResult = self::getUserPetsList($userId);
			foreach ($arResult as $petId => $arPet)
			{
				foreach ($arPet['photo'] as $photoId => $arPhoto)
				{
					unset($arResult[$petId]['photo'][$photoId]);
					$arResult[$petId]['photo'][] = self::getUserPetPhoto($photoId);
				}
			}
		}

		foreach ($arResult as $petId => $arPet)
		{
			$arResult[$petId]['photo'] = array_values($arPet['photo']);
		}

		$arResult = array_values($arResult);
		return $arResult;
	}

	// добавление питомца
	public function post($arInput)
	{
		$arResult = null;

		if (!isset($arInput['category_id']) || !isset($arInput['name'])) {
			$this->addError('required_params_missed');
		}

		if (!$this->getUserId()) {
			$this->addError('user_not_authorized');
		} else {
			$userId = $this->getUserId();
		}

		if ($arInput['age_date']) {
			$birthday = new DateTime($arInput['age_date']);
			$now = new DateTime();
			if ($birthday > $now) {
				$this->addError('user_pet_age_error');
			}
		}

		if (!$this->hasErrors()) {
			self::editUserPet($arInput, $userId);
		}

		$arResult = self::get();

		return $arResult;
	}

	// редактирование питомца
	public function put($arInput)
	{
		$arResult = null;

		if (!isset($arInput['category_id']) || !isset($arInput['name']) || intval($arInput['id']) <= 0) {
			$this->addError('required_params_missed');
		} else {
			$petId = intval($arInput['id']);
		}

		if (!$this->getUserId()) {
			$this->addError('user_not_authorized');
		} else {
			$userId = $this->getUserId();
		}

		if ($arInput['age_date']) {
			$birthday = new DateTime($arInput['age_date']);
			$now = new DateTime();
			if ($birthday > $now) {
				$this->addError('user_pet_age_error');
			}
		}

		if (!$this->hasErrors()) {
			self::editUserPet($arInput, $userId);
		}

		$arResult = self::get();

		return $arResult;
	}

	// удаление питомца
	public function delete($arInput)
	{
		$arResult = null;

		CModule::IncludeModule('iblock');

		if (!$this->getUserId()) {
			$this->addError('user_not_authorized');
		} else {
			$userId = $this->getUserId();
		}

		if (!isset($arInput['id']) || intval($arInput['id']) <= 0) {
			$this->addError('required_params_missed');
		} else {
			$petId = intval($arInput['id']);
		}

		if (!$this->hasErrors()) {
			$arUserPets = self::getUserPetsList($userId);
			if (array_key_exists($petId, $arUserPets)) {
				CIBlockElement::Delete($petId);
			}
		}

		$arResult = self::get();

		return $arResult;
	}

	private function editUserPet($arInput, $userId)
	{
		CModule::IncludeModule('iblock');

		$arFields = array(
			'MODIFIED_BY' => $userId,
			'IBLOCK_ID' => CIBlockTools::GetIBlockId('user_pets'),
			'IBLOCK_SECTION_ID' => false,
			'NAME' => $arInput['name'],
			'PROPERTY_VALUES' => array(
				'USER_ID' => $userId,
				'PET_CATEGORY' => $arInput['category_id'],
				'PET_BREED' => $arInput['breed_id'],
				'PET_BREED_OTHER' => $arInput['breed_other'],
				'PET_SEX' => $arInput['gender'],
				'PET_BIRTHDAY' => $arInput['age_date']
			)
		);

		$arUserPets = self::getUserPetsList($userId);

		if (intval($arInput['id']) > 0) {
			$petId = intval($arInput['id']);

			if (array_key_exists($petId, $arUserPets)) {
				$el = new CIBlockElement;
				$el->update($petId, $arFields);
			}
		} else {
			if (count($arUserPets) >= self::MAX_USER_PETS_COUNT) {
				$this->addError('max_pets_count_exceeded');
			} else {
				$el = new CIBlockElement;
				$el->add($arFields);
			}
		}

	}

	public function getUserPetsList($userId)
	{
		CModule::IncludeModule('iblock');

		$arResult = array();

		$oPets = CIBlockElement::GetList(
			array(
				'NAME' => 'ASC'
			),
			array(
				'IBLOCK_ID' => CIBlockTools::GetIBlockId('user_pets'),
				'ACTIVE' => 'Y',
				'PROPERTY_USER_ID' => $userId
			),
			false,
			false,
			array(
				'ID',
				'NAME',
				'PROPERTY_PET_CATEGORY',
				'PROPERTY_PET_BREED',
				'PROPERTY_PET_BREED_OTHER',
				'PROPERTY_PET_SEX.NAME',
				'PROPERTY_PET_SEX.ID',
				'PROPERTY_PET_BIRTHDAY',
				'PROPERTY_PET_PHOTO'
			)
		);

		while ($arPet = $oPets->Fetch())
		{
			if (!isset($arResult[$arPet['ID']])) {
				$petAgeStr = '';
				if ($arPet['PROPERTY_PET_BIRTHDAY_VALUE']) {
					$birthday = new DateTime($arPet['PROPERTY_PET_BIRTHDAY_VALUE']);
					$now = new DateTime();
					$petAge = date_diff($now, $birthday);
					$petAgeYear = $petAge->format('%y');
					$petAgeMonth = $petAge->format('%m');

					if ($petAgeYear > 0) {
						$petAgeStr = $petAgeYear . ' ' . self::getPluralForm($petAgeYear, array('год', 'года', 'лет'));
					}
					if ($petAgeMonth > 0) {
						$petAgeStr .= (($petAgeYear > 0) ? ' ' : '') . $petAgeMonth . ' ' . self::getPluralForm($petAgeMonth, array('месяц', 'месяца', 'месяцев'));
					} elseif ($petAgeYear <= 0) {
						$petAgeStr = 'меньше месяца';
					}
				}

				$arResult[$arPet['ID']] = array(
					'id' => $arPet['ID'],
					'name' => $arPet['NAME'],
					'category_id' => $arPet['PROPERTY_PET_CATEGORY_VALUE'],
					'breed_id' => $arPet['PROPERTY_PET_BREED_VALUE'],
					'breed_other' => $arPet['PROPERTY_PET_BREED_OTHER_VALUE'],
					'gender' => array(
						'id' => $arPet['PROPERTY_PET_SEX_ID'],
						'title' => $arPet['PROPERTY_PET_SEX_NAME'],
					),
					'age_date' => $arPet['PROPERTY_PET_BIRTHDAY_VALUE'],
					'age_string' => $petAgeStr,
					'photo' => array()
				);
			}

			if ($arPet['PROPERTY_PET_PHOTO_VALUE']) {
				if (is_array($arPet['PROPERTY_PET_PHOTO_VALUE'])) {
					foreach ($arPet['PROPERTY_PET_PHOTO_VALUE'] as $photoId)
					{
						$arResult[$arPet['ID']]['photo'][$photoId] = null;
					}
				} else {
					$arResult[$arPet['ID']]['photo'][$arPet['PROPERTY_PET_PHOTO_VALUE']] = null;
				}
			}
		}

		foreach ($arResult as $petId => $arPet)
		{
			$arResult[$petId]['photo'] = array_reverse($arPet['photo'], true);
		}

		return $arResult;
	}

	public function getUserPetPhoto($photoId)
	{
		$arFile = CFile::GetFileArray($photoId);
		$arPreview = CFile::ResizeImageGet($photoId, array('width' => self::PREVIEW_WIDTH, 'height' => self::PREVIEW_HEIGHT), BX_RESIZE_IMAGE_EXACT, false, false, false, self::PREVIEW_QUALITY);

		$arResult = array(
			'id' => $arFile['ID'],
			'src' => 'https://' . SITE_SERVER_NAME_API . $arFile['SRC'],
			'preview' => 'https://' . SITE_SERVER_NAME_API . $arPreview['src']
		);

		return $arResult;
	}

	private function getPluralForm($n, $forms = array())
	{
		$n = abs((int) $n);
		return $forms[(($n%10 === 1) && ($n%100 !== 11)) ? 0 : ((($n%10 >= 2) && ($n%10 <= 4) && (($n%100 < 10) || ($n%100 >= 20))) ? 1 : 2)];
	}

}
