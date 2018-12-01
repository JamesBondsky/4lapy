<?

use FourPaws\MobileApiOldBundle\GeoCatalog;
use FourPaws\MobileApiOldBundle\Ajax;

class city extends \stdClass
{
	public static function getListDefaultSite()
	{
		\Bitrix\Main\Loader::includeModule('sale');

		$arResult = array();
		$arCities = array();

		// $oCities = \Bitrix\Sale\Location\DefaultSiteTable::getList(array(
		// 	'order' => array('LOCATION.PARENTS.LEFT_MARGIN' => 'DESC'),
		// 	'filter' => array(
		// 		'=SITE_ID' => SITE_ID,
		// 		'=LOCATION.PARENTS.NAME.LANGUAGE_ID' => 'ru',
		// 	),
		// 	'select' => array(
		// 		'ID' => 'LOCATION.ID',
		// 		'PARENT_ID' => 'LOCATION.PARENTS.ID',
		// 		'PARENT_NAME' => 'LOCATION.PARENTS.NAME.NAME',
		// 		'SORT',
				// 'LAT' => 'LOCATION.LATITUDE',
				// 'LON' => 'LOCATION.LONGITUDE'
		// 	)
		// ));

		$ttt = Array(
			Array('ID' => 67221,	'PARENT_ID' => 67221,	'PARENT_NAME' => 'Зеленоград',	'SORT' => 132,	'LAT' => '55.981308',	'LON' => '37.119711'),
			Array('ID' => 540,	'PARENT_ID' => 540,	'PARENT_NAME' => 'Железнодорожный',	'SORT' => 134,	'LAT' => '55.747176',	'LON' => '38.016837'),
			Array('ID' => 561,	'PARENT_ID' => 561,	'PARENT_NAME' => 'Жуковский',	'SORT' => 133,	'LAT' => '55.603589',	'LON' => '38.107745'),
			Array('ID' => 586,	'PARENT_ID' => 586,	'PARENT_NAME' => 'Дубна',	'SORT' => 136,	'LAT' => '56.735717',	'LON' => '37.158953'),
			Array('ID' => 603,	'PARENT_ID' => 603,	'PARENT_NAME' => 'Королев',	'SORT' => 128,	'LAT' => '55.918803',	'LON' => '37.853773'),
			Array('ID' => 665,	'PARENT_ID' => 665,	'PARENT_NAME' => 'Климовск',	'SORT' => 131,	'LAT' => '55.385881',	'LON' => '37.541747'),
			Array('ID' => 672,	'PARENT_ID' => 672,	'PARENT_NAME' => 'Реутов',	'SORT' => 112,	'LAT' => '55.752283',	'LON' => '37.887161'),
			Array('ID' => 675,	'PARENT_ID' => 675,	'PARENT_NAME' => 'Лобня',	'SORT' => 125,	'LAT' => '56.012630',	'LON' => '37.481519'),
			Array('ID' => 684,	'PARENT_ID' => 684,	'PARENT_NAME' => 'Лыткарино',	'SORT' => 123,	'LAT' => '55.584009',	'LON' => '37.908869'),
			Array('ID' => 726,	'PARENT_ID' => 726,	'PARENT_NAME' => 'Фрязино',	'SORT' => 107,	'LAT' => '55.949788',	'LON' => '38.059295'),
			Array('ID' => 728,	'PARENT_ID' => 728,	'PARENT_NAME' => 'Электросталь',	'SORT' => 102,	'LAT' => '55.784747',	'LON' => '38.444367'),
			Array('ID' => 789,	'PARENT_ID' => 789,	'PARENT_NAME' => 'Орехово-Зуево',	'SORT' => 118,	'LAT' => '55.805602',	'LON' => '38.984100'),
			Array('ID' => 791,	'PARENT_ID' => 791,	'PARENT_NAME' => 'Юбилейный',	'SORT' => 101,	'LAT' => '55.936807',	'LON' => '37.843578'),
			Array('ID' => 792,	'PARENT_ID' => 792,	'PARENT_NAME' => 'Дзержинский',	'SORT' => 139,	'LAT' => '55.630334',	'LON' => '37.849319'),
			Array('ID' => 793,	'PARENT_ID' => 793,	'PARENT_NAME' => 'Коломна',	'SORT' => 129,	'LAT' => '55.084244',	'LON' => '38.804127'),
			Array('ID' => 845,	'PARENT_ID' => 845,	'PARENT_NAME' => 'Подольск',	'SORT' => 115,	'LAT' => '55.425682',	'LON' => '37.545595'),
			Array('ID' => 847,	'PARENT_ID' => 847,	'PARENT_NAME' => 'Долгопрудный',	'SORT' => 137,	'LAT' => '55.938609',	'LON' => '37.514905'),
			Array('ID' => 869,	'PARENT_ID' => 869,	'PARENT_NAME' => 'Химки',	'SORT' => 106,	'LAT' => '55.889015',	'LON' => '37.434152'),
			Array('ID' => 955,	'PARENT_ID' => 955,	'PARENT_NAME' => 'Серпухов',	'SORT' => 110,	'LAT' => '54.914920',	'LON' => '37.417793'),
			Array('ID' => 994,	'PARENT_ID' => 994,	'PARENT_NAME' => 'Балашиха',	'SORT' => 143,	'LAT' => '55.814495',	'LON' => '37.954673'),
			Array('ID' => 1944,	'PARENT_ID' => 1944,	'PARENT_NAME' => 'Воскресенск',	'SORT' => 140,	'LAT' => '55.320983',	'LON' => '38.670835'),
			Array('ID' => 1944,	'PARENT_ID' => 1682,	'PARENT_NAME' => 'Воскресенский район',	'SORT' => 140,	'LAT' => '55.320983',	'LON' => '38.670835'),
			Array('ID' => 2331,	'PARENT_ID' => 2331,	'PARENT_NAME' => 'Дмитров',	'SORT' => 138,	'LAT' => '56.343394',	'LON' => '37.518642'),
			Array('ID' => 2331,	'PARENT_ID' => 1984,	'PARENT_NAME' => 'Дмитровский район',	'SORT' => 138,	'LAT' => '56.343394',	'LON' => '37.518642'),
			Array('ID' => 2898,	'PARENT_ID' => 2898,	'PARENT_NAME' => 'Егорьевск',	'SORT' => 135,	'LAT' => '55.377627',	'LON' => '39.045778'),
			Array('ID' => 2898,	'PARENT_ID' => 2444,	'PARENT_NAME' => 'Егорьевский район',	'SORT' => 135,	'LAT' => '55.377627',	'LON' => '39.045778'),
			Array('ID' => 3820,	'PARENT_ID' => 3820,	'PARENT_NAME' => 'Клин',	'SORT' => 130,	'LAT' => '0.000000',	'LON' => '0.000000'),
			Array('ID' => 3820,	'PARENT_ID' => 3683,	'PARENT_NAME' => 'Клинский район',	'SORT' => 130,	'LAT' => '0.000000',	'LON' => '0.000000'),
			Array('ID' => 4341,	'PARENT_ID' => 4341,	'PARENT_NAME' => 'Красногорск',	'SORT' => 126,	'LAT' => '55.816955',	'LON' => '37.353547'),
			Array('ID' => 4362,	'PARENT_ID' => 4362,	'PARENT_NAME' => 'Отрадное посёлок',	'SORT' => 117,	'LAT' => '56.143037',	'LON' => '40.417330'),
			Array('ID' => 4341,	'PARENT_ID' => 4279,	'PARENT_NAME' => 'Красногорский район',	'SORT' => 126,	'LAT' => '55.816955',	'LON' => '37.353547'),
			Array('ID' => 4362,	'PARENT_ID' => 4279,	'PARENT_NAME' => 'Красногорский район',	'SORT' => 117,	'LAT' => '56.143037',	'LON' => '40.417330'),
			Array('ID' => 4487,	'PARENT_ID' => 4487,	'PARENT_NAME' => 'Видное',	'SORT' => 141,	'LAT' => '55.551507',	'LON' => '37.702346'),
			Array('ID' => 4487,	'PARENT_ID' => 4408,	'PARENT_NAME' => 'Ленинский район',	'SORT' => 141,	'LAT' => '55.551507',	'LON' => '37.702346'),
			Array('ID' => 4797,	'PARENT_ID' => 4797,	'PARENT_NAME' => 'Луховицы',	'SORT' => 124,	'LAT' => '54.964903',	'LON' => '39.025802'),
			Array('ID' => 4797,	'PARENT_ID' => 4630,	'PARENT_NAME' => 'Луховицкий район',	'SORT' => 124,	'LAT' => '54.964903',	'LON' => '39.025802'),
			Array('ID' => 4829,	'PARENT_ID' => 4829,	'PARENT_NAME' => 'Красково посёлок',	'SORT' => 127,	'LAT' => '55.660087',	'LON' => '37.976871'),
			Array('ID' => 4845,	'PARENT_ID' => 4845,	'PARENT_NAME' => 'Люберцы',	'SORT' => 122,	'LAT' => '55.681999',	'LON' => '37.893508'),
			Array('ID' => 4939,	'PARENT_ID' => 4939,	'PARENT_NAME' => 'Томилино посёлок',	'SORT' => 108,	'LAT' => '55.655511',	'LON' => '37.952912'),
			Array('ID' => 4939,	'PARENT_ID' => 4828,	'PARENT_NAME' => 'Люберецкий район',	'SORT' => 108,	'LAT' => '55.655511',	'LON' => '37.952912'),
			Array('ID' => 4829,	'PARENT_ID' => 4828,	'PARENT_NAME' => 'Люберецкий район',	'SORT' => 127,	'LAT' => '55.660087',	'LON' => '37.976871'),
			Array('ID' => 4845,	'PARENT_ID' => 4828,	'PARENT_NAME' => 'Люберецкий район',	'SORT' => 122,	'LAT' => '55.681999',	'LON' => '37.893508'),
			Array('ID' => 5809,	'PARENT_ID' => 5809,	'PARENT_NAME' => 'Мытищи',	'SORT' => 121,	'LAT' => '55.916288',	'LON' => '37.754869'),
			Array('ID' => 5809,	'PARENT_ID' => 5677,	'PARENT_NAME' => 'Мытищинский район',	'SORT' => 121,	'LAT' => '55.916288',	'LON' => '37.754869'),
			Array('ID' => 6571,	'PARENT_ID' => 6571,	'PARENT_NAME' => 'Ногинск',	'SORT' => 120,	'LAT' => '55.854566',	'LON' => '38.441844'),
			Array('ID' => 6571,	'PARENT_ID' => 6338,	'PARENT_NAME' => 'Ногинский район',	'SORT' => 120,	'LAT' => '55.854566',	'LON' => '38.441844'),
			Array('ID' => 7129,	'PARENT_ID' => 7129,	'PARENT_NAME' => 'Одинцово',	'SORT' => 119,	'LAT' => '55.672860',	'LON' => '37.280419'),
			Array('ID' => 7129,	'PARENT_ID' => 6934,	'PARENT_NAME' => 'Одинцовский район',	'SORT' => 119,	'LAT' => '55.672860',	'LON' => '37.280419'),
			Array('ID' => 7941,	'PARENT_ID' => 7941,	'PARENT_NAME' => 'Павловский Посад',	'SORT' => 116,	'LAT' => '55.770975',	'LON' => '38.654751'),
			Array('ID' => 7941,	'PARENT_ID' => 7921,	'PARENT_NAME' => 'Павлово-Посадский район',	'SORT' => 116,	'LAT' => '55.770975',	'LON' => '38.654751'),
			Array('ID' => 8738,	'PARENT_ID' => 8738,	'PARENT_NAME' => 'Пушкино',	'SORT' => 114,	'LAT' => '56.003731',	'LON' => '37.852517'),
			Array('ID' => 8738,	'PARENT_ID' => 8357,	'PARENT_NAME' => 'Пушкинский район',	'SORT' => 114,	'LAT' => '56.003731',	'LON' => '37.852517'),
			Array('ID' => 9082,	'PARENT_ID' => 9082,	'PARENT_NAME' => 'Быково рабочий посёлок',	'SORT' => 142,	'LAT' => '56.143037',	'LON' => '40.417330'),
			Array('ID' => 9498,	'PARENT_ID' => 9498,	'PARENT_NAME' => 'Раменское',	'SORT' => 113,	'LAT' => '55.572219',	'LON' => '38.236017'),
			Array('ID' => 9082,	'PARENT_ID' => 8788,	'PARENT_NAME' => 'Раменский район',	'SORT' => 142,	'LAT' => '56.143037',	'LON' => '40.417330'),
			Array('ID' => 9498,	'PARENT_ID' => 8788,	'PARENT_NAME' => 'Раменский район',	'SORT' => 113,	'LAT' => '55.572219',	'LON' => '38.236017'),
			Array('ID' => 10559,	'PARENT_ID' => 10559,	'PARENT_NAME' => 'Сергиев Посад',	'SORT' => 111,	'LAT' => '56.330735',	'LON' => '38.130244'),
			Array('ID' => 10559,	'PARENT_ID' => 10195,	'PARENT_NAME' => 'Сергиево-Посадский район',	'SORT' => 111,	'LAT' => '56.330735',	'LON' => '38.130244'),
			Array('ID' => 12289,	'PARENT_ID' => 12289,	'PARENT_NAME' => 'Солнечногорск',	'SORT' => 109,	'LAT' => '56.186296',	'LON' => '36.977021'),
			Array('ID' => 12289,	'PARENT_ID' => 11807,	'PARENT_NAME' => 'Солнечногорский район',	'SORT' => 109,	'LAT' => '56.186296',	'LON' => '36.977021'),
			Array('ID' => 13807,	'PARENT_ID' => 13807,	'PARENT_NAME' => 'Чехов',	'SORT' => 105,	'LAT' => '55.157251',	'LON' => '37.466074'),
			Array('ID' => 13807,	'PARENT_ID' => 13380,	'PARENT_NAME' => 'Чеховский район',	'SORT' => 105,	'LAT' => '55.157251',	'LON' => '37.466074'),
			Array('ID' => 14354,	'PARENT_ID' => 14354,	'PARENT_NAME' => 'Щелково',	'SORT' => 104,	'LAT' => '55.922251',	'LON' => '38.003236'),
			Array('ID' => 14354,	'PARENT_ID' => 14214,	'PARENT_NAME' => 'Щелковский район',	'SORT' => 104,	'LAT' => '55.922251',	'LON' => '38.003236'),
			Array('ID' => 728,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 102,	'LAT' => '55.784747',	'LON' => '38.444367'),
			Array('ID' => 4487,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 141,	'LAT' => '55.551507',	'LON' => '37.702346'),
			Array('ID' => 675,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 125,	'LAT' => '56.012630',	'LON' => '37.481519'),
			Array('ID' => 3820,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 130,	'LAT' => '0.000000',	'LON' => '0.000000'),
			Array('ID' => 12289,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 109,	'LAT' => '56.186296',	'LON' => '36.977021'),
			Array('ID' => 603,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 128,	'LAT' => '55.918803',	'LON' => '37.853773'),
			Array('ID' => 1944,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 140,	'LAT' => '55.320983',	'LON' => '38.670835'),
			Array('ID' => 9082,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 142,	'LAT' => '56.143037',	'LON' => '40.417330'),
			Array('ID' => 540,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 134,	'LAT' => '55.747176',	'LON' => '38.016837'),
			Array('ID' => 869,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 106,	'LAT' => '55.889015',	'LON' => '37.434152'),
			Array('ID' => 7129,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 119,	'LAT' => '55.672860',	'LON' => '37.280419'),
			Array('ID' => 793,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 129,	'LAT' => '55.084244',	'LON' => '38.804127'),
			Array('ID' => 4939,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 108,	'LAT' => '55.655511',	'LON' => '37.952912'),
			Array('ID' => 789,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 118,	'LAT' => '55.805602',	'LON' => '38.984100'),
			Array('ID' => 4797,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 124,	'LAT' => '54.964903',	'LON' => '39.025802'),
			Array('ID' => 684,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 123,	'LAT' => '55.584009',	'LON' => '37.908869'),
			Array('ID' => 4341,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 126,	'LAT' => '55.816955',	'LON' => '37.353547'),
			Array('ID' => 13807,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 105,	'LAT' => '55.157251',	'LON' => '37.466074'),
			Array('ID' => 665,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 131,	'LAT' => '55.385881',	'LON' => '37.541747'),
			Array('ID' => 2331,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 138,	'LAT' => '56.343394',	'LON' => '37.518642'),
			Array('ID' => 9498,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 113,	'LAT' => '55.572219',	'LON' => '38.236017'),
			Array('ID' => 561,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 133,	'LAT' => '55.603589',	'LON' => '38.107745'),
			Array('ID' => 955,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 110,	'LAT' => '54.914920',	'LON' => '37.417793'),
			Array('ID' => 7941,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 116,	'LAT' => '55.770975',	'LON' => '38.654751'),
			Array('ID' => 845,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 115,	'LAT' => '55.425682',	'LON' => '37.545595'),
			Array('ID' => 5809,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 121,	'LAT' => '55.916288',	'LON' => '37.754869'),
			Array('ID' => 67221,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 132,	'LAT' => '55.981308',	'LON' => '37.119711'),
			Array('ID' => 791,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 101,	'LAT' => '55.936807',	'LON' => '37.843578'),
			Array('ID' => 4829,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 127,	'LAT' => '55.660087',	'LON' => '37.976871'),
			Array('ID' => 726,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 107,	'LAT' => '55.949788',	'LON' => '38.059295'),
			Array('ID' => 4362,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 117,	'LAT' => '56.143037',	'LON' => '40.417330'),
			Array('ID' => 14354,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 104,	'LAT' => '55.922251',	'LON' => '38.003236'),
			Array('ID' => 672,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 112,	'LAT' => '55.752283',	'LON' => '37.887161'),
			Array('ID' => 2898,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 135,	'LAT' => '55.377627',	'LON' => '39.045778'),
			Array('ID' => 10559,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 111,	'LAT' => '56.330735',	'LON' => '38.130244'),
			Array('ID' => 586,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 136,	'LAT' => '56.735717',	'LON' => '37.158953'),
			Array('ID' => 994,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 143,	'LAT' => '55.814495',	'LON' => '37.954673'),
			Array('ID' => 8738,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 114,	'LAT' => '56.003731',	'LON' => '37.852517'),
			Array('ID' => 847,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 137,	'LAT' => '55.938609',	'LON' => '37.514905'),
			Array('ID' => 6571,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 120,	'LAT' => '55.854566',	'LON' => '38.441844'),
			Array('ID' => 792,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 139,	'LAT' => '55.630334',	'LON' => '37.849319'),
			Array('ID' => 4845,	'PARENT_ID' => 1,	'PARENT_NAME' => 'Московская область',	'SORT' => 122,	'LAT' => '55.681999',	'LON' => '37.893508'),
			Array('ID' => 14620,	'PARENT_ID' => 14620,	'PARENT_NAME' => 'Иваново',	'SORT' => 900,	'LAT' => '56.999599',	'LON' => '40.991774'),
			Array('ID' => 14620,	'PARENT_ID' => 2,	'PARENT_NAME' => 'Ивановская область',	'SORT' => 900,	'LAT' => '56.999599',	'LON' => '40.991774'),
			Array('ID' => 18661,	'PARENT_ID' => 18661,	'PARENT_NAME' => 'Калуга',	'SORT' => 870,	'LAT' => '54.506345',	'LON' => '36.258237'),
			Array('ID' => 18922,	'PARENT_ID' => 18922,	'PARENT_NAME' => 'Обнинск',	'SORT' => 760,	'LAT' => '55.115276',	'LON' => '36.588850'),
			Array('ID' => 18661,	'PARENT_ID' => 3,	'PARENT_NAME' => 'Калужская область',	'SORT' => 870,	'LAT' => '54.506345',	'LON' => '36.258237'),
			Array('ID' => 18922,	'PARENT_ID' => 3,	'PARENT_NAME' => 'Калужская область',	'SORT' => 760,	'LAT' => '55.115276',	'LON' => '36.588850'),
			Array('ID' => 22959,	'PARENT_ID' => 22959,	'PARENT_NAME' => 'Кострома',	'SORT' => 840,	'LAT' => '57.746395',	'LON' => '40.914058'),
			Array('ID' => 22959,	'PARENT_ID' => 22743,	'PARENT_NAME' => 'Костромской район',	'SORT' => 840,	'LAT' => '57.746395',	'LON' => '40.914058'),
			Array('ID' => 22959,	'PARENT_ID' => 4,	'PARENT_NAME' => 'Костромская область',	'SORT' => 840,	'LAT' => '57.746395',	'LON' => '40.914058'),
			Array('ID' => 26924,	'PARENT_ID' => 26924,	'PARENT_NAME' => 'Липецк',	'SORT' => 780,	'LAT' => '52.575095',	'LON' => '39.523995'),
			Array('ID' => 26924,	'PARENT_ID' => 5,	'PARENT_NAME' => 'Липецкая область',	'SORT' => 780,	'LAT' => '52.575095',	'LON' => '39.523995'),
			Array('ID' => 28624,	'PARENT_ID' => 28624,	'PARENT_NAME' => 'Орёл',	'SORT' => 740,	'LAT' => '52.966057',	'LON' => '36.066981'),
			Array('ID' => 28624,	'PARENT_ID' => 6,	'PARENT_NAME' => 'Орловская область',	'SORT' => 740,	'LAT' => '52.966057',	'LON' => '36.066981'),
			Array('ID' => 32102,	'PARENT_ID' => 32102,	'PARENT_NAME' => 'Рязань',	'SORT' => 720,	'LAT' => '54.626600',	'LON' => '39.786100'),
			Array('ID' => 32102,	'PARENT_ID' => 7,	'PARENT_NAME' => 'Рязанская область',	'SORT' => 720,	'LAT' => '54.626600',	'LON' => '39.786100'),
			Array('ID' => 35106,	'PARENT_ID' => 35106,	'PARENT_NAME' => 'Тверь',	'SORT' => 680,	'LAT' => '56.815697',	'LON' => '35.885140'),
			Array('ID' => 35106,	'PARENT_ID' => 8,	'PARENT_NAME' => 'Тверская область',	'SORT' => 680,	'LAT' => '56.815697',	'LON' => '35.885140'),
			Array('ID' => 45546,	'PARENT_ID' => 45546,	'PARENT_NAME' => 'Ярославль',	'SORT' => 640,	'LAT' => '57.650413',	'LON' => '39.874253'),
			Array('ID' => 45546,	'PARENT_ID' => 9,	'PARENT_NAME' => 'Ярославская область',	'SORT' => 640,	'LAT' => '57.650413',	'LON' => '39.874253'),
			Array('ID' => 52883,	'PARENT_ID' => 52883,	'PARENT_NAME' => 'Воронеж',	'SORT' => 950,	'LAT' => '51.710864',	'LON' => '39.159649'),
			Array('ID' => 52883,	'PARENT_ID' => 10,	'PARENT_NAME' => 'Воронежская область',	'SORT' => 950,	'LAT' => '51.710864',	'LON' => '39.159649'),
			Array('ID' => 55277,	'PARENT_ID' => 55277,	'PARENT_NAME' => 'Владимир',	'SORT' => 1000,	'LAT' => '56.143037',	'LON' => '40.417330'),
			Array('ID' => 55277,	'PARENT_ID' => 11,	'PARENT_NAME' => 'Владимирская область',	'SORT' => 1000,	'LAT' => '56.143037',	'LON' => '40.417330'),
			Array('ID' => 67310,	'PARENT_ID' => 67310,	'PARENT_NAME' => 'Щербинка',	'SORT' => 103,	'LAT' => '55.506914',	'LON' => '37.567354'),
			Array('ID' => 12,	'PARENT_ID' => 12,	'PARENT_NAME' => 'Москва',	'SORT' => 770,	'LAT' => '55.750718',	'LON' => '37.617661'),
			Array('ID' => 67310,	'PARENT_ID' => 12,	'PARENT_NAME' => 'Москва',	'SORT' => 103,	'LAT' => '55.506914',	'LON' => '37.567354'),
			Array('ID' => 58976,	'PARENT_ID' => 58976,	'PARENT_NAME' => 'Волгоград',	'SORT' => 980,	'LAT' => '48.743900',	'LON' => '44.512132'),
			Array('ID' => 58976,	'PARENT_ID' => 13,	'PARENT_NAME' => 'Волгоградская область',	'SORT' => 980,	'LAT' => '48.743900',	'LON' => '44.512132'),
			Array('ID' => 61358,	'PARENT_ID' => 61358,	'PARENT_NAME' => 'Нижний Новгород',	'SORT' => 770,	'LAT' => '56.291126',	'LON' => '43.984374'),
			Array('ID' => 61358,	'PARENT_ID' => 14,	'PARENT_NAME' => 'Нижегородская область',	'SORT' => 770,	'LAT' => '56.291126',	'LON' => '43.984374'),
			Array('ID' => 67687,	'PARENT_ID' => 67687,	'PARENT_NAME' => 'Тула',	'SORT' => 660,	'LAT' => '54.196100',	'LON' => '37.618200'),
			Array('ID' => 70828,	'PARENT_ID' => 70828,	'PARENT_NAME' => 'Новомосковск',	'SORT' => 770,	'LAT' => '54.010914',	'LON' => '38.281867'),
			Array('ID' => 70828,	'PARENT_ID' => 70639,	'PARENT_NAME' => 'Новомосковский район',	'SORT' => 770,	'LAT' => '54.010914',	'LON' => '38.281867'),
			Array('ID' => 67687,	'PARENT_ID' => 67686,	'PARENT_NAME' => 'Тульская область',	'SORT' => 660,	'LAT' => '54.196100',	'LON' => '37.618200'),
			Array('ID' => 70828,	'PARENT_ID' => 67686,	'PARENT_NAME' => 'Тульская область',	'SORT' => 770,	'LAT' => '54.010914',	'LON' => '38.281867'),
			Array('ID' => 82596,	'PARENT_ID' => 82596,	'PARENT_NAME' => 'Казань',	'SORT' => 860,	'LAT' => '55.760419',	'LON' => '49.190294'),
			Array('ID' => 82596,	'PARENT_ID' => 80986,	'PARENT_NAME' => 'Республика Татарстан',	'SORT' => 860,	'LAT' => '55.760419',	'LON' => '49.190294'),
			Array('ID' => 97957,	'PARENT_ID' => 97957,	'PARENT_NAME' => 'Самара',	'SORT' => 710,	'LAT' => '53.198627',	'LON' => '50.113987'),
			Array('ID' => 97957,	'PARENT_ID' => 80991,	'PARENT_NAME' => 'Самарская область',	'SORT' => 710,	'LAT' => '53.198627',	'LON' => '50.113987'),
			Array('ID' => 100915,	'PARENT_ID' => 100915,	'PARENT_NAME' => 'Пермь',	'SORT' => 730,	'LAT' => '58.014965',	'LON' => '56.246723'),
			Array('ID' => 100915,	'PARENT_ID' => 80992,	'PARENT_NAME' => 'Пермский край',	'SORT' => 730,	'LAT' => '58.014965',	'LON' => '56.246723'),
			Array('ID' => 126305,	'PARENT_ID' => 126305,	'PARENT_NAME' => 'Санкт-Петербург',	'SORT' => 700,	'LAT' => '59.938732',	'LON' => '30.316229'),
			Array('ID' => 166367,	'PARENT_ID' => 166367,	'PARENT_NAME' => 'Красноярск',	'SORT' => 800,	'LAT' => '56.009097',	'LON' => '92.872515'),
			Array('ID' => 166367,	'PARENT_ID' => 163504,	'PARENT_NAME' => 'Красноярский край',	'SORT' => 800,	'LAT' => '56.009097',	'LON' => '92.872515'),
			Array('ID' => 172796,	'PARENT_ID' => 172796,	'PARENT_NAME' => 'Новосибирск',	'SORT' => 765,	'LAT' => '55.028217',	'LON' => '82.923451'),
			Array('ID' => 172796,	'PARENT_ID' => 163507,	'PARENT_NAME' => 'Новосибирская область',	'SORT' => 765,	'LAT' => '55.028217',	'LON' => '82.923451'),
			Array('ID' => 175483,	'PARENT_ID' => 175483,	'PARENT_NAME' => 'Омск',	'SORT' => 750,	'LAT' => '54.991375',	'LON' => '73.371529'),
			Array('ID' => 175483,	'PARENT_ID' => 163508,	'PARENT_NAME' => 'Омская область',	'SORT' => 750,	'LAT' => '54.991375',	'LON' => '73.371529'),
			Array('ID' => 186914,	'PARENT_ID' => 186914,	'PARENT_NAME' => 'Екатеринбург',	'SORT' => 920,	'LAT' => '56.839104',	'LON' => '60.608250'),
			Array('ID' => 186914,	'PARENT_ID' => 184973,	'PARENT_NAME' => 'Свердловская область',	'SORT' => 920,	'LAT' => '56.839104',	'LON' => '60.608250'),
			Array('ID' => 192871,	'PARENT_ID' => 192871,	'PARENT_NAME' => 'Челябинск',	'SORT' => 645,	'LAT' => '55.159841',	'LON' => '61.402555'),
			Array('ID' => 192871,	'PARENT_ID' => 184977,	'PARENT_NAME' => 'Челябинская область',	'SORT' => 645,	'LAT' => '55.159841',	'LON' => '61.402555'),
			Array('ID' => 194792,	'PARENT_ID' => 194792,	'PARENT_NAME' => 'Краснодар',	'SORT' => 820,	'LAT' => '45.035433',	'LON' => '38.975712'),
			Array('ID' => 194792,	'PARENT_ID' => 194786,	'PARENT_NAME' => 'Краснодарский край',	'SORT' => 820,	'LAT' => '45.035433',	'LON' => '38.975712'),
			Array('ID' => 198252,	'PARENT_ID' => 198252,	'PARENT_NAME' => 'Ростов-на-Дону',	'SORT' => 740,	'LAT' => '47.224914',	'LON' => '39.702276'),
			Array('ID' => 198252,	'PARENT_ID' => 194787,	'PARENT_NAME' => 'Ростовская область',	'SORT' => 740,	'LAT' => '47.224914',	'LON' => '39.702276')
		);

		// while ($arCity = $oCities->Fetch()) {
		foreach ($ttt as $arCity) {
			if ($arCity['ID'] == $arCity['PARENT_ID']) {
				if (!isset($arCities[$arCity['ID']])) {
					$arCities[$arCity['ID']] = array(
						'ID' => $arCity['ID'],
						'NAME' => $arCity['PARENT_NAME'],
						'SORT' => $arCity['SORT'],
						'PATH' => array(),
						'LAT' => $arCity['LAT'],
						'LON' => $arCity['LON']
					);
				}
			} else {
				if (!in_array($arCity['PARENT_NAME'], $arCities[$arCity['ID']]['PATH'])) {
					$arCities[$arCity['ID']]['PATH'][] = $arCity['PARENT_NAME'];
				}
			}
		}

		usort($arCities, function ($a, $b) {
			if ($a['SORT'] > $b['SORT']) {
				return -1;
			} elseif ($a['SORT'] < $b['SORT']) {
				return 1;
			} elseif ($a['NAME'] > $b['NAME']) {
				return 1;
			} elseif ($a['NAME'] < $b['NAME']) {
				return -1;
			} else {
				return 0;
			}
		});

		foreach ($arCities as $arCity) {
			$arResult[] = array(
				'id' => $arCity['ID'],
				'title' => $arCity['NAME'],
				'lat' => $arCity['LAT'],
				'lon' => $arCity['LON'],
				'has_metro' => ($arCity['NAME'] == 'Москва'),
				'path' => $arCity['PATH']
			);
		}

		return $arResult;
	}

	public static function getList($arParams = array())
	{
		\Bitrix\Main\Loader::includeModule('sale');

		$arResult = array();
		$arCities = array();
		$arFilter = array();

		if (isset($arParams['filter'])) {
			$arFilter = $arParams['filter'];
		}

		$arCities = array();
		$arCitiesR = array();
		$arParents_0 = array();
		$arFilter_ = array();
		$i = 0;

		foreach ($arFilter['=ID'] as $l_id) 
		{
			$arFilter_['=ID'][$l_id] = $l_id;
		}
		$arFilter = $arFilter_;

		while (count($arFilter['=ID'])) 
		{
			$oCities = \Bitrix\Sale\Location\LocationTable::getList(array(
				'filter' => $arFilter,
				'select' => array(
					'ID',
					'PARENT_ID',
					'SORT',
					'TYPE_ID',
					'LAT' => 'LATITUDE',
					'LON' => 'LONGITUDE'
				)
			));

			while ($arCity = $oCities->Fetch()) {
				$arCities[$arCity['ID']] = $arCity;

				if($arCity['PARENT_ID'])
				{
					$arFilter['=ID'][$arCity['PARENT_ID']] = $arCity['PARENT_ID'];
				}
				
				unset($arFilter['=ID'][$arCity['ID']]);
			}

			//на всякий пожарный)
			$i++;
			if($i>10) break;
		}

		$oCitiesR = \Bitrix\Sale\Location\LocationTable::getList(array(
			// 'order' => array('PARENTS.LEFT_MARGIN' => 'DESC'),
			'filter' => array(
				'=ID' => array_keys($arCities),
				'=NAME.LANGUAGE_ID' => 'ru'
				),
			'select' => array(
				'ID',
				'NAME_RU' => 'NAME.NAME',
			)
		));

		while ($arCityR = $oCitiesR->Fetch()) {
			$arCities[$arCityR['ID']]['NAME'] = $arCityR['NAME_RU'];
		}

		foreach ($arParams['filter']['=ID'] as $city_id) 
		{
			$arCitiesR[$city_id] = array(
					'ID' => $city_id,
					'NAME' => $arCities[$city_id]['NAME'],
					'SORT' => $arCities[$city_id]['SORT'],
					'TYPE_ID' => $arCities[$city_id]['TYPE_ID'],
					'PATH' => array(),
					'LAT' => $arCities[$city_id]['LAT'],
					'LON' => $arCities[$city_id]['LON']
				);
			$parentID = $arCities[$city_id]['PARENT_ID'];
			while($parentID)
			{
				$arCitiesR[$city_id]['PATH'][] = $arCities[$parentID]['NAME'];
				$parentID = $arCities[$parentID]['PARENT_ID'];
			}

		}

		usort($arCitiesR, function ($a, $b) {
			if ($a['TYPE_ID'] == '3' && $b['TYPE_ID'] != '3') {
				return -1;
			} elseif ($a['TYPE_ID'] != '3' && $b['TYPE_ID'] == '3') {
				return 1;
			} elseif ($a['TYPE_ID'] == '3' && $b['TYPE_ID'] == '3') {
				if ($a['SORT'] > $b['SORT']) {
					return 1;
				} elseif ($a['SORT'] < $b['SORT']) {
					return -1;
				} elseif ($a['NAME'] > $b['NAME']) {
					return 1;
				} elseif ($a['NAME'] < $b['NAME']) {
					return -1;
				} else {
					return 0;
				}
			} elseif ($a['SORT'] > $b['SORT']) {
				return 1;
			} elseif ($a['SORT'] < $b['SORT']) {
				return -1;
			} elseif ($a['NAME'] > $b['NAME']) {
				return 1;
			} elseif ($a['NAME'] < $b['NAME']) {
				return -1;
			} else {
				return 0;
			}
		});

		foreach ($arCitiesR as $arCity) {
			//тут вырезаем из поисковой выдачи области и улицы
			if($arCity['TYPE_ID'] > 2 and $arCity['TYPE_ID'] < 7){
				$arResult[] = array(
					'id' => $arCity['ID'],
					'title' => $arCity['NAME'],
					'lat' => $arCity['LAT'],
					'lon' => $arCity['LON'],
					'has_metro' => ($arCity['NAME'] == 'Москва'),
					'path' => $arCity['PATH']
				);
			}

		}

		return $arResult;
	}

	/*
	* метод для нахождения id города ИБ по id местоположения
	*/
	public static function convGeo2toGeo1($id)
	{
		\Bitrix\Main\Loader::includeModule('sale');
		\Bitrix\Main\Loader::includeModule('iblock');

		$result = null;

		if ($id = intval($id)) {
			// $arLocation = \Bitrix\Sale\Location\LocationTable::getList(array(
				// 'filter' => array('=ID' => $id, 'NAME.LANGUAGE_ID' => 'ru'),
				// 'select' => array('CITY_NAME' => 'NAME.NAME'),
			// ))->fetch();

			$arLocation = \Bitrix\Sale\Location\LocationTable::getList(array(
				'order' => array('PARENTS.LEFT_MARGIN' => 'ASC'),
				'filter' => array(
					'=ID' => $id,
					'=NAME.LANGUAGE_ID' => 'ru',
					'=PARENTS.NAME.LANGUAGE_ID' => 'ru',
					'=PARENTS.TYPE.CODE' => array('CITY', 'REGION'),
				),
				'select' => array('PARENTS_NAME' => 'PARENTS.NAME.NAME', 'CITY_NAME' => 'NAME.NAME'),
				'limit' => 1
			))->fetch();

			if ($arLocation) {
				$arElement = \Bitrix\Iblock\ElementTable::getList(array(
					'filter' => array(
						'=IBLOCK_ID' => \CIBlockTools::GetIBlockId('area-city'),
						'=NAME' => $arLocation['CITY_NAME'],
						'=IBLOCK_SECTION.NAME' => (($arLocation['PARENTS_NAME']=='Москва')?'Новая Москва':$arLocation['PARENTS_NAME']),
					),
					'select' => array('ID'),
				))->fetch();

				$result = $arElement['ID'];
			}
		}

		return $result;
	}

	/*
	* метод для нахождения id местоположения по id города ИБ
	*/
	public static function convGeo1toGeo2($id)
	{
		\Bitrix\Main\Loader::includeModule('sale');

		$result = null;

		if ($id && $id = intval($id)) {
			$arCityRegion = GeoCatalog::GetCityRegionById($id);
			$arResult = Ajax::GetLocationByName($arCityRegion['IBLOCK_SECTION_NAME'], $arCityRegion['NAME']);

			if ($arResult['result']) {
				$arCity = reset($arResult['data']);
				$result = $arCity['ID'];
			}
		}

		return $result;
	}

	public static function getById($id)
	{
		$arResult = null;

		if ($id && $id = intval($id)) {
			$oLocations = \Bitrix\Sale\Location\LocationTable::getList(array(
				'order' => array('PARENTS.LEFT_MARGIN' => 'DESC'),
				'filter' => array(
					'=PARENTS.NAME.LANGUAGE_ID' => 'ru',
					'=ID' => $id,
				),
				'select' => array(
					'ID',
					'PARENTS_ID' => 'PARENTS.ID',
					'PARENTS_NAME' => 'PARENTS.NAME.NAME',
					'LAT' => 'LATITUDE',
					'LON' => 'LONGITUDE'
				)
			));

			while ($arLocation = $oLocations->Fetch()) {
				if ($arLocation['ID'] == $arLocation['PARENTS_ID']) {
					$arResult = array(
						'id' => $arLocation['ID'],
						'title' => $arLocation['PARENTS_NAME'],
						'lat' => $arLocation['LAT'],
						'lon' => $arLocation['LON'],
						'has_metro' => ($arLocation['PARENTS_NAME'] == 'Москва'),
						'path' => array()
					);
				} else {
					$arResult['path'][] = $arLocation['PARENTS_NAME'];
				}
			}
		}

		return $arResult;
	}
}
