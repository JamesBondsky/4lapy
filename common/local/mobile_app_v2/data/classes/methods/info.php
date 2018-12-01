<?php
class info extends APIServer
{
	protected $type='token';
	protected $arInfo = array(
		'id' =>	'',	//	Идентификатор	раздела,
		'type' => '',	//	Тип	страницы
		'date'	=> '',	//	Дата	новости	(см.формат	Дата)
		'end_date' => '',	//	Окончание периода (см.формат Дата)
		'title' => '',	//	Название	раздела
		'details' => '', // Анонс	-	Краткое	описание	новости
		'html'	=>	'',	//	HTML-текст	контента
		'web_url' => '',	//	Ссылка	на	веб-страницу	с	контентом
		'icon' => '',	//	ссылка	на	главную	картинку	страницы
		'images' =>	array(),	//	Массив	url	до	изображений	(если	в	приложении	надо	показать	фотоленту)
		'participants' => array(),	//	Список	ОбъектОценки	(если	есть	по	смыслу)
		'vote_enabled' => false,	//	Признак	открытого/закрытого	голосования
		'goods' => array(),	//	Список	объектов	ОбъектКаталога.КраткийТовар
		'subitems' => array() //	Список	вложенных	(дочерних)	объектов Инфо
	);

	protected function GetStaticPage($pageType)
	{
		\Bitrix\Main\Loader::includeModule('iblock');

		$arResult = false;

		//получаем статическую страницу
		$arPageInfo = \Bitrix\Iblock\ElementTable::getList(array(
			'filter' => array(
				'=IBLOCK_ID' => \CIBlockTools::GetIBlockId('static_pages_app'),
				'=ACTIVE' => 'Y',
				'=CODE' => $pageType,
			),
			'select' => array('ID', 'NAME', 'CODE', 'DETAIL_TEXT'),
		))->fetch();

		//формируем ответ
		if ($arPageInfo) {
			$arResult['id'] = $arPageInfo['ID'];
			$arResult['type'] = $arPageInfo['CODE'];
			$arResult['title'] = $arPageInfo['NAME'];

			$arResult['html'] = $this->correctionLinkInText($arPageInfo['DETAIL_TEXT']);
			$arResult['html'] = htmlspecialcharsbx($arResult['html']);
		} else {
			$this->addError('info_page_not_found');
		}

		return $arResult;
	}

	protected function GetVacanciesPage($sPageType, $iVacancyId = 0)
	{
		CModule::IncludeModule("iblock");

		$arResult = false;

		//получаем список вакансий
		$oPageInfo = CIBlockElement::GetList(
			array(),
			array(
				'IBLOCK_ID' => CIBlockTools::GetIBlockId('vacancies'),
				'ACTIVE' => 'Y',
				'ID' => ($iVacancyId > 0) ? $iVacancyId : ''
			),
			false,
			false,
			array(
				'ID',
				'NAME',
				'DETAIL_TEXT',
				'CODE',
				'DETAIL_PAGE_URL',
				'PREVIEW_TEXT',
			)
		);
		//формируем ответ
		while ($arPageInfo = $oPageInfo->GetNext())
		{
			$this->arInfo['id'] = $arPageInfo['ID'];
			$this->arInfo['type'] = $sPageType;
			$this->arInfo['title'] = $arPageInfo['NAME'];

			$this->arInfo['details'] = ($arPageInfo['PREVIEW_TEXT'] ?: '');
			$this->arInfo['details'] = \Bitrix\Main\Text\String::htmlDecode($this->arInfo['details']);
			$this->arInfo['details'] = \Bitrix\Main\Text\String::htmlDecode($this->arInfo['details']);
			$this->arInfo['details'] = strip_tags($this->arInfo['details']);
			$this->arInfo['details'] = str_replace('"', '', $this->arInfo['details']);
			$this->arInfo['details'] = htmlspecialcharsbx($this->arInfo['details']);

			$this->arInfo['html'] = $this->correctionLinkInText($arPageInfo['DETAIL_TEXT']);
			$this->arInfo['html'] = htmlspecialcharsbx($this->arInfo['html']);

			$this->arInfo['web_url'] = 'https://'.SITE_SERVER_NAME_API.$arPageInfo['DETAIL_PAGE_URL'];

			$arResult[] = $this->arInfo;
		}

		return $arResult;
	}

	protected function GetСompetitionPage($sPageType, $iСompetitionId = 0)
	{
		CModule::IncludeModule("iblock");

		$arResult = false;
		global $DB;

		//получаем список активных конкурсов (разделов) а также информацию по ним
		$oСompetitionSections = CIBlockSection::GetList(
				array(),
				array(
					'IBLOCK_ID' => CIBlockTools::GetIBlockId('actions'),
					'ID' => ($iСompetitionId > 0) ? $iСompetitionId : '',
					'ACTIVE' => 'Y',
					'>=UF_PERIOD_END' => date($DB->DateFormatToPHP(CLang::GetDateFormat("FULL"))),
					'!UF_ARHIVE' => true
				),
				false,
				array(
					'ID',
					'NAME',
					'DESCRIPTION',
					'DETAIL_PICTURE',
					'SECTION_PAGE_URL',
					'UF_PERIOD_START',
					'UF_PERIOD_END',
					'UF_ARHIVE'
				)
		);

		while ($arCompetitionSection = $oСompetitionSections->GetNext())
		{
			$arSectionInfo[$arCompetitionSection['ID']] = array(
				'ID' => $arCompetitionSection['ID'],
					'NAME' => $arCompetitionSection['NAME'],
					'DESCRIPTION' => htmlspecialchars($arCompetitionSection['DESCRIPTION']),
					'DETAIL_PICTURE' => ($arCompetitionSection['DETAIL_PICTURE']) ? 'https://'.SITE_SERVER_NAME_API.CFile::GetPath($arCompetitionSection['DETAIL_PICTURE']) : '',
					'SECTION_PAGE_URL' => 'https://'.SITE_SERVER_NAME_API.$arCompetitionSection['SECTION_PAGE_URL'],
					'UF_PERIOD_START' => ($arCompetitionSection['UF_PERIOD_START']) ? date(API_DATE_FORMAT, strtotime($arCompetitionSection['UF_PERIOD_START'])) : '' ,
					'UF_PERIOD_END' => ($arCompetitionSection['UF_PERIOD_END']) ? date(API_DATE_FORMAT, strtotime($arCompetitionSection['UF_PERIOD_END'])) : '',
			);
		}

		if ($arSectionInfo)
		{
			//получаем все активные фотографии участников конкурса
			$oCompetitions = CIBlockElement::GetList(
				array(
					'ID' => 'DESC'
				),
				array(
					'IBLOCK_ID' => CIBlockTools::GetIBlockId('actions'),
					'ACTIVE' => 'Y',
					'SECTION_ID' => array_keys($arSectionInfo),
				),
				false,
				false,
				array(
					'ID',
					'NAME',
					'IBLOCK_SECTION_ID',
					'DETAIL_PICTURE',
					'PROPERTY_rating',
					'PROPERTY_vote_count'
				)
			);

			while ($arCompetition = $oCompetitions->Fetch())
			{
				if ($arCompetition['DETAIL_PICTURE'] > 0) {
					$picture = \CFile::ResizeImageGet($arCompetition['DETAIL_PICTURE'], array('width'=>'300', 'height'=>'300'), BX_RESIZE_IMAGE_PROPORTIONAL, true);
				}

				$arCompetitionPhotos[$arCompetition['IBLOCK_SECTION_ID']][] = array(
					'id' => $arCompetition['ID'],
					'title'	=> $arCompetition['NAME'],
					'icon' => 'https://'.SITE_SERVER_NAME_API.CFile::GetPath($arCompetition['DETAIL_PICTURE']),
					'icon_preview' => ($arCompetition['DETAIL_PICTURE'] > 0 ? 'https://'.SITE_SERVER_NAME_API.$picture['src'] : ''),
					'rate' => ($arCompetition['PROPERTY_RATING_VALUE']) ? $arCompetition['PROPERTY_RATING_VALUE'] : 0,
					'count' => ($arCompetition['PROPERTY_VOTE_COUNT_VALUE']) ? $arCompetition['PROPERTY_VOTE_COUNT_VALUE'] : 0
				);
			}

			//формируем ответ
			foreach ($arSectionInfo as $iCompetitionId => $arCompValue)
			{
				$this->arInfo['id'] = $arCompValue['ID'];
				$this->arInfo['type'] = $sPageType;
				$this->arInfo['title'] = $arCompValue['NAME'];

				$this->arInfo['date'] = ($arCompValue['UF_PERIOD_START'] ? date(API_DATE_FORMAT, strtotime($arCompValue['UF_PERIOD_START'])) : '');
				$this->arInfo['end_date'] = ($arCompValue['UF_PERIOD_END'] ? date(API_DATE_FORMAT, strtotime($arCompValue['UF_PERIOD_END'])) : '');

				$this->arInfo['details'] = ($arCompValue['DESCRIPTION'] ?: '');
				$this->arInfo['details'] = \Bitrix\Main\Text\String::htmlDecode($this->arInfo['details']);
				$this->arInfo['details'] = \Bitrix\Main\Text\String::htmlDecode($this->arInfo['details']);
				$this->arInfo['details'] = strip_tags($this->arInfo['details']);
				$this->arInfo['details'] = str_replace(array("\r\n", '"'), '', $this->arInfo['details']);
				$this->arInfo['details'] = htmlspecialcharsbx($this->arInfo['details']);

				$this->arInfo['html'] = $this->correctionLinkInText($arCompValue['DESCRIPTION']);
				$this->arInfo['html'] = htmlspecialcharsbx($this->arInfo['html']);

				$this->arInfo['web_url'] = $arCompValue['SECTION_PAGE_URL'];
				$this->arInfo['icon'] = $arCompValue['DETAIL_PICTURE'];
				$this->arInfo['vote_enabled'] = true;
				$this->arInfo['participants'] = $arCompetitionPhotos[$arCompValue['ID']];

				$arResult[] = $this->arInfo;
			}
		}

		return $arResult;
	}

	protected function GetActionPage($sPageType, $elementId = 0, \req_info_fields $oSelect)
	{
		$oCache = \Bitrix\Main\Data\Cache::createInstance();
		$cacheTime = 60 * 60;
		$sStreetsId = implode(',', $arStreetsId);
		$cacheId = md5("info_action_page|{$elementId}|".implode('|', $oSelect->getFields()));
		$cacheDir = '/info_action_page';

		if ($oCache->initCache($cacheTime, $cacheId, $cacheDir)) {
			$arResult = $oCache->getVars();
		} elseif ($oCache->startDataCache()) {
			\Bitrix\Main\Loader::includeModule('iblock');

			$arResult = false;

			//получаем список активных конкурсов (разделов) а также информацию по ним
			$oActions = \CIBlockElement::GetList(
				array('ID' => 'DESC'),
				array(
					'IBLOCK_ID' => \CIBlockTools::GetIBlockId('actions_and_programs'),
					'ID' => ($elementId > 0 ? $elementId : ''),
					'ACTIVE' => 'Y',
					array(
						"LOGIC" => "OR",
						array('>=DATE_ACTIVE_TO' => new \Bitrix\Main\Type\DateTime()),
						array("DATE_ACTIVE_TO" => false),
					),
				),
				false,
				false,
				$oSelect->getFields()
			);

			while ($arAction = $oActions->GetNext()) {
				$arProductsArticle = array();
				$arProduct = array();

				foreach ($oSelect->getFields() as $fieldName) {
					if ($fieldName == 'ID') {
						$arProduct['id'] = $arAction['ID'];
					} elseif ($fieldName == 'PAGE_TYPE') {
						$arProduct['type'] = $sPageType;
					} elseif ($fieldName == 'NAME') {
						$arProduct['title'] = $arAction['NAME'];
					} elseif ($fieldName == 'DATE_ACTIVE_FROM') {
						$arProduct['date'] = ($arAction['DATE_ACTIVE_FROM'] ? date(API_DATE_FORMAT, strtotime($arAction['DATE_ACTIVE_FROM'])) : '');
					} elseif ($fieldName == 'DATE_ACTIVE_TO') {
						$arProduct['end_date'] = ($arAction['DATE_ACTIVE_TO'] ? date(API_DATE_FORMAT, strtotime($arAction['DATE_ACTIVE_TO'])) : '');
					} elseif ($fieldName == 'DETAIL_PAGE_URL') {
						$arProduct['web_url'] = ($arAction['DETAIL_PAGE_URL'] ? 'https://'.SITE_SERVER_NAME_API.$arAction['DETAIL_PAGE_URL'] : '');
					} elseif ($fieldName == 'PREVIEW_PICTURE') {
						$arProduct['icon'] = ($arAction['PREVIEW_PICTURE'] ? 'https://'.SITE_SERVER_NAME_API.\CFile::GetPath($arAction['PREVIEW_PICTURE']) : '');
					} elseif ($fieldName == 'PREVIEW_TEXT') {
						if ($arAction['PREVIEW_TEXT']) {
							$arProduct['details'] = \Bitrix\Main\Text\String::htmlDecode($arAction['PREVIEW_TEXT']);
							$arProduct['details'] = \Bitrix\Main\Text\String::htmlDecode($arProduct['details']);
							$arProduct['details'] = strip_tags($arProduct['details']);
							$arProduct['details'] = str_replace('"', '', $arProduct['details']);
							$arProduct['details'] = htmlspecialcharsbx($arProduct['details']);
						} else {
							$arProduct['details'] = '';
						}
					}
				}

				// выносим обработку из цикла, т.к. порядок обработки для DETAIL_TEXT и SUB_ITEMS важен
				if (in_array('DETAIL_TEXT', $oSelect->getFields())) {
					if ($arAction['DETAIL_TEXT']) {
						// артикулы акционных товаров прописаны в подробном описании акции
						// код ниже находит их, собирает в массив и вырезает из описания
						$p = preg_match_all('/#t_id=(\d+)#/', $arAction['DETAIL_TEXT'], $matches, PREG_PATTERN_ORDER);
						$arProductsArticle = $matches[1];

						$arAction['DETAIL_TEXT'] = preg_replace('/#t_id=\d+#/', '', $arAction['DETAIL_TEXT']);

						$arProduct['html'] = $this->correctionLinkInText($arAction['DETAIL_TEXT']);
						$arProduct['html'] = htmlspecialcharsbx($arProduct['html']);
					} else {
						$arProduct['html'] = '';
					}
				}

				if (in_array('SUB_ITEMS', $oSelect->getFields()) && !empty($arProductsArticle)) {
					// получаем список ID товаров по заданным параметрам
					$arProductsId = array();
					$oGoodsList = new \goods_list;
					$oGoodsList->User = $this->User;

					$oElements = \Bitrix\Iblock\ElementTable::getList(array(
						'filter' => array(
							'=IBLOCK_ID' => ROOT_CATALOG_ID,
							'=XML_ID' => $arProductsArticle,
							'=ACTIVE' => 'Y',
						),
						'select' => array('ID'),
					));

					while ($arElement = $oElements->fetch()) {
						if ($arProductInfo = reset($oGoodsList->GetProdInfo($arElement['ID']))) {
							//получаем количество бонусов по позиции
							$arProductBonus = $oGoodsList->GetProductBonus($arProductInfo['price'],$arProductInfo);

							//округляем
							$arProductInfo['bonus_user'] = ceil($arProductBonus['bonus_user']);
							$arProductInfo['bonus_all'] = ceil($arProductBonus['bonus_all']);

							$arProduct['goods'][] = $arProductInfo;
						}
					}
				}

				$arResult[] = $arProduct;
			}

			$oCache->endDataCache($arResult);
		}

		return $arResult;
	}

	protected function GetArticlesPage($sPageType, $iArticleId = 0)
	{
		CModule::IncludeModule("iblock");

		$arResult = false;

		//получаем список статей
		$oPageInfo = CIBlockElement::GetList(
			array(
				'ACTIVE_FROM' => 'DESC',
				'SORT' => 'ASC'
			),
			array(
				'IBLOCK_ID' => CIBlockTools::GetIBlockId('articles'),
				'ACTIVE' => 'Y',
				'ID' => ($iArticleId > 0) ? $iArticleId : ''
			),
			false,
			array(
				'nTopCount' => 50
			),
			array(
				'ID',
				'NAME',
				'DATE_ACTIVE_FROM',
				'PREVIEW_TEXT',
				'PREVIEW_PICTURE',
				'CODE',
				'DETAIL_PAGE_URL',
				'DETAIL_TEXT',
			)
		);
		//формируем ответ
		while ($arPageInfo = $oPageInfo->GetNext())
		{
			$this->arInfo['id'] = $arPageInfo['ID'];
			$this->arInfo['type'] = $sPageType;
			$this->arInfo['date'] = ($arPageInfo['DATE_ACTIVE_FROM']) ? date(API_DATE_FORMAT, strtotime($arPageInfo['DATE_ACTIVE_FROM'])) : '';
			$this->arInfo['title'] = $arPageInfo['NAME'];

			$this->arInfo['details'] = ($arPageInfo['PREVIEW_TEXT'] ?: '');
			$this->arInfo['details'] = \Bitrix\Main\Text\String::htmlDecode($this->arInfo['details']);
			$this->arInfo['details'] = \Bitrix\Main\Text\String::htmlDecode($this->arInfo['details']);
			$this->arInfo['details'] = strip_tags($this->arInfo['details']);
			$this->arInfo['details'] = str_replace('"', '', $this->arInfo['details']);
			$this->arInfo['details'] = htmlspecialcharsbx($this->arInfo['details']);

			$this->arInfo['html'] = $this->correctionLinkInText($arPageInfo['DETAIL_TEXT']);
			$this->arInfo['html'] = preg_replace('/#t_id=\d+#/', '', $this->arInfo['html']);
			$this->arInfo['html'] = htmlspecialcharsbx($this->arInfo['html']);

			$this->arInfo['icon'] = ($arPageInfo['PREVIEW_PICTURE']) ? 'https://'.SITE_SERVER_NAME_API.CFile::GetPath($arPageInfo['PREVIEW_PICTURE']) : '';
			$this->arInfo['web_url'] = 'https://'.SITE_SERVER_NAME_API.$arPageInfo['DETAIL_PAGE_URL'].'?MOB_APP=Y';

			$arResult[] = $this->arInfo;
		}

		return $arResult;
	}

	protected function GetNewsPage($sPageType, $iNewsId = 0)
	{
		CModule::IncludeModule("iblock");

		$arResult = false;

		//получаем список новостей
		$oPageInfo = CIBlockElement::GetList(
			array(
				'ACTIVE_FROM' => 'DESC',
				'SORT' => 'ASC'
			),
			array(
				'IBLOCK_ID' => CIBlockTools::GetIBlockId('news'),
				'ACTIVE' => 'Y',
				'ID' => ($iNewsId > 0) ? $iNewsId : ''
			),
			false,
			array(
				'nTopCount' => 50
			),
			array(
				'ID',
				'NAME',
				'DATE_ACTIVE_FROM',
				'PREVIEW_TEXT',
				'PREVIEW_PICTURE',
				'CODE',
				'DETAIL_PAGE_URL',
				'DETAIL_TEXT',
			)
		);
		//формируем ответ
		while ($arPageInfo = $oPageInfo->GetNext())
		{
			$this->arInfo['id'] = $arPageInfo['ID'];
			$this->arInfo['type'] = $sPageType;
			$this->arInfo['date'] = ($arPageInfo['DATE_ACTIVE_FROM']) ? date(API_DATE_FORMAT, strtotime($arPageInfo['DATE_ACTIVE_FROM'])) : '';
			$this->arInfo['title'] = $arPageInfo['NAME'];

			$this->arInfo['details'] = ($arPageInfo['PREVIEW_TEXT'] ?: '');
			$this->arInfo['details'] = \Bitrix\Main\Text\String::htmlDecode($this->arInfo['details']);
			$this->arInfo['details'] = \Bitrix\Main\Text\String::htmlDecode($this->arInfo['details']);
			$this->arInfo['details'] = strip_tags($this->arInfo['details']);
			$this->arInfo['details'] = str_replace('"', '', $this->arInfo['details']);
			$this->arInfo['details'] = htmlspecialcharsbx($this->arInfo['details']);

			$this->arInfo['html'] = $this->correctionLinkInText($arPageInfo['DETAIL_TEXT']);
			$this->arInfo['html'] = htmlspecialcharsbx($this->arInfo['html']);

			$this->arInfo['icon'] = ($arPageInfo['PREVIEW_PICTURE']) ? 'https://'.SITE_SERVER_NAME_API.CFile::GetPath($arPageInfo['PREVIEW_PICTURE']) : '';
			$this->arInfo['web_url'] = 'https://'.SITE_SERVER_NAME_API.$arPageInfo['DETAIL_PAGE_URL'].'?MOB_APP=Y';

			$arResult[] = $this->arInfo;
		}

		return $arResult;
	}

	protected function GetDeliveryPage($pageType, $cityId = 0)
	{
		$arResult = array();

		if ($cityId > 0) {
			$arResult = array(
				'id' => $cityId,
				'type' => $pageType,
				'title' => \GeoCatalog::GetRegionPhone(false, $cityId),
				'html' => \GeoCatalog::GetRegionDeliveryText($cityId),
			);

			$arResult['details'] = \Bitrix\Main\Text\String::htmlDecode($arResult['html']);
			$arResult['details'] = \Bitrix\Main\Text\String::htmlDecode($arResult['details']);
			$arResult['details'] = strip_tags($arResult['details']);
			$arResult['details'] = str_replace('"', '', $arResult['details']);
			$arResult['details'] = htmlspecialcharsbx($arResult['details']);

			$arResult['html'] = $this->correctionLinkInText($arResult['html']);
			$arResult['html'] = htmlspecialcharsbx($arResult['html']);
		}

		return $arResult;
	}

	public function get($arInput)
	{
		// проверяем существование ключей
		$sPageType = (isset($arInput['type']) and !empty($arInput['type'])) ? $arInput['type'] : null;
		$iCityID = (isset($arInput['city_id']) and !empty($arInput['city_id'])) ? $arInput['city_id'] : null;

		$oSelect = new \req_info_fields($arInput);

		if ($sPageType and $iCityID)
		{
			$iInfoId = (isset($arInput['info_id']) and !empty($arInput['info_id'])) ? $arInput['info_id'] : 0;

			switch ($sPageType)
			{
				case 'register_terms':
					$arResInfo = $this->GetStaticPage($sPageType);
					break;

				case 'bonus_card_info':
					$arResInfo = $this->GetStaticPage($sPageType);
					break;

				case 'obtain_bonus_card':
					$arResInfo = $this->GetStaticPage($sPageType);
					break;

				case 'contacts':
					$arResInfo = $this->GetStaticPage($sPageType);
					break;

				case 'about':
					$arResInfo = $this->GetStaticPage($sPageType);
					break;

				case 'vacance':
					$arResInfo = $this->GetVacanciesPage($sPageType, $iInfoId);
					break;

				case 'letters':
					$arResInfo = $this->GetArticlesPage($sPageType, $iInfoId);
					break;

				case 'news':
					$arResInfo = $this->GetNewsPage($sPageType, $iInfoId);
					break;

				case 'action':
					$arResInfo = $this->GetActionPage($sPageType, $iInfoId, $oSelect);
					break;

				case 'delivery':
					$arResInfo = $this->GetDeliveryPage($sPageType, $iCityID);
					break;

				case 'competition':
					$arResInfo = $this->GetСompetitionPage($sPageType, $iInfoId);
					break;

				default:
					$this->addError('wrong_input_file_type');
					break;
			}

			if ($arResInfo)
				$arResult['info'] = $arResInfo;
		}
		else
			$this->addError('required_params_missed');

		return($arResult);
	}

	private function correctionLinkInText($text)
	{
		if (strlen($text) > 0) {
			$dom = new \DomDocument('1.0', 'utf-8');
			$text = mb_convert_encoding($text, 'HTML-ENTITIES', 'utf8');
			$dom->loadHTML($text);

			foreach ($dom->getElementsByTagName('a') as $node) {
				$href = $node->getAttribute('href');

				if (strpos($href, 'http://') !== 0
					&& strpos($href, 'https://') !== 0
					&& strpos($href, 'mailto:') !== 0
					&& strpos($href, 'tel:') !== 0
				) {
					$node->setAttribute('href', 'https://'.SITE_SERVER_NAME_API.$href);
				}
			}

			foreach ($dom->getElementsByTagName('img') as $node) {
				$src = $node->getAttribute('src');

				if (strpos($src, 'http://') !== 0
					&& strpos($src, 'https://') !== 0
				) {
					$node->setAttribute('src', 'https://'.SITE_SERVER_NAME_API.$src);
				}
			}

			$text = preg_replace(array('|^\<\!DOCTYPE.*?<html><body>|si', '|</body></html>$|si'), '', $dom->saveHTML());
			$text = mb_convert_encoding($text, 'utf8', 'HTML-ENTITIES');

			unset($dom, $node, $href, $src);
		}

		return $text;
	}
}
