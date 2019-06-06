<?php
namespace FourPaws\SocServ;


class CSocServVK2 extends \CSocServVKontakte {
    const ID = "VK2";

    public function prepareUser($arVkUser, $short = false)
    {
        $first_name = $last_name = $gender = "";

        if ($arVkUser['response']['0']['first_name'] <> '')
        {
            $first_name = preg_replace("/&[#a-z0-9]+;/", "", $arVkUser['response']['0']['first_name']);
        }

        if ($arVkUser['response']['0']['last_name'] <> '')
        {
            $last_name = preg_replace("/&[#a-z0-9]+;/", "", $arVkUser['response']['0']['last_name']);
        }

        if (isset($arVkUser['response']['0']['sex']) && $arVkUser['response']['0']['sex'] != '')
        {
            if ($arVkUser['response']['0']['sex'] == '2')
                $gender = 'M';
            elseif ($arVkUser['response']['0']['sex'] == '1')
                $gender = 'F';
        }

        $phone = null;

        if (isset($arVkUser['response']['0']['contacts'])) {
            if (isset($arVkUser['response']['0']['contacts']['mobile_phone'])) {
                try {
                    $phone = \FourPaws\Helpers\PhoneHelper::normalizePhone($arVkUser['response']['0']['mobile_phone']);
                } catch (\Exception $e) {}
            }
            if (isset($arVkUser['response']['0']['contacts']['home_phone'])) {
                try {
                    $phone = \FourPaws\Helpers\PhoneHelper::normalizePhone($arVkUser['response']['0']['home_phone']);
                } catch (\Exception $e) {}
            }
        }

        $arFields = array(
//			'EXTERNAL_AUTH_ID' => self::ID,
            'XML_ID' => $arVkUser['response']['0']['id'],
//			'LOGIN' => "VKuser" . $arVkUser['response']['0']['id'],
            'LOGIN' => $phone ?? "VKuser" . $arVkUser['response']['0']['id'],
            'EMAIL' => $this->entityOAuth->GetCurrentUserEmail(),
            'NAME' => $first_name,
            'LAST_NAME' => $last_name,
            'PERSONAL_GENDER' => $gender,
            'OATOKEN' => $this->entityOAuth->getToken(),
            'OATOKEN_EXPIRES' => $this->entityOAuth->getAccessTokenExpires(),
        );

        if ($phone) {
            $arFields['PERSONAL_PHONE'] = $phone;
        }

        if (isset($arVkUser['response']['0']['photo_max_orig']) && self::CheckPhotoURI($arVkUser['response']['0']['photo_max_orig']))
        {
            if (!$short)
            {
                $arPic = \CFile::MakeFileArray($arVkUser['response']['0']['photo_max_orig']);
                if ($arPic)
                {
                    $arFields["PERSONAL_PHOTO"] = $arPic;
                }
            }

            if (isset($arVkUser['response']['0']['bdate']))
            {
                if ($date = MakeTimeStamp($arVkUser['response']['0']['bdate'], "DD.MM.YYYY"))
                {
                    $arFields["PERSONAL_BIRTHDAY"] = ConvertTimeStamp($date);
                }
            }

            $arFields["PERSONAL_WWW"] = self::getProfileUrl($arVkUser['response']['0']['id']);

            if (strlen(SITE_ID) > 0)
            {
                $arFields["SITE_ID"] = SITE_ID;
            }
        }

        return $arFields;
    }
}
