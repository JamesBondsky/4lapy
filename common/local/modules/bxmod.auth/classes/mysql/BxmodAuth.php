<?php
#################################
#   Developer: Lynnik Danil     #
#   Site: http://bxmod.ru       #
#   E-mail: support@bxmod.ru    #
#################################

IncludeModuleLangFile(__FILE__);

class BxmodAuth
{
    /**
    * ���������� ������ �������� ���.�������� ��� �����������
    */
    public function GetSocList()
    {
        global $APPLICATION;
        
        $result = Array();
        
        $options = self::GetOptions();
        
        // ���������� �������� ������ ������ � ������.
        // ��� ���������� ��� ��������� ��������� ������������ ����� ����������� ����� ���������� �������
        if ( is_object( $APPLICATION ) ) {
            $_SESSION["BXMOD_AUTH_LAST_PAGE"] = $APPLICATION->GetCurUri();
        }
        
        if ( $options["USE_SOCIAL"] == "Y" )
        {
            // ����������� ����������� � ���������� ���� ������
            $supportedTypes = Array(
                "soc_fb" => "BxmodAuthSocServFB",
                "soc_gg" => "BxmodAuthSocServGG",
                "soc_mr" => "BxmodAuthSocServMR",
                "soc_ok" => "BxmodAuthSocServOK",
                "soc_vk" => "BxmodAuthSocServVK",
                "soc_ya" => "BxmodAuthSocServYA",
                "soc_tw" => "BxmodAuthSocServTW",
            );
            
            // ���������, ������ �� ��������
            $res = Array();
            foreach ( $supportedTypes AS $k => $v )
            {
                if ( $options[ $k ] == "Y" )
                {
                    $res[ $options[$k . "_order"] . $v ] = $v::Get();
                }
            }
            ksort( $res );
            
            foreach ( $res AS $v )
            {
                $result[ $v["CLASS"] ] = $v;
            }
        }
        return $result;
    }
    
    /**
    * ���������� ������� ��������� ������ ������� ��������
    */
    public function GetSettings()
    {
        $setting = Array(
            Array (
                // ������������ ���� �� E-mail
                Array (
                    "ID" => "USE_EMAIL",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_USE_EMAIL"),
                    "FIELD" => 'checkbox',
                    "DEFAULT" => "Y",
                ),
                // ������������ ���� �� ������ ��������
                Array (
                    "ID" => "USE_PHONE",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_USE_PHONE"),
                    "FIELD" => 'checkbox',
                    "DEFAULT" => "Y"
                ),
                // ������������ ���� ����� ���. �������
                Array (
                    "ID" => "USE_SOCIAL",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_USE_SOCIAL"),
                    "FIELD" => 'checkbox',
                    "DEFAULT" => "Y",
                    "MESSAGE" => GetMessage("BXMOD_AUTH_MESSAGE_ALL_HELP"),
                )
            ),
            Array (
                // �������� CAPTCHA ��� �����������
                Array (
                    "ID" => "EMAIL_CAPTCHA",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_EMAIL_CAPTCHA"),
                    "FIELD" => 'checkbox',
                    "DEFAULT" => "N",
                    "HEADING" => GetMessage("BXMOD_AUTH_PARAM_AUTH_HEADING")
                ),
                // ����� ������� ������� �������� ����������� �������� CAPTCHA
                Array (
                    "ID" => "EMAIL_CAPTCHA_COUNT",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_EMAIL_CAPTCHA_COUNT"),
                    "FIELD" => 'text',
                    "DEFAULT" => "0"
                ),
                // �������� CAPTCHA ��� �������������� ������
                Array (
                    "ID" => "EMAIL_RESTORE_CAPTCHA",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_EMAIL_RESTORE_CAPTCHA"),
                    "FIELD" => 'checkbox',
                    "DEFAULT" => "Y"
                ),
                // �������� ������� &laquo;��������� ����&raquo;
                Array (
                    "ID" => "EMAIL_REMIND",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_EMAIL_REMIND"),
                    "FIELD" => 'checkbox',
                    "DEFAULT" => "Y"
                ),
                // ����������� ������ ������ ������������
                Array (
                    "ID" => "PASSWORD_LENGTH_MIN",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_PASSWORD_LENGTH_MIN"),
                    "FIELD" => 'text',
                    "DEFAULT" => "6"
                ),
                // ������������ ������ ������ ������������
                Array (
                    "ID" => "PASSWORD_LENGTH_MAX",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_PASSWORD_LENGTH_MAX"),
                    "FIELD" => 'text',
                    "DEFAULT" => "20"
                ),
                // �������� ��������� �������� USER_CHECKWORD ��� �������������� �������
                Array (
                    "ID" => "GET_RESTORE_CHECKWORD",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_GET_RESTORE_CHECKWORD"),
                    "FIELD" => 'text',
                    "DEFAULT" => "USER_CHECKWORD"
                ),
                // �������� ��������� �������� USER_ID ��� �������������� �������
                Array (
                    "ID" => "GET_RESTORE_USER_ID",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_GET_RESTORE_USER_ID"),
                    "FIELD" => 'text',
                    "DEFAULT" => "USER_ID"
                ),
                // �������� ��������� �������� USER_ID ��� ������������� �����������
                Array (
                    "ID" => "GET_CONFIRM_USER_ID",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_GET_CONFIRM_USER_ID"),
                    "FIELD" => 'text',
                    "DEFAULT" => "confirm_user_id"
                ),
                // �������� ��������� �������� CONFIRM_CODE ��� ������������� �����������
                Array (
                    "ID" => "GET_CONFIRM_CODE",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_GET_CONFIRM_CODE"),
                    "FIELD" => 'text',
                    "DEFAULT" => "confirm_code"
                ),
                // ������������ ���������� SMS �� ���� ����� �������� � ���
                Array (
                    "ID" => "PHONE_MAX_SMS",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_PHONE_MAX_SMS"),
                    "FIELD" => 'text',
                    "DEFAULT" => "0",
                    "HEADING" => GetMessage("BXMOD_AUTH_PARAM_PHONE_HEADING")
                ),
                // ����� SMS ��������� ��� ������������� �����������
                Array (
                    "ID" => "PHONE_SMS_CONFIRM_MSG",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_PHONE_SMS_CONFIRM_MSG"),
                    "FIELD" => 'textarea',
                    "DEFAULT" => GetMessage("BXMOD_AUTH_PARAM_PHONE_SMS_CONFIRM_DEFAULT"),
                ),
                // ����� SMS ��������� ��� �������������� ������
                Array (
                    "ID" => "PHONE_SMS_RESTORE_MSG",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_PHONE_SMS_RESTORE_MSG"),
                    "FIELD" => 'textarea',
                    "DEFAULT" => GetMessage("BXMOD_AUTH_PARAM_PHONE_SMS_RESTORE_DEFAULT"),
                ),
                // ��� ID � ������� SMS.ru
                Array (
                    "ID" => "PHONE_SMSRU_ID",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_PHONE_SMSRU_ID"),
                    "FIELD" => 'text',
                    "DEFAULT" => "",
                ),
                // ��� ID � ������� SMS.ru
                Array (
                    "ID" => "PHONE_SMSRU_FROM",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_PHONE_SMSRU_FROM"),
                    "FIELD" => 'text',
                    "DEFAULT" => "",
                )
            ),
            Array (
            // ���������
                // ������������ ���� ����� ���������
                Array (
                    "ID" => "soc_vk",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_SOC_VK"),
                    "FIELD" => 'checkbox',
                    "DEFAULT" => "N",
                    "HEADING" => GetMessage("BXMOD_AUTH_PARAM_SOC_VK_HEADING")
                ),
                // ID ����������
                Array (
                    "ID" => "soc_vkID",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_SOC_VK_ID"),
                    "FIELD" => 'text',
                    "DEFAULT" => "",
                ),
                // ��������� ����
                Array (
                    "ID" => "soc_vkKey",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_SOC_VK_KEY"),
                    "FIELD" => 'text',
                    "DEFAULT" => "",
                ),
                // ����������
                Array (
                    "ID" => "soc_vk_order",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_SOC_ORDER"),
                    "FIELD" => 'text',
                    "DEFAULT" => "10",
                    "MESSAGE" => GetMessage("BXMOD_AUTH_PARAM_SOC_VK_MESS")
                ),
            // �������������
                // ������������ ���� ����� �������������
                Array (
                    "ID" => "soc_ok",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_SOC_OK"),
                    "FIELD" => 'checkbox',
                    "DEFAULT" => "N",
                    "HEADING" => GetMessage("BXMOD_AUTH_PARAM_SOC_OK_HEADING")
                ),
                // ID ����������
                Array (
                    "ID" => "soc_okID",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_SOC_OK_ID"),
                    "FIELD" => 'text',
                    "DEFAULT" => "",
                ),
                // ���� ����������
                Array (
                    "ID" => "soc_odKey",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_SOC_OK_KEY"),
                    "FIELD" => 'text',
                    "DEFAULT" => ""
                ),
                // ��������� ��� ����������
                Array (
                    "ID" => "soc_odSecretKey",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_SOC_OK_SECRET"),
                    "FIELD" => 'text',
                    "DEFAULT" => "",
                ),
                // ����������
                Array (
                    "ID" => "soc_ok_order",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_SOC_ORDER"),
                    "FIELD" => 'text',
                    "DEFAULT" => "20",
                    "MESSAGE" => GetMessage("BXMOD_AUTH_PARAM_SOC_OK_MESS")
                ),
            // Google
                // ������������ ���� ����� Google
                Array (
                    "ID" => "soc_gg",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_SOC_GG"),
                    "FIELD" => 'checkbox',
                    "DEFAULT" => "N",
                    "HEADING" => GetMessage("BXMOD_AUTH_PARAM_SOC_GG_HEADING")
                ),
                // ������������� (Client ID)
                Array (
                    "ID" => "soc_ggID",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_SOC_GG_ID"),
                    "FIELD" => 'text',
                    "DEFAULT" => "",
                ),
                // ��������� ��� (Client secret)
                Array (
                    "ID" => "soc_ggKey",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_SOC_GG_KEY"),
                    "FIELD" => 'text',
                    "DEFAULT" => ""
                ),
                // ����������
                Array (
                    "ID" => "soc_gg_order",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_SOC_ORDER"),
                    "FIELD" => 'text',
                    "DEFAULT" => "30",
                    "MESSAGE" => GetMessage("BXMOD_AUTH_PARAM_SOC_GG_MESS")
                ),
            // Facebook
                // ������������ ���� ����� Facebook
                Array (
                    "ID" => "soc_fb",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_SOC_FB"),
                    "FIELD" => 'checkbox',
                    "DEFAULT" => "N",
                    "HEADING" => GetMessage("BXMOD_AUTH_PARAM_SOC_FB_HEADING")
                ),
                // ������������� ���������� (App ID)
                Array (
                    "ID" => "soc_fbID",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_SOC_FB_ID"),
                    "FIELD" => 'text',
                    "DEFAULT" => "",
                ),
                // ��������� ��� ���������� (App Secret)
                Array (
                    "ID" => "soc_fbKey",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_SOC_FB_KEY"),
                    "FIELD" => 'text',
                    "DEFAULT" => "",
                ),
                // ����������
                Array (
                    "ID" => "soc_fb_order",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_SOC_ORDER"),
                    "FIELD" => 'text',
                    "DEFAULT" => "40",
                    "MESSAGE" => GetMessage("BXMOD_AUTH_PARAM_SOC_FB_MESS")
                ),
            // ������
                // ������������ ���� ����� ������
                Array (
                    "ID" => "soc_ya",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_SOC_YA"),
                    "FIELD" => 'checkbox',
                    "DEFAULT" => "N",
                    "HEADING" => GetMessage("BXMOD_AUTH_PARAM_SOC_YA_HEADING")
                ),
                // ID ����������
                Array (
                    "ID" => "soc_yaID",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_SOC_YA_ID"),
                    "FIELD" => 'text',
                    "DEFAULT" => "",
                ),
                // ��������� ����
                Array (
                    "ID" => "soc_yaKey",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_SOC_YA_KEY"),
                    "FIELD" => 'text',
                    "DEFAULT" => ""
                ),
                // ����������
                Array (
                    "ID" => "soc_ya_order",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_SOC_ORDER"),
                    "FIELD" => 'text',
                    "DEFAULT" => "50",
                    "MESSAGE" => GetMessage("BXMOD_AUTH_PARAM_SOC_YA_MESS")
                ),
            // Mail.ru
                // ������������ ���� ����� Mail.ru
                Array (
                    "ID" => "soc_mr",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_SOC_MR"),
                    "FIELD" => 'checkbox',
                    "DEFAULT" => "N",
                    "HEADING" => GetMessage("BXMOD_AUTH_PARAM_SOC_MR_HEADING")
                ),
                // ID �����
                Array (
                    "ID" => "soc_mrID",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_SOC_MR_ID"),
                    "FIELD" => 'text',
                    "DEFAULT" => ""
                ),
                // ��������� ����
                Array (
                    "ID" => "soc_mrKey",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_SOC_MR_KEY"),
                    "FIELD" => 'text',
                    "DEFAULT" => ""
                ),
                // ��������� ����
                Array (
                    "ID" => "soc_mrSecretKey",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_SOC_MR_SECRET"),
                    "FIELD" => 'text',
                    "DEFAULT" => ""
                ),
                // ����������
                Array (
                    "ID" => "soc_mr_order",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_SOC_ORDER"),
                    "FIELD" => 'text',
                    "DEFAULT" => "60",
                    "MESSAGE" => GetMessage("BXMOD_AUTH_PARAM_SOC_MR_MESS")
                ),
            // Twitter
                // ������������ ���� ����� Twitter
                Array (
                    "ID" => "soc_tw",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_SOC_TW"),
                    "FIELD" => 'checkbox',
                    "DEFAULT" => "N",
                    "HEADING" => GetMessage("BXMOD_AUTH_PARAM_SOC_TW_HEADING")
                ),
                // ID �����
                Array (
                    "ID" => "soc_twID",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_SOC_TW_ID"),
                    "FIELD" => 'text',
                    "DEFAULT" => ""
                ),
                // ��������� ����
                Array (
                    "ID" => "soc_twKey",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_SOC_TW_KEY"),
                    "FIELD" => 'text',
                    "DEFAULT" => "",
                    "MESSAGE" => GetMessage("BXMOD_AUTH_PARAM_SOC_TW_MESS")
                ),
            // �������� ��������� ��� �� �������� �����������
            Array (
                    "ID" => "fail_url",
                    "NAME" => GetMessage("BXMOD_AUTH_PARAM_SOC_FAIL"),
                    "FIELD" => 'text',
                    "DEFAULT" => "/",
                    "HEADING" => GetMessage("BXMOD_AUTH_PARAM_SOC_ADDIT_HEADING")
                ),
            ),
        );
        
        // ���������� �������� ����� � ����������� � ������������ �����. ���� ����������� ���, �� ���������� �������� ��-���������
        $options = COption::GetOptionString( "bxmod.auth", "options" );
        if ( $options ) $options = unserialize( $options );

        foreach ( $setting AS $K=>$V )
        {
            foreach ( $V AS $k=>$v )
            {
                if ( isset( $options[ $v["ID"] ] ) )
                {
                    $setting[$K][$k]["VALUE"] = $options[ $v["ID"] ];
                }
                else
                {
                    $setting[$K][$k]["VALUE"] = $v["DEFAULT"];
                }
            }
        }
        
        return $setting;
    }
    
    /**
    * ���������� ������� ��������� ������
    */
    public function GetOptions()
    {
        $allOptions = Array();
        $options = self::GetSettings();
        foreach ( $options AS $option )
        {
            foreach ( $option AS $v )
            {
                $allOptions[ $v["ID"] ] = $v["VALUE"];
            }
        }
        return $allOptions;
    }
    
    /**
    * ��������� ����� �������� �� ���������� ����� ���� � �������� �� ���� ��� ����� ����
    * 
    * @param string $phone ����� ��������
    */
    public function CheckPhone( $phone )
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if ( (strlen( $phone ) == 10 && substr( $phone, 0, 1 ) == 9 ) || ( strlen( $phone ) == 11 && ( substr( $phone, 0, 1 ) == 8 || substr( $phone, 0, 1 ) == 7 ) && substr( $phone, 1, 1 ) == 9 ) )
        {
            if ( strlen( $phone ) == 10 ) $phone = "7" . $phone;
            $phone = "7" . substr( $phone, 1, 3 ) . substr( $phone, 4, 3 ) . substr( $phone, 7 );
            
            return $phone;
        }
        
        return false;
    }
    
    /**
    * �������� ���� ������
    * 
    * @param string $str ����� ������������
    * @return mixed ������ email || phone || false
    */
    public function CheckLoginType ( $str )
    {
        if ( preg_match("~^([a-z0-9_\-\.])+@([a-z0-9_\-\.])+\.([a-z0-9])+$~i", $str) )
        {
            return "email";
        }
        elseif ( self::CheckPhone( $str ) )
        {
            return "phone";
        }
        
        return false;
    }
    
    /**
    * �������� SMS �� �����
    * 
    * @param string $phone ����� ��������
    * @param ineger $text ����� ���������
    */
    public function SendSMS ( $phone, $text )
    {
        $sendSMS = true;
        
        // ��������� ��������� �������, ���� ���������� false, �� ��� �� ����������
        $rsHandlers = GetModuleEvents("bxmod.auth", "OnBeforeSendSMS");
        while ( $arHandler = $rsHandlers->Fetch() )
        {
            if ( !ExecuteModuleEvent($arHandler, $phone, $text) ) {
                $sendSMS = false;
            }
        }
        
        // ����� ���������� ��� ����� sms.ru
        if ( $sendSMS ) {
        	
            $options = self::GetOptions();
            
            if ( !empty($options["PHONE_SMSRU_ID"]) )
            {
                $text = urlencode( self::StrToUtf8( $text ) );
                
                $query = "http://sms.ru/sms/send?api_id={$options["PHONE_SMSRU_ID"]}&to={$phone}&text={$text}&partner_id=36537";
                
                if ( !empty($options["PHONE_SMSRU_ID"]) )
                {
                    $query .= "&from={$options["PHONE_SMSRU_FROM"]}";
                }
                
                $response = file_get_contents($query);
                return true;
            }
        }
        return false;
    }
    
    /**
    * �������� ������������ ���� �����/������ ��� ������� �����������
    * 
    * @param string $login ����� ������������
    * @param string $password ������ ������������
    * @param array $arUser ������ ������ ������������
    */
    public function CheckLoginPassword ( $login, $password, $arUser = false )
    {
        global $DB;
        
        if ( !$arUser )
        {
            $arUser = self::GetUserByEmail( $login );
        }
        
        if ( $arUser )
        {
            if ( strlen($arUser["PASSWORD"]) > 32 )
            {
                $salt = substr( $arUser["PASSWORD"], 0, strlen($arUser["PASSWORD"]) - 32 );
                $db_password = substr( $arUser["PASSWORD"], -32 );
            }
            else
            {
                $salt = "";
                $db_password = $arUser["PASSWORD"];
            }
            
            // ���� ������ ������
            if ( md5($salt . $password) == $db_password )
            {
                return true;
            }
        }
        
        $strSql = "UPDATE `b_user` SET `LOGIN_ATTEMPTS` = `LOGIN_ATTEMPTS` + 1 WHERE `LOGIN` = '". $DB->ForSql($login) ."'";
        $DB->Query($strSql);
        
        return false;
    }
    
    /**
    * �������� ������������ ���������� �� �������������� ������
    * 
    * @param integer $userID ID ������������
    * @param string $loginType ��� ������ (email ��� phone)
    */
    public function SendRestore ( $userID = false, $loginType )
    {
        global $DB;
        
        // ��������� ������
        $options = self::GetOptions();
        
        $rsUser = CUser::GetByID( intval( $userID ) );
        $arUser = $rsUser->Fetch();

        if ( $loginType && $arUser["LOGIN"] )
        {
            $user = new CUser;
            
            if ( $loginType == "email" )
            {
                // if($arUser["EMAIL"])
                // {
                //     if($arUser['ID'] > 0)
                //     {
                //         CUser::SendUserInfo($arUser['ID'], 's1', GetMessage("INFO_REQ"), true, 'REQUEST_USER_PASS');
                //         return true;
                //     }
                // }

                $result = self::funSendMailRestorePassword($arUser["EMAIL"]);
                if ( $result["result"] )
                {
                    return true;
                } 

                // $arRes = $user->SendPassword($arUser["LOGIN"], $arUser["EMAIL"]);
                // if ( $arRes["TYPE"] == "OK" )
                // {
                //     return true;
                // }
            }
            elseif ( $loginType == "phone" )
            {
                $res = $DB->Query( "SELECT * FROM `b_bxmod_auth_smscontrol` WHERE `phone`='" . $DB->ForSql($arUser["PERSONAL_PHONE"]) . "'" );
                if ( $res = $res->Fetch() )
                {
                    // ���� �������� ����� �� �������� ��� �� ���� �����
                    if ( self::GetPhoneTimeLimit( $arUser["PERSONAL_PHONE"] ) )
                    {
                        return false;
                    }
                    $DB->Query("UPDATE `b_bxmod_auth_smscontrol` SET `sendTime`=". time() ." WHERE `phone`='". $DB->ForSql( $arUser["PERSONAL_PHONE"] ) ."'");
                }
                else
                {
                    $DB->Query("INSERT INTO `b_bxmod_auth_smscontrol` (`phone`, `sendTime`) VALUES ('". $DB->ForSql( $arUser["PERSONAL_PHONE"] ) ."',". time() .")");
                }
                
                $salt = randString(8);
                $code = rand(10000, 99999);
                
                $checkWordTime = date("Y-m-d H:i:s", mktime(date("H"), date("i") + 10, date("s"), date("m"), date("d"), date("Y")));
                
                $DB->Query("UPDATE `b_user` SET `CHECKWORD`='". $salt . md5($salt . $code) ."', `CHECKWORD_TIME`='". $checkWordTime ."' WHERE `ID`=" . intval($arUser["ID"]));
                
                self::SendSMS( $arUser["PERSONAL_PHONE"], str_replace("#CODE#", $code, $options["PHONE_SMS_RESTORE_MSG"]) );
                
                return true;
            }
        }
        return false;
    }
    
    /**
    * �������� ����������� �������� SMS �� ����� ��������. ����������, ���� � ���������� ������ ����� ���������� ������������ SMS �� ���� ����� � ���
    * ���������� ���������� ������, � ������� ������� �������� �� ��������.
    * ���� false, ���� �������� �������� ����������
    * 
    * @param Int $phone ����� ��������
    */
    public function GetPhoneTimeLimit ( $phone )
    {
        global $DB;
        
        $options = self::GetOptions();
        $options["PHONE_MAX_SMS"] = intval( $options["PHONE_MAX_SMS"] );
        
        if ( $options["PHONE_MAX_SMS"] > 0 )
        {
            $res = $DB->Query( "SELECT * FROM `b_bxmod_auth_smscontrol` WHERE `phone`='" . $DB->ForSql($phone) . "'" );
            if ( $res = $res->Fetch() )
            {
                $timeLimit = 3600 / $options["PHONE_MAX_SMS"] - (time() - $res["sendTime"]);
                
                if ( $timeLimit > 0 )
                {
                    return $timeLimit;
                }
            }
        }
        
        return false;
    }
    
    /**
    * �������� ������������ ������
    * 
    * @param string $password ������
    * @param string $rePassword ������������� ������
    */
    public function CheckPassword ( $password, $rePassword )
    {
        // ��������� ������
        $options = self::GetOptions();
        
        // ���� ������ ������, ��� ������� � ����������
        if ( strlen( $password ) < intval($options["PASSWORD_LENGTH_MIN"]) )
        {
            return GetMessage('BXMOD_AUTH_ERROR_SHORT_PASSWORD') . intval($options["PASSWORD_LENGTH_MIN"]);
        }
        // ���� ������ �������, ��� ������� � ����������
        elseif ( strlen( $password ) > intval($options["PASSWORD_LENGTH_MAX"]) )
        {
            return GetMessage('BXMOD_AUTH_ERROR_LONG_PASSWORD') . intval($options["PASSWORD_LENGTH_MAX"]);
        }
        // ���� ������ � ��� ������������� �� ���������
        elseif ( $password != $rePassword )
        {
            return GetMessage('BXMOD_AUTH_ERROR_REPASSWORD');
        }
        
        return false;
    }
    
    /**
    * ����������� / ����������� ������������
    * 
    * @param string $login ����� ������������
    * @param string $password ������ ������������
    * @param string $captchaCode ��� ������
    * @param string $captchaSid Sid ������
    * @param boolean $remember ��������� �� ������������
    * @return string �������� ����������
    */
    public function Login ( $login, $password, $captchaCode = false, $captchaSid = false, $remember = false, $mob = false )
    {
        global $USER, $APPLICATION, $DB;
        
        if ( !is_object( $USER ) )
        {
            $USER = new CUser;
        }
        
        // ��������� ������
        $options = self::GetOptions();
        
        // �������� ���� ����������� (���� ��� ����� ��������)
        if ( !$loginType = self::CheckLoginType( $login ) )
        {
            // ���� ������ �� ���������� �����, �� ������� �����. ���������
            if ( $options["USE_EMAIL"] == "Y" && $options["USE_PHONE"] == "Y" )
            {
                return self::Response("Error", "login_error_ep", "login");
            }
            elseif ( $arParams["OPTIONS"]["USE_EMAIL"] == "Y" )
            {
                return self::Response("Error", "login_error_e", "login");
            }
            else
            {
                return self::Response("Error", "login_error_p", "login");
            }
        }
        // ���� ������ ����� ��������, �� ����������� �� ���� �� ��������
        elseif( $loginType == "phone" && $options["USE_PHONE"] != "Y" )
        {
            return self::Response("Error", "login_error_e", "login");
        }
        // ���� ������ ����� email, �� ����������� �� ���� �� ��������
        elseif( $loginType == "email" && $options["USE_EMAIL"] != "Y" )
        {
            return self::Response("Error", "login_error_p", "login");
        }
        
        // ���������� �����
        if ( $loginType == 'email' )
        {
            $userLogin = $userEmail = $login;
        }
        // ���� ����������� �� ������ ��������, �� ������ �� ������ �������� ������ e-mail
        else
        {
            $userLogin = self::CheckPhone( $login );
            $userEmail = $userLogin . "@register.phone";
        }
        
        // ������������ � �� ������
        if ( $arUser = self::GetUserByEmail( $userEmail ) or $arUser = self::GetUserByPhone($userLogin) )
        {
            // ��� ������������� ��������� ������������ ����� ������
            if ( $options["EMAIL_CAPTCHA"] == "Y" )
            {
                // ���� � ���������� ������� �������� ������ ������, ��� ������������ ��� ������ ������ ������� �����, ��� ������� � ����������, �� ��������� ������
                if ( ( $options["EMAIL_CAPTCHA_COUNT"] == 0 || $options["EMAIL_CAPTCHA_COUNT"] < $arUser["LOGIN_ATTEMPTS"] ) && !$APPLICATION->CaptchaCheckCode($captchaCode, $captchaSid) )
                {
                    return self::Response("Error", "login_error_captcha", "captcha");
                }
            }
            
            // ���� ������ ������ ������
            if ( self::CheckLoginPassword( $arUser["LOGIN"], $password, $arUser ) )
            {
                // ���� ������������ �����������������, �� ��� �� ���������� ���� ����� ��� ����� ��������
                if ( $arUser["ACTIVE"] == "N" && strlen( $arUser["CONFIRM_CODE"] ) > 0 )
                {
                    if ( $loginType == 'phone' )
                    {
                        return self::Response("RegisterPhoneConfirm", "register_confirm_phone");
                    }
                    else
                    {
                        return self::Response("RegisterEmailConfirm", "register_confirm_email");
                    }
                }
                // ���� ������������ ������������� �������
                elseif ( $arUser["ACTIVE"] == "N" )
                {
                    return self::Response("Error", "login_error_unactive", "login");
                }
                // ���� ��� ���������, ����������
                else
                {
                    // if ( $USER->Authorize($arUser["ID"], $remember) )
                    // {
                    //     return self::Response("Login", "login_success");
                    // }
                    if ( ($USER->Login($arUser["LOGIN"], $password, $remember)) == true )
                    {
                        return self::Response("Login", "login_success");
                    }
                }
                return self::Response("Error", "unknown_error", "unknown");
            }
            return self::Response("Error", "login_error_password", "password");
        }
        // ���� ������������ �� �������, �������� ��������
        elseif($mob)
        {
            if ( $passError = self::CheckPassword( $password, $password ) )
            {
                return self::Response("Error", "register_error_password", "password", $passError);
            }
            
            // �������� ����������������
            COption::SetOptionString("main","captcha_registration","N");
            $arRegisterResult = $USER->Register($userLogin, "", "", $password, $password, $userEmail);
            COption::SetOptionString("main","captcha_registration","Y");

            // ���� ����������� ������ �������
            if ( $arRegisterResult["TYPE"] == "OK" )
            {
                $arUser = self::GetUserByEmail( $userLogin );

                if ( $loginType == 'phone' )
                {
                    $fields = Array("PERSONAL_PHONE" => $userLogin);
                    $USER->Update($arUser["ID"], $fields);
                }

                // ���� ����������� �� ������ �������� � ��������� ������������� ������ ��������
                if ( COption::GetOptionString("main", "new_user_registration_email_confirmation") == "Y" )
                {
                    if ( $loginType == 'phone' )
                    {
                        // ���������� ��� � ����� ���������
                        $code = rand(10000, 99999);
                        $DB->Query("UPDATE `b_user` SET `CONFIRM_CODE`='". $code ."', `ACTIVE`='N' WHERE `ID`=" . intval($arUser["ID"]));
                    
                        $codeText = str_replace( "#CODE#", $code, $options["PHONE_SMS_CONFIRM_MSG"] );
                        self::SendSMS($userLogin, $codeText);
                        
                        // �������� � ������������� ������������� ������ ��������
                        return self::Response("RegisterPhoneConfirm", "register_confirm_phone");
                    }
                    // ���� ����������� �� email � ��������� ������������� email
                    else
                    {
                        // �������� � ������������� ������������� email
                        return self::Response("RegisterEmailConfirm", "register_confirm_email");
                    }
                }
                // ���� ������� ������������� �� ���������, �������� ������������
                else
                {
                    // ������� ������������
                    if ( $USER->Authorize($arUser["ID"]) )
                    {
                        return self::Response("Register", "register_success");
                    }
                }
            } else {
                return self::Response("Error", "register_error_password", "password", $arRegisterResult["MESSAGE"]);
            }
        }
        return self::Response("Error", "unknown_error", "unknown");
    }
    
    /**
    * ������������� �����������
    * 
    * @param string $login ����� ������������
    * @param string $confirmCode ��� �������������
    * @param string $userID ID ������������. �������, ���� �� �������� �����
    * @return string �������� ����������
    */
    public function Confirm ( $login, $confirmCode, $userID = false )
    {
        if ( $login )
        {
            $arUser = self::GetUserByEmail( $login );
        }
        elseif ( $userID )
        {
            $rsUser = CUser::GetByID( $userID );
            $arUser = $rsUser->Fetch();
        }
        
        // ���� ������������ ������ � ������������� ��������� �������������
        if ( $arUser["ACTIVE"] == "N" && strlen($arUser["CONFIRM_CODE"]) > 0 )
        {
            // ���� ������ ���������� ���
            if ( $arUser["CONFIRM_CODE"] == $confirmCode )
            {
                $user = new CUser;
                $user->Update($arUser["ID"], Array( "ACTIVE" => "Y", "CONFIRM_CODE" => "" ));
                
                // ������� ������������
                if ( $user->Authorize($arUser["ID"]) )
                {
                    return self::Response("Register", "confirm_success");
                }
            }
            else
            {
                return self::Response("Error", "confirm_error_code", "confirm_code");
            }
        }
        
        return self::Response("Error", "unknown_error", "unknown");
    }
    
    /**
    * �������� ��������� �������������� ������
    * 
    * @param string $login ����� ������������
    * @param string $captchaCode ��� ������
    * @param string $captchaSid Sid ������
    * @return string �������� ����������
    */
    public function InitRestore ( $login, $captchaCode = false, $captchaSid = false )
    {
        global $APPLICATION;
        
        // ��������� ������
        $options = self::GetOptions();
        
        // ���� ����� �� ���������
        if ( !$loginType = self::CheckLoginType( $login ) )
        {
            if ( $options["USE_EMAIL"] == "Y" && $options["USE_PHONE"] == "Y" )
            {
                return self::Response("Error", "initrestore_error_ep", "login");
                
            }
            elseif ( $arParams["OPTIONS"]["USE_EMAIL"] == "Y" )
            {
                return self::Response("Error", "initrestore_error_e", "login");
            }
            else
            {
                return self::Response("Error", "initrestore_error_p", "login");
            }
        }
        // ���� ������ ����� ��������, �� ����������� �� ���� �� ��������
        elseif( $loginType == "phone" && $options["USE_PHONE"] != "Y" )
        {
            return self::Response("Error", "initrestore_error_e", "login");
        }
        // ���� ������ ����� email, �� ����������� �� ���� �� ��������
        elseif( $loginType == "email" && $options["USE_EMAIL"] != "Y" )
        {
            return self::Response("Error", "initrestore_error_p", "login");
        }
        
        // ���� ������������ ������ � ��
        if ( ($arUser = self::GetUserByEmail( $login )) || ($arUser = self::GetUserByPhone( $login )) )
        {
            if ( $options["EMAIL_RESTORE_CAPTCHA"] == "Y" && !$APPLICATION->CaptchaCheckCode($captchaCode, $captchaSid) )
            {
                return self::Response("Error", "initrestore_error_captcha", "captcha");
            }
            
            // ���� �������� ��������� ����� �������� ���������
            if ( $loginType == "phone" && $timeLimit = self::GetPhoneTimeLimit( $arUser["LOGIN"] ) )
            {
                $hrs = str_pad(floor($timeLimit / 3600), 2, "0", STR_PAD_LEFT);
                $min = str_pad(floor(($timeLimit - $hours * 3600) / 60), 2, "0", STR_PAD_LEFT);
                $sec = str_pad($timeLimit - ($hours * 3600) - ($min * 60), 2, "0", STR_PAD_LEFT);
                
                $limitStr = str_replace(Array("#HOUR#", "#MIN#", "#SEC#"), Array($hrs, $min, $sec), GetMessage('BXMOD_AUTH_INITRESTORE_SMS_LIMIT'));
                return self::Response("Error", "initrestore_error_smslimit", "sms_limit", $limitStr);
            }

            // ���� �������� ���� �������������� ������ ������ �������
            if ( self::SendRestore( $arUser["ID"], $loginType ) )
            {
                if ( $loginType == "phone" )
                {
                    return self::Response("RestoreSendPhone", "initrestore_send_phone");
                }
                else
                {
                    return self::Response("RestoreSendEmail", "initrestore_send_email");
                }
                return self::Response("Error", "unknown_error_", "unknown");
            }
        }
        return self::Response("Error", "initrestore_error_ep", "login");
        // return self::Response("Error", "unknown_error", "unknown");
    }
    
    /**
    * ������� ����� ������ ������������
    * 
    * @param string $login ����� ������������
    * @param string $restoreCode ��� �������������� �������
    * @param string $password ����� ������
    * @param string $rePassword ����� ������ ��������
    * @return �������� ����������
    */
    public function Restore ( $login, $restoreCode, $password, $rePassword )
    {
        // ���� ������������
        $arUser = (self::GetUserByEmail( $login ))?:self::GetUserByPhone( $login );
        
        if ( $arUser["ACTIVE"] == "Y" )
        {
            // �������� "����" ������������ ����� �� ��
            if ( strlen($arUser["CHECKWORD"]) > 32 )
            {
                $salt = substr( $arUser["CHECKWORD"], 0, strlen($arUser["CHECKWORD"]) - 32 );
                $checkword = substr( $arUser["CHECKWORD"], -32 );
            }
            else
            {
                $salt = "";
                $checkword = $arUser["CHECKWORD"];
            }
            
            // ��������� ��� �� ������������ ���� � ��
            if ( $checkword != md5($salt . $restoreCode ) )
            {
                return self::Response("Error", "restore_error_code", "restore_code");
            }
            
            // ��������� ���� ������ � ������������� ������
            if ( $passError = self::CheckPassword( $password, $rePassword ) )
            {
                return self::Response("Error", "restore_error_password", "password", $passError);
            }
            
            $user = new CUser;
            $arRes = $user->ChangePassword($arUser["LOGIN"], $restoreCode, $password, $rePassword);
            if ( $arRes["TYPE"] == "OK")
            {
                // �������� ������������
                $user->Authorize($arUser["ID"]);
                
                return self::Response("Restore", "restore_success");
            } else {
                return self::Response("Error", "restore_error_password", "password", $arRes["MESSAGE"]);
            }
        }
        return self::Response("Error", "unknown_error", "unknown");
    }
    
    /**
    * ���� ������������ � �������� e-mail � ��
    * 
    * @param string $email
    */
    public function GetUserByEmail ( $email )
    {
        if ( $email && $loginType = self::CheckLoginType( $email ) )
        {
            // ���� �����
            if ( $loginType == "email" ) {
                $userEmail = $email;
            } else {
                if ( !$userLogin = self::CheckPhone( $email ) ) return false;
                $userEmail = $userLogin . "@register.phone";
            }
            
            // ���� ������������ � ����� e-mail � ��
            $rsUser = CUser::GetList($by, $order, Array("=EMAIL" => $userEmail, "EXTERNAL_AUTH_ID" => false));
            // ���� ������������ ������ � ��
            if ( $arUser = $rsUser->Fetch() )
            {
                return $arUser;
            }
        }
        return false;
    }
    
    /**
    * ���� ������������ � �������� ��������� � ��
    * 
    * @param string $phone
    */
    public function GetUserByPhone ( $phone )
    {
        if ( $phone && $loginType = self::CheckLoginType( $phone ) )
        {
            // ���� �����
            if ( !$userLogin = self::CheckPhone( $phone ) ) return false;
                $userPhone = substr($userLogin, 1);

            // ���� ������������ � ����� ��������� � ��
            $rsUser = CUser::GetList(($by="ID"), $order, Array("PERSONAL_PHONE" => $userPhone, "PERSONAL_PHONE_EXACT_MATCH" => "Y"));
            // ���� ������������ ������ � ��
            if ( $arUser = $rsUser->Fetch() )
            {
                while(strpos($arUser['EMAIL'], '@fastorder.ru'))
                {
                    $arUser = $rsUser->Fetch();
                }
                return $arUser;
            }
        }
        return false;
    }
    
    /**
    * ������ ������ ������
    *  
    * @param string $type - ��� ������
    * @param string $code - ��� �������
    * @param string $field - ����, �� ������� �������� �������
    * @param string $desc - �������� �������
    */
    public function Response ( $type, $code = false, $field = false, $desc = false )
    {
        $result = Array(
            "TYPE" => $type,
            "DESC" => $desc ? $desc : GetMessage( "BXMOD_AUTH_" . strtoupper( $code ) ),
            "FIELD" => $code,
            "FIELD" => $field
        );
        
        return $result;
    }
    
    /**
    * ������������ ������ �� ��������� ����� � UTF-8
    * 
    * @param String $str
    * @return String
    */
    public function StrToUtf8( $str )
    {
        if ( defined("LANG_CHARSET") && strtolower( LANG_CHARSET ) != "utf-8" )
        {
            $str = iconv( LANG_CHARSET, "utf-8", $str );
        }
        
        return $str;
    }
    
    /**
    * ������������ ������ �� UTF-8 � ��������� �����
    * 
    * @param String $str
    * @return String
    */
    public function StrFromUtf8( $str )
    {
        if ( defined("LANG_CHARSET") && strtolower( LANG_CHARSET ) != "utf-8" )
        {
            $str = iconv( "utf-8", LANG_CHARSET, $str );
        }
        
        return $str;
    }

    /**
    * ���������� �������� ������ ��� ����� ������
    * 
    * @param String $userEmail
    * @return Array
    */
    public function funSendMailRestorePassword($userEmail)
    {
        $arResult["result"] = false;

        if($userEmail and filter_var($userEmail, FILTER_VALIDATE_EMAIL))
        {
            $arUser = CUser::GetByLogin($userEmail)->Fetch();

            if($arUser)
            {
                $arFields = array(
                    "EMAIL" => $arUser["EMAIL"],
                    "NAME" => ($arUser["NAME"] != "") ? $arUser["NAME"] : $arUser["LAST_NAME"],
                    "ADV_ID" => fUserForcedAuthorization($arUser["ID"]),
                );

                CEvent::SendImmediate("REQUEST_USER_PASS", SITE_ID, $arFields);
                //CUser::SendUserInfo($arUser["ID"], 's1', GetMessage("INFO_REQ"), true, 'REQUEST_USER_PASS');
                $arResult["result"] = true;
            }
            else
            {
                $arResult["error"] = array(
                    "no" => 2,
                    "text" => "email not found",
                );
            }
        }
        else
        {
            $arResult["error"] = array(
                "no" => 1,
                "text" => "email not valid",
            );
        }

        return $arResult;
    }

}
?>