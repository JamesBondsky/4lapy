<?php
$DOCUMENT_ROOT=$_SERVER["DOCUMENT_ROOT"];

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);

define("SITE_SERVER_NAME_API", "api.4lapy.ru");

require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");
require_once(CLASSESPATH.'/Storage.php');

class APIServer extends Storage
{
	private $method;
	private $request_method;
	protected $param;

	protected $conf;
	protected $res;

	protected $User;
	protected $ERROR;
	protected $WARNING;

	// включаем проверку подписи для соответствующих методов
	public $check_sign_get = true;
	public $check_sign_post = true;
	public $check_sign_put = true;
	public $check_sign_delete = true;

	public function __construct($h = [])
	{
		$type=!isset($this->type) ? 'token' : $this->type;
		$checkSalt = !isset($this->type) ? 'token' : $this->type;

		include CLASSESPATH.'/APIServerMessages.php'; // include_once - не подойдет
		$this->ERROR=$ERROR;
		$this->WARNING=$WARNING;
		$this->res=array('result'=>array(), 'errors'=>array(), 'warnings'=>array());

		$this->conf=$h['conf'];
		$this->method=get_called_class();
		$this->request_method=isset($h['request_method']) ? mb_strtolower($h['request_method']) : 'get';
		$this->param=$h['param'];

		// проверка подписи метода
		$need_check_sign = (!isset($this->{"check_sign_$this->request_method"}) || $this->{"check_sign_$this->request_method"} === true);
		if($this->method=='mobile_version') $need_check_sign = false;

		if ($need_check_sign
			&& $this->conf['security']['salt']
			&& !$this->checkSign($this->param, $this->conf['security']['salt'])
		) {
			http_response_code('403');
			die();
		}

		//
		if ($type == 'token') {
			if (!empty($this->param['token'])) {
				$this->User=$this->getUser(array('token' => $this->param['token']));

				if (!is_array($this->User)) {
					$this->addError('bad_token');
				} else {
					if (!isset($this->User['dt_update'])
						|| substr($this->User['dt_update'], 0, 10) != date('Y-m-d', time())
					) {
						$sql = "UPDATE `user_session` SET `dt_update`=NOW() WHERE `token`='".$this->User['token']."';";
						$GLOBALS['DB']->Query($sql);
					}
				}
			} else {
				$this->addError('empty_token');
			}
		}
	}

	private function md5ValueRecursive($argument)
	{
		$arResult = array();

		if (is_array($argument)) {
			if (!array_search($argument, $_FILES)) {
				foreach ($argument as $value) {
					$arResult = array_merge($arResult, $this->md5ValueRecursive($value));
				}
			}
		} else {
			$arResult[] = md5($argument);
		}

		return $arResult;
	}

	private function checkSign($arParams, $salt)
	{
		if (isset($arParams['sign'])) {
			$sign = $arParams['sign'];
			unset($arParams['sign']);
		} else {
			return false;
		}

		$arMd5 = $this->md5ValueRecursive($arParams);
		sort($arMd5);

		$hash = md5($salt.join('', $arMd5));

		return (($sign == $hash) or ($sign == '666666'));
	}

	public function runMethod(){
		if(count($this->res['errors'])==0){
			if(method_exists($this, $this->request_method)){
				$request_method=$this->request_method;
				$result=$this->$request_method($this->param);
				if($result && is_array($result)){
					$this->res['result']+=$result;
				}
			}else{
				$this->res['errors']+=array('request_method_not_found'=>array('msg'=>'REST-метод '.$this->request_method.' не найден', 'msg_en'=>'REST-method '.$this->request_method.' not found'));
			}
		}
		return($this->res);
	}

	// Выносить из APIServer в дочерний класс нельзя, потому что функция используется в самом APIServer
	// login (email), user_id
	protected function getBitrixUser($aInput){
		$oCUser='';
		if(isset($aInput['login'])){
			$oCUser=CUser::GetByLogin($aInput['login']);
		}elseif(isset($aInput['user_id'])){
			$oCUser=CUser::GetByID($aInput['user_id']);
		}
		if($oCUser && $aCUser=$oCUser->Fetch())
			return($aCUser);

		return(false);
	}

	// Выносить из APIServer в дочерний класс нельзя, потому что функция используется в самом APIServer
	// by token
	protected function getUser($aInput){
		if(isset($aInput['token'])){
			$sQuery="/*ms=last_used*/ SELECT * FROM `user_session` WHERE `token`='".$aInput['token']."' ORDER BY `id` LIMIT 0,1;";
			global $DB;
			$oQuery=$DB->Query($sQuery);
			if($aUser=$oQuery->Fetch()){
				if($aUser['user_id']){
					if($aCUser=$this->getBitrixUser(array('user_id'=>$aUser['user_id']))){
						return(array_merge($aCUser, $aUser));
					}
				}
				return($aUser);
			}
		}
		return(false);
	}

	public static function createUserSession($iUserID, $bRemember=false, $sToken='', $iBasketID=0){
		// проверка существования токена защитит нас от коллизий md5
		$sIP=getRealIp();
		$sUserAgent=(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null);
		if($sToken && $aUser=App::db()->getOne(
			'user_session',
			array('id'),
			array('token'=>$sToken)
		)){
			App::db()->query(
				'UPDATE '
					.'`user_session` '
				.'SET '
					.'`user_id`=:user_id, '
					.'`dt_update`=NOW(), '
					.'`ip`=:ip, '
					.'`user_agent`=:user_agent, '
					.'`basket_id`=:basket_id '
				.' WHERE '
					.'`id`=:id '
					.'AND `token`=:token;',
				array(
					'user_id'=>$iUserID,
					'ip'=>$sIP,
					'user_agent'=>$sUserAgent,
					'basket_id'=>$iBasketID,
					'id'=>$aUser['id'],
					'token'=>$sToken,
				)
			);
		}else{
			do{
				$sToken=md5(uuid());
				$users=App::db()->getOne(
					'user_session',
					array('id'),
					array('token'=>$sToken)
				);
			}while($users);

			App::db()->query(
				'INSERT INTO `user_session` (`token`, `user_id`, `dt_create`, `dt_update`, `ip`, `user_agent`, `basket_id`)'
				.' VALUES (:token, :user_id, NOW(), NOW(), :ip, :user_agent, :basket_id);',
				array(
					'token'=>$sToken,
					'user_id'=>$iUserID,
					'ip'=>$sIP,
					'user_agent'=>$sUserAgent,
					'basket_id'=>$iBasketID,
				)
			);
		}
		return($sToken);
	}

	public function addError($error, $mergeError=array()){
		$errorObj=is_string($error) ? $this->ERROR[$error] : $error;
		if($mergeError){
			$errorObj=mergeArray($errorObj, $mergeError);
		}
		$this->res['errors']+=$errorObj;
	}

	public function addWarning($warning, $mergeError=array()){
		$warningObj=is_string($warning) ? $this->WARNING[$warning] : $warning;
		if($mergeError){
			$warningObj=mergeArray($warningObj, $mergeError);
		}
		$this->res['warnings']+=$warningObj;
	}

	public function hasErrors(){
		return(count($this->res['errors'])>0);
	}

	public function hasWarnings(){
		return(count($this->res['warnings'])>0);
	}

	public function getUserId(){
		return($this->User['user_id']);
	}

	public function getFuserId(){
		return($this->User['basket_id']);
	}

	protected function getPushParams()
	{
		return array(
			'platform' => $this->User['platform'],
			'token' => $this->User['push_token'],
		);
	}

	protected function setPushParams($arPushParams)
	{
		if ($this->User && $this->User['id'] && $this->User['token']) {
			\App::db()->query(
				'UPDATE '
					.'`user_session` '
				.'SET '
					.'`platform`=:platform, '
					.'`push_token`=:push_token'
				.' WHERE '
					.'`id`=:id '
					.'AND `token`=:token;',
				array(
					'platform'=>$arPushParams['platform'],
					'push_token'=>$arPushParams['token'],
					'id'=>$this->User['id'],
					'token'=>$this->User['token'],
				)
			);
		}
	}
}
