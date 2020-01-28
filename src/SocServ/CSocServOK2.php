<?php

namespace FourPaws\SocServ;

class CSocServOK2 extends \CSocServOdnoklassniki
{
    use SocServiceHelper;

    const ID = 'OK2';
    public function Authorize()
    {
        global $APPLICATION;

        $APPLICATION->RestartBuffer();
        $bSuccess = SOCSERV_AUTHORISATION_ERROR;
        $bProcessState = false;
        $paramsProfile = [];

        if((isset($_REQUEST["code"]) && $_REQUEST["code"] <> '') && \CSocServAuthManager::CheckUniqueKey())
        {
            $bProcessState = true;

            if(IsModuleInstalled('bitrix24') && defined('BX24_HOST_NAME'))
                $redirect_uri = self::CONTROLLER_URL."/redirect.php";
            else
                $redirect_uri= \CHTTP::URN2URI("/bitrix/tools/oauth/odnoklassniki.php");

            $appID = trim(self::GetOption("odnoklassniki_appid"));
            $appSecret = trim(self::GetOption("odnoklassniki_appsecret"));
            $appKey = trim(self::GetOption("odnoklassniki_appkey"));

            $gAuth = new \COdnoklassnikiInterface($appID, $appSecret, $appKey, $_REQUEST["code"]);

            if($gAuth->GetAccessToken($redirect_uri) !== false)
            {
                $arOdnoklUser = $gAuth->GetCurrentUser();

                if(is_array($arOdnoklUser) && ($arOdnoklUser['uid'] <> ''))
                {
                    $uid = $arOdnoklUser['uid'];
                    $first_name = $last_name = $gender = "";
                    if($arOdnoklUser['first_name'] <> '')
                        $first_name = $arOdnoklUser['first_name'];
                    if($arOdnoklUser['last_name'] <> '')
                        $last_name = $arOdnoklUser['last_name'];
                    if(isset($arOdnoklUser['gender']) && $arOdnoklUser['gender'] != '')
                    {
                        if($arOdnoklUser['gender'] == 'male')
                            $gender = 'M';
                        elseif($arOdnoklUser['gender'] == 'female')
                            $gender = 'F';
                    }

                    $arFields = array(
						'EXTERNAL_AUTH_ID' => self::ID,
                        'XML_ID' => $uid,
                        'LOGIN' => "OKuser".$uid,
                        'NAME'=> $first_name,
                        'LAST_NAME'=> $last_name,
                        'PERSONAL_GENDER' => $gender,
                    );
                    if(isset($arOdnoklUser['birthday']))
                        if($date = MakeTimeStamp($arOdnoklUser['birthday'], "YYYY-MM-DD"))
                            $arFields["PERSONAL_BIRTHDAY"] = ConvertTimeStamp($date);
                    if(isset($arOdnoklUser['pic_2']) && self::CheckPhotoURI($arOdnoklUser['pic_2']))
                    {
                        if($arPic = \CFile::MakeFileArray($arOdnoklUser['pic_2']))
                        {
                            $arPic['name'] = md5($arOdnoklUser['pic_2']).'.jpg';
                            $arFields["PERSONAL_PHOTO"] = $arPic;
                        }
                    }
                    $arFields["PERSONAL_WWW"] = "http://odnoklassniki.ru/profile/".$uid;
                    if(strlen(SITE_ID) > 0)
                        $arFields["SITE_ID"] = SITE_ID;

//                    $bSuccess = $this->AuthorizeUser($arFields);
                    $checkUser = $this->checkUser($arFields);

                    $exAuthId = $xmlId = '';

                    if (strripos($arFields['LOGIN'], 'VK') !== false) {
                        $exAuthId = CSocServVK2::ID;
                        [,$xmlId] = explode('VKuser', $arFields['LOGIN']);
                    } else if (strripos($arFields['LOGIN'], 'OK') !== false) {
                        $exAuthId = CSocServOK2::ID;
                        [,$xmlId] = explode('OKuser', $arFields['LOGIN']);
                    } else if (strripos($arFields['LOGIN'], 'FB') !== false) {
                        $exAuthId = CSocServFB2::ID;
                        [,$xmlId] = explode('FB_', $arFields['LOGIN']);
                    }

                    if ($checkUser) {
                        $paramsProfile = [];
                        $bSuccess = $this->AuthorizeUser($arFields);

                        if ($bSuccess) {
                            $user = new \CUser();
                            $user->Update($checkUser['USER_ID'], [
                                'EXTERNAL_AUTH_ID' => $exAuthId,
                                'XML_ID' => $xmlId,
                            ]);
                            $user->Authorize($checkUser['USER_ID']);
                            unset($_SESSION['socServiceParams']);
                        }
                    } else {

                        global $USER;
                        if ($USER->IsAuthorized()) {
                            $fieldsUserTable = [
                                'LOGIN' => $USER->GetID(),
                                'EXTERNAL_AUTH_ID' => $exAuthId,
                                'USER_ID' => $USER->GetID(),
                                'XML_ID' => $xmlId,
                                'NAME' => $arFields['NAME'],
                                'LAST_NAME' => $arFields['LAST_NAME'],
                                'EMAIL' => '',
                                'OATOKEN' => $this->getEntityOAuth()->getToken(),
                            ];

                            $result = \Bitrix\Socialservices\UserTable::add($fieldsUserTable);
                        } else {
                            $paramsProfile = [
                                'name' => $arFields['NAME'],
                                'last_name' => $arFields['LAST_NAME'],
                                'gender' => $arFields['PERSONAL_GENDER'],
                                'birthday' => $arFields['PERSONAL_BIRTHDAY'],
                                'ex_id' => 'VKuser' . $arOdnoklUser['uid'],
                                'token' => $this->getEntityOAuth()->getToken()
                            ];

                            $_SESSION['socServiceParams'] = $paramsProfile;
                        }
                    }
                }
            }
        }

        if(!$bProcessState)
        {
            unset($_REQUEST["state"]);
        }

        $url = ($APPLICATION->GetCurDir() == "/login/") ? "" : $APPLICATION->GetCurDir();
        $aRemove = array("logout", "auth_service_error", "auth_service_id", "code", "error_reason", "error", "error_description", "check_key", "current_fieldset");

        $mode = 'opener';
        if(isset($_REQUEST["state"]))
        {
            $arState = array();
            parse_str($_REQUEST["state"], $arState);
            if(isset($arState['backurl']) || isset($arState['redirect_url']))
            {
                $parseUrl = parse_url(!empty($arState['redirect_url']) ? $arState['redirect_url'] : $arState['backurl']);
                $urlPath = $parseUrl["path"];
                $arUrlQuery = explode('&', $parseUrl["query"]);

                foreach($arUrlQuery as $key => $value)
                {
                    foreach($aRemove as $param)
                    {
                        if(strpos($value, $param."=") === 0)
                        {
                            unset($arUrlQuery[$key]);
                            break;
                        }
                    }
                }

                $url = (!empty($arUrlQuery)) ? $urlPath.'?'.implode("&", $arUrlQuery) : $urlPath;
            }

            if(isset($arState['mode']))
            {
                $mode = $arState['mode'];
            }
        }

        if($bSuccess === SOCSERV_REGISTRATION_DENY)
        {
            $url = (preg_match("/\?/", $url)) ? $url.'&' : $url.'?';
            $url .= 'auth_service_id='.self::ID.'&auth_service_error='.SOCSERV_REGISTRATION_DENY;
        }
        elseif($bSuccess !== true)
        {
            $backUrl = $url;
            $url = (isset($parseUrl))
                ? $urlPath.'?auth_service_id='.self::ID.'&auth_service_error='.$bSuccess
                : $APPLICATION->GetCurPageParam(('auth_service_id='.self::ID.'&auth_service_error='.$bSuccess), $aRemove);
        }

        if(\CModule::IncludeModule("socialnetwork") && strpos($url, "current_fieldset=") === false)
            $url = (preg_match("/\?/", $url)) ? $url."&current_fieldset=SOCSERV" : $url."?current_fieldset=SOCSERV";

        $url = \CUtil::JSEscape($url);

        if (count($paramsProfile) > 0) {
            $url = '/personal/register/?backurl=' . ($backUrl ?? '/');
        }

        $location = ($mode == "opener") ? 'if(window.opener) window.opener.location = \''.$url.'\'; window.close();' : ' window.location = \''.$url.'\';';

        $JSScript = '
		<script type="text/javascript">
		'.$location.'
		</script>
		';

        echo $JSScript;

        die();
    }
}
