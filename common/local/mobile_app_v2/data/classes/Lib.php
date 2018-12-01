<?php

require_once dirname(__FILE__).'/Config.php';

const HTML_IMAGES_URL_PATTERN = <<<PATTERN
/(?:src\s*=|background\s*=|background\s*:\s*url)\s*(?:(?:"|'|\(\s*"|\(\s*'|\()\s*((?!"|'|"\s*\)|'\s*\)|\)).+?)(?:"|'|"\s*\)|'\s*\)|\))|([^^\s>]+))/i
PATTERN;

const HTML_A_HREF_PATTERN = <<<PATTERN
/(href="([^"]+)")|(href=\'([^\']+)\')/i
PATTERN;

const DOMAIN_PATTERN = <<<PATTERN
/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}$/
PATTERN;

function d($var = null, $exit = true) {
	print_r($var);
	if($exit) {
		exit;
	}
}

/**
 * Ресайз картинки
 * @param  [type] $originalFile [description]
 * @param  [type] $sizeW        [description]
 * @return [type]               [description]
 */
function resizeProportionalImage($originalFile, $sizeW) {
	try {
		list($imagewidth, $imageheight, $imageType) = getimagesize($originalFile);
		$ext = getImageExtension($originalFile);
		if($ext == 'png') {
			$src = imagecreatefrompng($originalFile);
		} else if($ext == 'gif') {
			$src = imagecreatefromgif($originalFile);
		} else if($ext == 'jpg' || $ext == 'jpeg') {
			$src = imagecreatefromjpeg($originalFile);
		}
		$r_width = $sizeW;
		$r_height = $imageheight;
		$koe = $imagewidth / $sizeW;
		$r_height = ceil($imageheight / $koe);
		$dst = imageCreateTrueColor($r_width, $r_height);
		imageAlphaBlending($dst, false);
		imageSaveAlpha($dst, true);
		ImageCopyResampled($dst, $src, 0, 0, 0, 0, $sizeW, $r_height, $imagewidth, $imageheight);
		ob_start();
		if($ext == 'png') {
			imagepng($dst);
		} else if($ext == 'gif') {
			imagegif($dst);
		} else if($ext == 'jpg' || $ext == 'jpeg') {
			imagejpeg($dst);
		}

		$result = ob_get_contents();
		ob_end_clean();
	} catch(Exception $e) {
		$result = false;
	}

	return $result;
}

function reCropImage($originalFile, $sizeW, $sizeH, $type = 'imagick') {
	if($type == 'gd') {
		return reCropImageGd($originalFile, $sizeW, $sizeH);
	} else {
		return reCropImageImagick($originalFile, $sizeW, $sizeH);
	}
}

function reCropImageImagick($originalFile, $sizeW, $sizeH) {
	$tmpFile = TMPPATH."/".uniqid("", true).".".getImageExtension($originalFile);
	$cmd = "convert $originalFile -resize {$sizeW}x{$sizeH}^ -gravity center -extent {$sizeW}x{$sizeH} $tmpFile";
	exec($cmd);
	if(file_exists($tmpFile)) {
		$content = file_get_contents($tmpFile);
		unlink($tmpFile);
		return $content;
	} else {
		return "";
	}

}

function reCropImageGd($originalFile, $sizeW, $sizeH) {
	try {
		list($imageWidth, $imageHeight, $imageType) = getimagesize($originalFile);
		$ext = getImageExtension($originalFile);

		$src = false;
		if($ext == 'png') {
			$src = @imagecreatefrompng($originalFile);
		} else if($ext == 'gif') {
			$src = @imagecreatefromgif($originalFile);
		} else if($ext == 'jpg' || $ext == 'jpeg') {
			$src = @imagecreatefromjpeg($originalFile);
		}

		// При ошибке формирования картинки
		if(!$src) {
			// Создаем пустое изображение
			$src = imagecreatetruecolor($sizeW, $sizeH);
			$bgc = imagecolorallocate($src, 255, 255, 255);
			imagefill($src, 0, 0, $bgc);
		}

		$r_width = $sizeW;
		$r_height = $sizeH;
		$dst_y = $dst_x = $src_y = $src_x = 0;

		$dst = imageCreateTrueColor($sizeW, $sizeH);

		$white = imagecolorallocate($dst, 255, 255, 255);
		imagefill($dst, 0, 0, $white);

		if($imageWidth <= $sizeW) {
			if($imageHeight <= $sizeH) {
				//1 - ширина и высота меньше
				$imageWidth = $imageHeight;
				$r_height = $imageHeight;
				$r_width = $r_height;
				$src_x = ceil(($imageWidth - $r_width) / 2);
				$dst_x = ceil(($sizeW - $r_width) / 2);
				$dst_y = ceil(($sizeH - $r_height) / 2);
				$imageWidth = $imageHeight;
			} else {
				//3 - ширина меньше и высота больше
				$r_height = $sizeH;
				$r_width = $imageWidth;
				$dst_x = ceil(($sizeW - $r_width) / 2);
				$imageHeight = $sizeH;
			}
		} else {
			if($imageHeight <= $sizeH) {
				//2 - ширина больше и высота меньше
				$r_height = $imageHeight;
				$src_x = ceil(($imageWidth - $r_width) / 2);
				$dst_y = ceil(($sizeH - $r_height) / 2);
				$imageWidth = $sizeW;
			} else {
				//4 - ширина и высота больше
				if($imageHeight > $imageWidth) {
					$imageHeight = $imageWidth;
				} else {
					$src_x = ceil(($imageWidth - $imageHeight) / 2);
					$imageWidth = $imageHeight;
				}
			}
		}

		imageAlphaBlending($dst, false);
		imageSaveAlpha($dst, true);
		ImageCopyResampled($dst, $src, $dst_x, $dst_y, $src_x, $src_y, $r_width, $r_height, $imageWidth, $imageHeight);
		ob_start();
		if($ext == 'png') {
			imagepng($dst);
		} else if($ext == 'gif') {
			imagegif($dst);
		} else if($ext == 'jpg' || $ext == 'jpeg') {
			imagejpeg($dst);
		}

		$result = ob_get_contents();
		ob_end_clean();
	} catch(Exception $e) {
		$result = false;
	}

	return $result;
}

function translit($_string) {
	$rus = array(
		'а' => 'a',
		'б' => 'b',
		'в' => 'v',
		'г' => 'g',
		'д' => 'd',
		'е' => 'e',
		'ё' => 'e',
		'ж' => 'zh',
		'з' => 'z',
		'и' => 'i',
		'й' => 'i',
		'к' => 'k',
		'л' => 'l',
		'м' => 'm',
		'н' => 'n',
		'о' => 'o',
		'п' => 'p',
		'р' => 'r',
		'с' => 's',
		'т' => 't',
		'у' => 'u',
		'ф' => 'f',
		'х' => 'h',
		'ц' => 'c',
		'ч' => 'ch',
		'ш' => 'sh',
		'щ' => 'sh',
		'ъ' => '',
		'ы' => 'y',
		'ь' => '',
		'э' => 'e',
		'ю' => 'yu',
		'я' => 'ya',
		' ' => '_',
		',' => '_',
		'.' => '_',
		'/' => '-',
		'?' => '',
		'!' => '',
		'—' => '-',
		'-' => '-',
		':' => '',
		';' => '',
		'«' => '',
		'»' => '',
		'"' => '',
		"'" => '',
		'(' => '',
		')' => '',
		'–' => '-',
		'…' => '_',
		'&' => '',
		'№' => '',
		'’' => ''
	);

	$result = '';
	$string = $_string;

	for($i = 0; $i < mb_strlen($string, 'UTF-8'); $i++) {
		$char = mb_substr($string, $i, 1, 'UTF-8');

		if(isset($rus[$char])) {
			$result .= $rus[$char];
		} elseif(isset($rus[mb_strtolower($char, 'UTF-8')])) {
			$result .= $rus[mb_strtolower($char, 'UTF-8')];
		} else {
			$result .= $char;
		}
	}

	$result = preg_replace("/([_]{2,})/", "_", $result);

	return mb_strtolower($result, 'UTF-8');
}

function uuid() {
	// The field names refer to RFC 4122 section 4.1.2
	return sprintf('%04x%04x-%04x-%03x4-%04x-%04x%04x%04x',
		mt_rand(0, 65535), mt_rand(0, 65535), // 32 bits for "time_low"
		mt_rand(0, 65535), // 16 bits for "time_mid"
		mt_rand(0, 4095),  // 12 bits before the 0100 of (version) 4 for "time_hi_and_version"
		bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)),
		// 8 bits, the last two of which (positions 6 and 7) are 01, for "clk_seq_hi_res"
		// (hence, the 2nd hex digit after the 3rd hyphen can only be 1, 5, 9 or d)
		// 8 bits for "clk_seq_low"
		mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535) // 48 bits for "node"
	);
}

function getRealIp() {
	$ip = null;
	if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} elseif(!empty($_SERVER['REMOTE_ADDR'])) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}

function save2log($time) {
	global $conf;
	if($time > $conf['debug']['slow_work_time']) {
		$logsPath = TMPPATH.'/log';
		if(!is_dir($logsPath)) {
			mkdir($logsPath, 0755, true);
		}
		$file = $logsPath."/mysql.slow.log"; //куда пишем логи

		$ip = getRealIp();
		$date = date("Y-m-d H:i:s", time());
		$home = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : ''; //какая страница сайта

		$s = $date."	".$time."	".$ip."	".$home;
		$f = fopen($file, "a+");
		fwrite($f, "$s\r\n");
		fclose($f);
	}
}

// преобразует все элементы и ключи массива к строке
function arrayValues2String($array) {
	// Не использовать для больших массивов (> 10 000 элементов)
	$out = array();
	if(range(0, count($array) - 1) == array_keys($array)) {
		// не ассоциативный массив
		foreach($array as $value) {
			if(is_array($value)) {
				$out[] = arrayValues2String($value);
			} else {
				$out[] = (string)$value;
			}
		}
	} else {
		// ассоциативный массив
		foreach($array as $key => $value) {
			if(is_array($value)) {
				$out[(string)$key] = arrayValues2String($value);
			} else {
				$out[(string)$key] = (string)$value;
			}

		}
	}

	return $out;
}

function getClickHsh($email, $delivery_id, $link) {
	global $conf;
	// Последовательно декодирование, кодирование а потом снова декодирование помогают избежать
	// расхождения при генерации хеша и свести его к единственно верному варианту
	// Выглядит не очень приглядно, но только так работает корректно
	$link = urldecode(urlencode(urldecode($link)));
	return md5('click'.$email.$delivery_id.$link.$conf['security']['salt']);
}

function getOpenHsh($email, $delivery_id) {
	global $conf;
	return md5('open'.$email.$delivery_id.$conf['security']['salt']);
}

function getSubscriberUpdateHsh($email, $projectId, $time) {
	global $conf;
	return md5('subscriber_update'.$email.$projectId.$time.$conf['security']['salt']);
}

function getDomainByEmail($email) {
	$aResult = explode('@', $email, 2);
	return isset($aResult[1]) ? $aResult[1] : false;
}

function prepareArrayToCsv($array) {
	$last_index = max(array_keys($array));
	$out = array();
	for($i = 0; $i <= $last_index; $i++) {
		$out[$i] = isset($array[$i]) ? $array[$i] : '';
	}
	return $out;
}

// validate INN (10, 12)
function isValidInn($inn, $length = null) {
	if(preg_match('/\D/', $inn)) {
		return false;
	}

	$inn = (string)$inn;
	$len = strlen($inn);

	if($length && $length == $len) {
		if($len == 10) {
			return $inn[9] == (string)(((
						2 * $inn[0] + 4 * $inn[1] + 10 * $inn[2] +
						3 * $inn[3] + 5 * $inn[4] + 9 * $inn[5] +
						4 * $inn[6] + 6 * $inn[7] + 8 * $inn[8]
					) % 11) % 10);
		} elseif($len == 12) {
			$num10 = (string)(((
						7 * $inn[0] + 2 * $inn[1] + 4 * $inn[2] +
						10 * $inn[3] + 3 * $inn[4] + 5 * $inn[5] +
						9 * $inn[6] + 4 * $inn[7] + 6 * $inn[8] +
						8 * $inn[9]
					) % 11) % 10);

			$num11 = (string)(((
						3 * $inn[0] + 7 * $inn[1] + 2 * $inn[2] +
						4 * $inn[3] + 10 * $inn[4] + 3 * $inn[5] +
						5 * $inn[6] + 9 * $inn[7] + 4 * $inn[8] +
						6 * $inn[9] + 8 * $inn[10]
					) % 11) % 10);

			return $inn[11] == $num11 && $inn[10] == $num10;
		}
	}

	return false;
}

// validate 20-значного корр. или расчетного счета
function isValidBankAccount($schet) {
	if(!preg_match('/\D/', $schet)) {
		$schet = (string)$schet;
		$len = strlen($schet);
		if($len == 20) {
			return true;
		}
	}

	return false;
}

// validate KPP
function isValidKpp($kpp) {
	$kpp = (string)$kpp;
	if(strlen($kpp) == 9) {
		if(preg_match('/\d{4}[\dA-Z][\dA-Z]\d{3}/', $kpp)) {
			return true;
		}
	}

	return false;
}

// validate BIK
function isValidBik($bik) {
	if(!preg_match('/\D/', $bik)) {
		$bik = (string)$bik;
		if(strlen($bik) == 9) {
			return true;
		}
	}

	return false;
}

function isImageExtension($sExtension) {
	$aImagesExts = array('jpg', 'jpeg', 'png', 'gif');
	return in_array($sExtension, $aImagesExts);
}

// Получение расширения картинки по её содержимому а не по полному имени
// false если переданный файл - не является поддерживаемым форматом картинки
function getImageExtension($sPath = null, $sContent = null) {
	$finfo = new finfo(FILEINFO_MIME);
	if($sPath) {
		$sContent = file_get_contents($sPath);
	}
	$contentType = $finfo->buffer($sContent);
	return isImageMimeType($contentType);
}

function isImageMimeType($contentType) {
	$ext = false;
	if(strpos($contentType, 'image/gif') !== false) {
		$ext = 'gif';
	} elseif(strpos($contentType, 'image/png') !== false) {
		$ext = 'png';
	} elseif(strpos($contentType, 'image/jpeg') !== false || strpos($contentType, 'image/pjpeg') !== false) {
		$ext = 'jpeg';
	}
	return $ext;
}

function moneyFormat($money) {
	return number_format($money, 2, '.', ' ');
}

// перевод из байтов в мегабайты и вывод с точностью до первой значащей цифры после запятой
function byteToMegabyte($iByte) {
	if($iByte > 0) {
		$fMegabyte = $iByte / 1048576;
		$tmp = fmod($fMegabyte, 1);
		if($tmp == 0) {
			$decimals = 0;
		} else {
			if($fMegabyte > 1) {
				$decimals = 1;
				if($tmp < 0.1) {
					$decimals++;
					$tmp = $tmp * 10;
					if($tmp < 0.1) {
						$decimals = 0;
					}
				}
			} else {
				$decimals = 1; // от 1 до 7
				while($tmp < 0.1) {
					$decimals++;
					$tmp = $tmp * 10;
				}
			}
		}

		return number_format($fMegabyte, $decimals, '.', '');
	}
	return '0';
}

function datetimeFormat($datetime) {
	$aDatetimeFormat = datetimeFormatArray($datetime);
	return $aDatetimeFormat['date'].' '.$aDatetimeFormat['time'];
}

function dateFormat($datetime) {
	$aDatetimeFormat = datetimeFormatArray($datetime);
	return $aDatetimeFormat['date'];
}

function datetimeFormatArray($datetime, $isFullDate = false) {
	$diverTime = strtotime($datetime);
	$curTime = time();
	$diverMount = date('n', $diverTime);

	$aMonths = array(
		1 => 'января',
		2 => 'февраля',
		3 => 'марта',
		4 => 'апреля',
		5 => 'мая',
		6 => 'июня',
		7 => 'июля',
		8 => 'августа',
		9 => 'сентября',
		10 => 'октября',
		11 => 'ноября',
		12 => 'декабря',
	);
	$month = isset($aMonths[$diverMount]) ? $aMonths[$diverMount] : $diverMount;

	if($isFullDate) {
		$format = 'j '.$month.' Y';
	} else {
		$curYear = date('Y', $curTime);
		$diverYear = date('Y', $diverTime);
		$curDate = date('Y-m-d', $curTime);
		$yesterdayDate = date('Y-m-d', $curTime - 86400);
		$tomorrowDate = date('Y-m-d', $curTime + 86400);
		$diverDate = date('Y-m-d', $diverTime);

		if($curDate == $diverDate) {
			$format = 'сегодня';
		} elseif($yesterdayDate == $diverDate) {
			$format = 'вчера';
		} elseif($tomorrowDate == $diverDate) {
			$format = 'завтра';
		} elseif($curYear == $diverYear) {
			$format = 'j '.$month;
		} else {
			$format = 'j '.$month.' Y';
		}
	}

	return array(
		'date' => date($format, $diverTime),
		'time' => date('H:i', $diverTime),
	);
}

// Множественные формы
function t($n, $form1, $form2, $form5) {
	if($n > 0 && $n < 1) return $form2;
	$n = abs($n) % 100;
	$n1 = $n % 10;
	if($n > 10 && $n < 20) return $form5;
	if($n1 > 1 && $n1 < 5) return $form2;
	if($n1 == 1) return $form1;
	return $form5;
}

function strcode($str) {
	global $conf;
	$len = strlen($str);
	$gamma = '';
	$n = $len > 100 ? 8 : 2;
	while(strlen($gamma) < $len) {
		$gamma .= substr(pack('H*', sha1($gamma.$conf['security']['xor_code_salt'])), 0, $n);
	}
	return $str ^ $gamma;
}

function xorEncode($str) {
	return base64_encode(strcode($str));
}

function xorDecode($str) {
	return strcode(base64_decode($str));
}

function getUserUid($emailId) {
	global $conf;
	return $emailId.sha1($emailId.$conf['security']['salt']);
}

// для логирования в файл из любого места в проекте
function saveInDefaultLog($data) {
	file_put_contents(TMPPATH.'/log/default.log', date('Y-m-d H:i:s', time()).' '.(is_array($data) ? json_encode($data) : $data)."\r\n", FILE_APPEND);
}

// Ограничение на limit
function normaliseLimit($limit) {
	$limit = intval($limit);
	if($limit < 0) {
		$limit = 0;
	} elseif($limit > 100) {
		$limit = 100;
	}
	return $limit;
}

// Ограничение на offset
function normaliseOffset($offset) {
	$offset = intval($offset);
	if($offset < 0) {
		$offset = 0;
	}
	return $offset;
}

// Сумма прописью
function moneyToString($num) {
	$nul = 'ноль';
	$ten = array(
		array('', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'),
		array('', 'одна', 'две', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'),
	);
	$a20 = array('десять', 'одиннадцать', 'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать', 'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать');
	$tens = array(2 => 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто');
	$hundred = array('', 'сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот');
	$unit = array( // Units
		array('копейка', 'копейки', 'копеек', 1),
		array('рубль', 'рубля', 'рублей', 0),
		array('тысяча', 'тысячи', 'тысяч', 1),
		array('миллион', 'миллиона', 'миллионов', 0),
		array('миллиард', 'милиарда', 'миллиардов', 0),
	);

	list($rub, $kop) = explode('.', sprintf("%015.2f", floatval($num)));
	$out = array();
	if(intval($rub) > 0) {
		foreach(str_split($rub, 3) as $uk => $v) { // by 3 symbols
			if(!intval($v)) continue;
			$uk = sizeof($unit) - $uk - 1; // unit key
			$gender = $unit[$uk][3];
			list($i1, $i2, $i3) = array_map('intval', str_split($v, 1));
			// mega-logic
			$out[] = $hundred[$i1]; # 1xx-9xx
			if($i2 > 1) $out[] = $tens[$i2].' '.$ten[$gender][$i3]; # 20-99
			else $out[] = $i2 > 0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
			// units without rub & kop
			if($uk > 1) $out[] = t($v, $unit[$uk][0], $unit[$uk][1], $unit[$uk][2]);
		}
	} else $out[] = $nul;
	$out[] = t(intval($rub), $unit[1][0], $unit[1][1], $unit[1][2]); // rub
	$out[] = $kop.' '.t($kop, $unit[0][0], $unit[0][1], $unit[0][2]); // kop
	return trim(preg_replace('/ {2,}/', ' ', join(' ', $out)));
}

function getDomainByUrl($url) {
	$url = trim($url);
	if(preg_match(DOMAIN_PATTERN, $url)) {
		return $url;
	} else {
		$tryParseUrl = parse_url(trim($url), PHP_URL_HOST);
		return $tryParseUrl ? $tryParseUrl : $url;
	}
}

// Получаем домен второго уровня из домена любого уровня >=2
function getDomainSecondLevel($domain) {
	$domainSecondLevel = '';
	$aHost = explode('.', $domain);
	if($aHost) {
		// Получаем второй уровень доменного имени
		for($i = count($aHost)-1, $j = 0; $j < 2; $i--, $j++) {
			if(!empty($aHost[$i])) {
				$domainSecondLevel = $aHost[$i].($domainSecondLevel ? '.' : '').$domainSecondLevel;
			}
		}
	}

	return $domainSecondLevel;
}

// Определяем размер файла по url
function getRemoteFileSize($url) {
	ob_start();
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_NOBODY, 1);
	curl_exec($ch);
	curl_close($ch);
	$head = ob_get_contents();
	ob_end_clean();
	$regex = '/Content-Length:\s([0-9].+?)\s/';
	preg_match($regex, $head, $matches);
	return isset($matches[1]) ? $matches[1] : 0;
}

// todo: удалить функцию (и все классы монго) только после применения всех миграций на боевом
function normaliseMongoDate($mongoDate, $format = 'Y-m-d H:i:s') {
	return $mongoDate instanceof MongoDate ? date($format, $mongoDate->sec) : $mongoDate;
}
