<?php

namespace FourPaws\SocServ;

use Bitrix\Main\Web\HttpClient;

class CSocServFB2 extends \CSocServFacebook
{
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
}