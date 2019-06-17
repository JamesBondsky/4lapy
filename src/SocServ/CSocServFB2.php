<?php

namespace FourPaws\SocServ;

use Bitrix\Main\Web\HttpClient;
use CFacebookInterface;

class CSocServFB2 extends \CSocServFacebook
{
    const ID = 'FB2';
    public function prepareUser($arFBUser, $short = false)
    {
        $arFields = array(
//			'EXTERNAL_AUTH_ID' => self::ID,
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
            $redirect_uri = self::CONTROLLER_URL."/redirect.php?redirect_to=".urlencode(\CSocServUtil::GetCurUrl('auth_service_id='.self::ID, array("code")));
        }
        else
        {
            $redirect_uri = \CSocServUtil::GetCurUrl('auth_service_id='.self::ID, array("code"));
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
            $redirect_uri = self::CONTROLLER_URL."/redirect.php?redirect_to=".urlencode(\CSocServUtil::GetCurUrl('auth_service_id='.self::ID, array("code")));
        }
        else
        {
            $redirect_uri = \CSocServUtil::GetCurUrl('auth_service_id='.self::ID, array("code"));
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
            $redirect_uri = self::CONTROLLER_URL."/redirect.php?redirect_to=".urlencode(\CSocServUtil::GetCurUrl('auth_service_id='.self::ID, array("code")));
        }
        else
        {
            $redirect_uri = \CSocServUtil::GetCurUrl('auth_service_id='.self::ID, array("code"));
        }

        if($fb->GetAccessToken($redirect_uri) !== false)
        {
            $res = $fb->getMessages($uid);
        }

        return $res;
    }
}