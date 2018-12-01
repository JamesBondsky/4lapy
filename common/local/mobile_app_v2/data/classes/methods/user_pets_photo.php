<?php

class user_pets_photo extends APIServer
{
	const MAX_USER_PET_PHOTO_COUNT = 3;
	const PHOTO_FILE_SIZE = 1024 * 1024;
	const PHOTO_WIDTH = 800;
	const PHOTO_HEIGHT = 800;
	const PHOTO_QUALITY = 85;

	public function post($arInput)
	{
		$arResult = null;

		CModule::IncludeModule('iblock');

		if (intval($arInput['pet_id'] <= 0) || !isset($arInput['photo']) || empty($arInput['photo'])) {
			$this->addError('required_params_missed');
		} else {
			$petId = intval($arInput['pet_id']);
		}

		if (!$this->getUserId()) {
			$this->addError('user_not_authorized');
		} else {
			$userId = $this->getUserId();
		}

		if (!$this->hasErrors()) {
			$arUserPets = \user_pets::getUserPetsList($userId);

			if (!array_key_exists($petId, $arUserPets)) {
				$this->addError('user_pet_not_found');
			}
		}

		if (!$this->hasErrors()) {
			$sRes = CFile::CheckImageFile($arInput['photo']);
			if (strlen($sRes) == 0)
			{
				$fileInfo = getimagesize($arInput['photo']['tmp_name']);
				if ($arInput['photo']['size'] > self::PHOTO_FILE_SIZE || $fileInfo[0] > self::PHOTO_WIDTH || $fileInfo[1] > self::PHOTO_HEIGHT) {
					$tempName = tempnam(sys_get_temp_dir(), 'pet');

					CFile::ResizeImageFile(
						$arInput['photo']['tmp_name'],
						$tempName,
						array(
							'width' => self::PHOTO_WIDTH,
							'height' => self::PHOTO_HEIGHT
						),
						BX_RESIZE_IMAGE_PROPORTIONAL,
						array(),
						self::PHOTO_QUALITY
					);
					$arInput['photo']['tmp_name'] = $tempName;
				}
				if (count($arUserPets[$petId]['photo']) >= $this::MAX_USER_PET_PHOTO_COUNT) {
					// удалить последнее фото
					$photoId = array_shift(array_keys($arUserPets[$petId]['photo']));
					self::deletePhoto($petId, $photoId);
				}

				CIBlockElement::SetPropertyValues($petId, CIBlockTools::GetIBlockId('user_pets'), $arInput['photo'], 'PET_PHOTO');
				$arResult = \user_pets::get();
			}
		}

		return $arResult;
	}

	public function delete($arInput)
	{
		$arResult = null;

		CModule::IncludeModule('iblock');

		if (intval($arInput['pet_id'] <= 0) || intval($arInput['id']) <= 0) {
			$this->addError('required_params_missed');
		} else {
			$petId = intval($arInput['pet_id']);
			$id = intval($arInput['id']);
		}

		if (!$this->getUserId()) {
			$this->addError('user_not_authorized');
		} else {
			$userId = $this->getUserId();
		}

		if (!$this->hasErrors()) {
			$arUserPets = \user_pets::getUserPetsList($userId);
			if (!array_key_exists($petId, $arUserPets)) {
				$this->addError('user_pet_not_found');
			}
		}

		if (!$this->hasErrors()) {
			if (array_key_exists($id, $arUserPets[$petId]['photo'])) {
				self::deletePhoto($petId, $id);
			}

			$arResult = \user_pets::get();
		}

		return $arResult;
	}

	private function deletePhoto($petId, $id)
	{
		$oProps = CIBlockElement::GetProperty(
			CIBlockTools::GetIBlockId('user_pets'),
			$petId,
			'sort',
			'asc',
			array(
				'CODE' => 'PET_PHOTO'
			)
		);

		while ($arProps = $oProps->Fetch())
		{
			if ($arProps['VALUE'] == $id) {
				CIBlockElement::SetPropertyValueCode(
					$petId,
					'PET_PHOTO',
					array(
						$arProps['PROPERTY_VALUE_ID'] => array(
							'VALUE' => array(
								'MODULE_ID' => 'iblock',
								'del' => 'Y'
							)
						)
					)
				);
				CFile::Delete($id);
			}
		}
	}

}
