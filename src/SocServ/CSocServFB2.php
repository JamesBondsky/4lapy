<?php

namespace FourPaws\SocServ;

use Bitrix\Main\Web\HttpClient;
use CFacebookInterface;

class CSocServFB2 extends \CSocServFacebook
{
    use SocServiceHelper;

    const ID = 'FB2';
    public function prepareUser($arFBUser, $short = false)
    {
        $arFields = array(
			'EXTERNAL_AUTH_ID' => parent::ID,
            'XML_ID' => $arFBUser["id"],
            'LOGIN' => static::LOGIN_PREFIX.$arFBUser["id"],
            'EMAIL' => ($arFBUser["email"] != '') ? $arFBUser["email"] : '',
            'NAME'=> $arFBUser["first_name"],
            'LAST_NAME'=> $arFBUser["last_name"],
            'OATOKEN' => $this->entityOAuth->getToken(),
            'OATOKEN_EXPIRES' => $this->entityOAuth->getAccessTokenExpires(),
        );

        if(!$short && isset($arFBUser['picture']['data']['url']) && !$arFBUser['picture']['data']['is_silhouette'])
        {
            $picture_url = \CFacebookInterface::GRAPH_URL.'/'.$arFBUser['id'].'/picture?type=large';
            $temp_path = \CFile::GetTempName('', 'picture.jpg');

            $ob = new HttpClient(array(
                "redirect" => true
            ));
            $ob->download($picture_url, $temp_path);

            $arPic = \CFile::MakeFileArray($temp_path);
            if($arPic)
            {
                $arFields["PERSONAL_PHOTO"] = $arPic;
            }
        }

        if(isset($arFBUser['birthday']))
        {
            if($date = MakeTimeStamp($arFBUser['birthday'], "MM/DD/YYYY"))
            {
                $arFields["PERSONAL_BIRTHDAY"] = ConvertTimeStamp($date);
            }
        }

        if(isset($arFBUser['gender']) && $arFBUser['gender'] != '')
        {
            if($arFBUser['gender'] == 'male')
            {
                $arFields["PERSONAL_GENDER"] = 'M';
            }
            elseif($arFBUser['gender'] == 'female')
            {
                $arFields["PERSONAL_GENDER"] = 'F';
            }
        }

        $arFields["PERSONAL_WWW"] = $this->getProfileUrl($arFBUser['id']);

        if(strlen(SITE_ID) > 0)
        {
            $arFields["SITE_ID"] = SITE_ID;
        }

        return $arFields;
    }

    public function getFriendsList($limit, &$next)
    {
        if(IsModuleInstalled('bitrix24') && defined('BX24_HOST_NAME'))
        {
            $redirect_uri = self::CONTROLLER_URL."/redirect.php?redirect_to=".urlencode(\CSocServUtil::GetCurUrl('auth_service_id='.parent::ID, array("code")));
        }
        else
        {
            $redirect_uri = \CSocServUtil::GetCurUrl('auth_service_id='.parent::ID, array("code"));
        }

        $fb = $this->getEntityOAuth();
        if($fb->GetAccessToken($redirect_uri) !== false)
        {
            $res = $fb->GetCurrentUserFriends($limit, $next);
            if(is_array($res))
            {
                foreach($res['data'] as $key => $value)
                {
                    $res['data'][$key]['uid'] = $value['id'];
                    $res['data'][$key]['url'] = $this->getProfileUrl($value['id']);

                    if(is_array($value['picture']))
                    {
                        if(!$value['picture']['data']['is_silhouette'])
                        {
                            $res['data'][$key]['picture'] = CFacebookInterface::GRAPH_URL.'/'.$value['id'].'/picture?type=large';
                        }
                        else
                        {
                            $res['data'][$key]['picture'] = '';
                        }
                        //$res['data'][$key]['picture'] = $value['picture']['data']['url'];
                    }
                }

                return $res['data'];
            }
        }

        return false;
    }

    public function sendMessage($uid, $message)
    {
        $fb = new CFacebookInterface();

        if(IsModuleInstalled('bitrix24') && defined('BX24_HOST_NAME'))
        {
            $redirect_uri = self::CONTROLLER_URL."/redirect.php?redirect_to=".urlencode(\CSocServUtil::GetCurUrl('auth_service_id='.parent::ID, array("code")));
        }
        else
        {
            $redirect_uri = \CSocServUtil::GetCurUrl('auth_service_id='.parent::ID, array("code"));
        }

        if($fb->GetAccessToken($redirect_uri) !== false)
        {
            $res = $fb->sendMessage($uid, $message);
        }


        return $res;
    }

    public function getMessages($uid)
    {
        $fb = new CFacebookInterface();

        if(IsModuleInstalled('bitrix24') && defined('BX24_HOST_NAME'))
        {
            $redirect_uri = self::CONTROLLER_URL."/redirect.php?redirect_to=".urlencode(\CSocServUtil::GetCurUrl('auth_service_id='.parent::ID, array("code")));
        }
        else
        {
            $redirect_uri = \CSocServUtil::GetCurUrl('auth_service_id='.parent::ID, array("code"));
        }

        if($fb->GetAccessToken($redirect_uri) !== false)
        {
            $res = $fb->getMessages($uid);
        }

        return $res;
    }

    public function Authorize()
    {
        global $APPLICATION;
        $APPLICATION->RestartBuffer();

        $authError = SOCSERV_AUTHORISATION_ERROR;
        $paramsProfile = [];

        if(
            isset($_REQUEST["code"]) && $_REQUEST["code"] <> ''
            && \CSocServAuthManager::CheckUniqueKey()
        )
        {
            if(IsModuleInstalled('bitrix24') && defined('BX24_HOST_NAME'))
            {
                $redirect_uri = static::CONTROLLER_URL."/redirect.php";
            }
            else
            {
                $redirect_uri = $this->getEntityOAuth()->GetRedirectURI();
            }

            $this->entityOAuth = $this->getEntityOAuth($_REQUEST['code']);
            if($this->entityOAuth->GetAccessToken($redirect_uri) !== false)
            {
                $arFBUser = $this->entityOAuth->GetCurrentUser();
                if(is_array($arFBUser) && isset($arFBUser["id"]))
                {
//                    $arFields = self::prepareUser($arFBUser);
                    $arFields = $this->prepareUser($arFBUser);
                    $checkUser = $this->checkUser($arFields);
                    if ($checkUser) {
                        $paramsProfile = [];
                        $authError = $this->AuthorizeUser($arFields);
                    } else {
                        $paramsProfile = [
                            'name' => $arFields['NAME'],
                            'last_name' => $arFields['LAST_NAME'],
                            'gender' => $arFields['PERSONAL_GENDER'],
                            'birthday' => $arFields['PERSONAL_BIRTHDAY'],
                            'ex_id' => static::LOGIN_PREFIX.$arFBUser["id"]
                        ];
                    }
//                    $authError = $this->AuthorizeUser($arFields);
                }
            }
        }

        $bSuccess = $authError === true;

        $url = ($APPLICATION->GetCurDir() == "/login/") ? "" : $APPLICATION->GetCurDir();
        $aRemove = array("logout", "auth_service_error", "auth_service_id", "code", "error_reason", "error", "error_description", "check_key", "current_fieldset");

        if(isset($_REQUEST["state"]))
        {
            $arState = array();
            parse_str($_REQUEST["state"], $arState);

            if(isset($arState['backurl']) || isset($arState['redirect_url']))
            {
                $url = !empty($arState['redirect_url']) ? $arState['redirect_url'] : $arState['backurl'];
                if(substr($url, 0, 1) !== "#")
                {
                    $parseUrl = parse_url($url);

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
            }
        }

        if($authError === SOCSERV_REGISTRATION_DENY)
        {
            $url = (preg_match("/\?/", $url)) ? $url.'&' : $url.'?';
            $url .= 'auth_service_id='.parent::ID.'&auth_service_error='.$authError;
        }
        elseif($bSuccess !== true)
        {
            $url = (isset($urlPath)) ? $urlPath.'?auth_service_id='.parent::ID.'&auth_service_error='.$authError : $GLOBALS['APPLICATION']->GetCurPageParam(('auth_service_id='.parent::ID.'&auth_service_error='.$authError), $aRemove);
        }

        if(\CModule::IncludeModule("socialnetwork") && strpos($url, "current_fieldset=") === false)
        {
            $url .= ((strpos($url, "?") === false) ? '?' : '&')."current_fieldset=SOCSERV";
        }


        if (count($paramsProfile) > 0) {
            $url = '/personal/register/?backurl=/&' . http_build_query($paramsProfile);
        }
        ?>
        <script type="text/javascript">
            if(window.opener)
                window.opener.location = '<?=\CUtil::JSEscape($url)?>';
            window.close();
        </script>
        <?
        die();
    }
}
