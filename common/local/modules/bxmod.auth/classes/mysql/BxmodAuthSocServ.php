<?php
#################################
#   Developer: Lynnik Danil     #
#   Site: http://bxmod.ru       #
#   E-mail: support@bxmod.ru    #
#################################

IncludeModuleLangFile(__FILE__);

abstract class BxmodAuthSocServ
{
    // данные дл€ авторизации
    public $userID = false;
    public $userEmail = '';
    public $userNickname = '';
    public $userName = '';
    public $userLastName = '';
    public $userSex = '';
    public $userBDay = 0;
    public $bLogin = '';
    public $bPersWWW = '';
    public $bXMLID = '';
    public $bExtAuthID = 'socservices';
    public $userPhoto = false;
    
    /** ƒанные пользовател€ при успешной авторизации */
    public $userData = Array();
    
    /** ѕопытка авторизации через —оц. сервис */
    abstract public function SocLogin();
    
    /** ѕопытка авторизации пользовател€ */
    public function Login ()
    {
        global $DB;
        
        if ( !$this->userID || !CModule::IncludeModule("socialservices") ) return false;
        
        $userOb = new CUser();
        
        $dbSocUser = CSocServAuthDB::GetList(array(), array('XML_ID'=>$this->bXMLID, 'EXTERNAL_AUTH_ID'=>$this->bExtAuthID), false, false, array("ID", "USER_ID", "ACTIVE"));
        $dbUsersOld = CUser::GetList($by='ID', $ord='ASC', array('XML_ID'=>$this->bXMLID, 'EXTERNAL_AUTH_ID'=>$this->bExtAuthID, 'ACTIVE'=>'Y'), array('NAV_PARAMS'=>array("nTopCount"=>"1")));
        $dbUsersNew = CUser::GetList($by='ID', $ord='ASC', array('XML_ID'=>$this->bXMLID, 'EXTERNAL_AUTH_ID'=>$this->bExtAuthID, 'ACTIVE'=>'Y'),  array('NAV_PARAMS'=>array("nTopCount"=>"1")));
        
        if( $arUser = $dbSocUser->Fetch() )
        {
            if( $arUser["ACTIVE"] === 'Y' )
            {
                $USER_ID = $arUser["USER_ID"];
            }
        }
        elseif( $arUser = $dbUsersOld->Fetch() )
        {
            $USER_ID = $arUser["ID"];
        }
        elseif($arUser = $dbUsersNew->Fetch())
        {
            $USER_ID = $arUser["ID"];
        }
        // ƒобавл€ем нового пользовател€
        elseif(COption::GetOptionString("main", "new_user_registration", "N") == "Y")
        {
            $nowTime = $DB->CharToDateFunction(ConvertTimeStamp(time(), "FULL"));
            $arFields = Array(
                "LOGIN"               => $this->bLogin,
                "PASSWORD"            => randString(30),
                "CHECKWORD"           => randString(30),
                "ACTIVE"              => "Y",
                "LID"                 => strlen(SITE_ID) > 0 ? SITE_ID : false,
                "NAME"                => BxmodAuth::StrFromUtf8($this->userName),
                "LAST_NAME"           => BxmodAuth::StrFromUtf8($this->userLastName),
                "EMAIL"               => $this->userEmail,
                "PERSONAL_GENDER"     => $this->userSex,
                //"PERSONAL_BIRTHDAY"   => $this->userBDay ? date("d.m.Y", $this->userBDay) : false,
                "PERSONAL_WWW"        => $this->bPersWWW,
                "PERSONAL_PHOTO"      => $this->userPhoto,
                "XML_ID"              => $this->bXMLID,
                "EXTERNAL_AUTH_ID"    => $this->bExtAuthID,
                "LOGIN_ATTEMPTS"      => 0
            );
            
            $default_group = COption::GetOptionString("main", "new_user_registration_def_group", "2");
            if ( $default_group != "" ) {
                $arFields["GROUP_ID"] = explode(",", $default_group);
            }
            
            if( !($USER_ID = $userOb->Add($arFields)))
            {
                die( $userOb->LAST_ERROR );
            }
            $arFields['CAN_DELETE'] = 'N';
            $arFields['USER_ID'] = $USER_ID;
            $id = CSocServAuthDB::Add($arFields);
        }
        if ( isset( $USER_ID ) && $USER_ID > 0 )
        {
            $userOb->Authorize($USER_ID);
        }
        
        if ( isset( $_SESSION["BXMOD_AUTH_LAST_PAGE"] ) ) {
            Header("Location: " . $_SESSION["BXMOD_AUTH_LAST_PAGE"]);
        } else {
            Header("Location: /");
        }
        exit;
    }
    
    /** ѕолучить все данные об этом способе авторизации */
    public function Get(){}
    
    /** ѕолучить страницу редиректа */
    public function GetRedirectPage ( $type )
    {
        if ( defined("SITE_SERVER_NAME") && SITE_SERVER_NAME )
        {
            $url = "http://" . SITE_SERVER_NAME;
        }
        else
        {
            $url = $_SERVER['HTTP_HOST'];
        }
        
        $url = urlencode( $url . "/bitrix/tools/bxmod_auth.php?type={$type}" );
        
        return $url;
    }
}

class BxmodAuthSocServFB extends BxmodAuthSocServ
{
    public function SocLogin()
    {
        $options = BxmodAuth::GetOptions();
        
        if ( isset( $_GET['code'] ) && $data = @file_get_contents( 'https://graph.facebook.com/oauth/access_token?client_id='. $options["soc_fbID"] .'&redirect_uri='. self::GetRedirectPage("fb") .'&client_secret='. $options["soc_fbKey"] .'&code='. $_GET['code'] ) ) {
            parse_str( $data, $str );
            if ( isset( $str['access_token'] ) )
            {
                if ( $data = @file_get_contents("https://graph.facebook.com/me?access_token={$str['access_token']}") ) {
                    $data = json_decode( $data, true );
                    
                    if ( isset( $data['id'] ) && $data['id'] ) {
                        
                        $this->bExtAuthID = "Facebook";
                    
                        $data["name"] = explode(" ", $data["name"]);
                        
                        $this->userName = strlen($data["first_name"]) > 0 ? $data["first_name"] : $data["name"][0];
                        $this->userLastName = strlen($data["last_name"]) > 0 ? $data["last_name"] : $data["name"][1];
                        $this->userID = "FB_" . $data['id'];
                        $this->userEmail = $data['email'];
                        $this->userBDay = strtotime( $data['birthday'] );
                        $this->userNickname = $this->userID;
                        $this->bLogin = "FB_" . $data['id'];
                        $this->bPersWWW = $data["link"];
                        $this->bXMLID = $data['id'];
                        
                        if ( !empty($data["gender"]) )
                        {
                            $this->userSex = $data["gender"] == "male" ? "M" : "F";
                        }
                        
                        if ( isset( $data['picture']['data']['url'] ) && !empty($data['picture']['data']['url']) )
                        {
                            $this->userPhoto = CFile::MakeFileArray( $data['picture']['data']['url'] );
                        }
                        
                        return $this->Login();
                    }
                }
            }
        }
        return false;
    }
    
    /** ѕолучить все данные об этом способе авторизации */
    public function Get ()
    {
        $options = BxmodAuth::GetOptions();
        
        $result = Array(
            "NAME" => GetMessage("BXMOD_AUTH_SOC_FB"),
            "TRANSLIT" => "facebook",
            "CLASS" => __CLASS__,
            "LINK" => "https://www.facebook.com/dialog/oauth?client_id={$options["soc_fbID"]}&response_type=code&redirect_uri=" . self::GetRedirectPage("fb")
        );
        
        return $result;
    }
}

class BxmodAuthSocServGG extends BxmodAuthSocServ
{
    public function SocLogin()
    {
        $options = BxmodAuth::GetOptions();
        
        if ( isset( $_GET['code'] ) && $data = file_get_contents( 'https://accounts.google.com/o/oauth2/token', false, stream_context_create( array( 'http'=>array( 'method' => 'POST', 'header'  => 'Content-type: application/x-www-form-urlencoded', 'content' => "code={$_GET['code']}&client_id={$options["soc_ggID"]}&client_secret={$options["soc_ggKey"]}&redirect_uri=". self::GetRedirectPage("gg") ."&grant_type=authorization_code" ) ) ) ) ) {
            $token = json_decode( $data, true );
            
            if ( $data = file_get_contents("https://www.googleapis.com/oauth2/v1/userinfo?access_token={$token['access_token']}") ) {
                $data = json_decode( $data, true );
                
                if ( isset( $data['id'] ) && $data['id'] ) {
                    $this->bExtAuthID = "GoogleOAuth";
                    
                    $data["name"] = explode(" ", $data["name"]);
                    $this->userName = $data["given_name"] ? $data["given_name"] : $data["name"][0];
                    $this->userLastName = $data["family_name"] ? $data["family_name"] : $data["name"][1];
                    
                    $this->userID = "G_" . $data['id'];
                    $this->userEmail = $data['email'];
                    $this->userNickname = $this->userID;
                    $this->bLogin = "G_" . $data['email'];
                    $this->bPersWWW = $data["link"];
                    $this->bXMLID = $data['email'];
                    
                    if ( !empty($data["gender"]) )
                    {
                        $this->userSex = $data["gender"] == "male" ? "M" : "F";
                    }
                    
                    if ( !empty($data["picture"]) )
                    {
                        $this->userPhoto = CFile::MakeFileArray( $data['picture'] );
                    }
                    
                    return $this->Login();
                }
            }
        }
        return false;
    }
    
    /** ѕолучить все данные об этом способе авторизации */
    public function Get ()
    {
        $options = BxmodAuth::GetOptions();
        
        $result = Array(
            "NAME" => GetMessage("BXMOD_AUTH_SOC_GG"),
            "TRANSLIT" => "google",
            "CLASS" => __CLASS__,
            "LINK" => "https://accounts.google.com/o/oauth2/auth?redirect_uri=". self::GetRedirectPage("gg") ."&response_type=code&client_id={$options["soc_ggID"]}&scope=https://www.googleapis.com/auth/userinfo.email+https://www.googleapis.com/auth/userinfo.profile"
        );
        
        return $result;
    }
}

class BxmodAuthSocServMR extends BxmodAuthSocServ
{
    public function SocLogin()
    {
        $options = BxmodAuth::GetOptions();

        if ( isset( $_GET['code'] ) && $data = file_get_contents( 'https://connect.mail.ru/oauth/token', false, stream_context_create( array( 'http'=>array( 'method' => 'POST', 'header'  => 'Content-type: application/x-www-form-urlencoded', 'content' => "client_id=". $options["soc_mrID"] ."&client_secret=". $options["soc_mrSecretKey"] ."&grant_type=authorization_code&code={$_GET['code']}&redirect_uri=" . self::GetRedirectPage("mr") ) ) ) ) ) {
            $token = json_decode( $data, true );
            $sign = md5('app_id='. $options["soc_mrID"] .'method=users.getInfosecure=1session_key='. $token['access_token'] . $options["soc_mrSecretKey"]);
            if ( $data = @file_get_contents("http://www.appsmail.ru/platform/api?method=users.getInfo&secure=1&app_id=". $options["soc_mrID"] ."&session_key={$token['access_token']}&sig={$sign}") ) {
                $data = json_decode( $data, true );
                if ( isset( $data[0]['uid'] ) && $data[0]['uid'] ) {
                    $data = $data[0];

                    $this->bExtAuthID = "MyMailRu";

                    $data["nick"] = explode(" ", $data["nick"]);

                    $this->userName = strlen($data["first_name"]) > 0 ? $data["first_name"] : $data["nick"][0];
                    $this->userLastName = strlen($data["last_name"]) > 0 ? $data["last_name"] : $data["nick"][1];
                    $this->userBDay = strtotime( $data['birthday'] );
                    $this->userID = "MM_" . $data['id'];
                    $this->userEmail = $data['email'];
                    $this->userNickname = $this->userID;
                    $this->bLogin = "MM_" . $data['email'];
                    $this->bPersWWW = $data["link"];
                    $this->bXMLID = $data['uid'];

                    if ( !empty($data["sex"]) )
                    {
                        $this->userSex = $data["sex"] == 1 ? "M" : "F";
                    }

                    if ( !empty($data["pic_190"]) )
                    {
                        //  опирование необходимо, т.к. ссылка на файл картинки не проходит валидацию.
                        @mkdir( $_SERVER["DOCUMENT_ROOT"] . "/upload/temp/" );
                        @mkdir( $_SERVER["DOCUMENT_ROOT"] . "/upload/temp/avatars/" );

                        if ( $file = @file_get_contents( $data['pic_190'] . '?name=/'.md5($data['pic_190']).'.jpg' ) ) {
                            $imageName = md5( $data['pic_190'] . '?name=/'.md5($data['pic_190']).'.jpg' ).'.jpg';
                            $imageFile = fopen($_SERVER["DOCUMENT_ROOT"] . "/upload/temp/avatars/" . $imageName, "wb");
                            fwrite($imageFile, $file);
                            fclose($imageFile);

                            $this->userPhoto = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"] . "/upload/temp/avatars/" . $imageName);
                        }
                    }

                    return $this->Login();
                }
            }
        }
        return false;
    }

    /** ѕолучить все данные об этом способе авторизации */
    public function Get ()
    {
        $options = BxmodAuth::GetOptions();

        $result = Array(
            "NAME" => GetMessage("BXMOD_AUTH_SOC_MR"),
            "TRANSLIT" => "mailru",
            "CLASS" => __CLASS__,
            "LINK" => "https://connect.mail.ru/oauth/authorize?client_id={$options["soc_mrID"]}&response_type=code&redirect_uri=" . self::GetRedirectPage("mr")
        );

        return $result;
    }
}

class BxmodAuthSocServOK extends BxmodAuthSocServ
{
    public function SocLogin()
    {
        $options = BxmodAuth::GetOptions();

        if ( isset( $_GET['code'] ) && $data = file_get_contents( 'http://api.odnoklassniki.ru/oauth/token.do', false, stream_context_create( array( 'http'=>array( 'method' => 'POST', 'header'  => 'Content-type: application/x-www-form-urlencoded', 'content' => "code={$_GET['code']}&redirect_uri=". self::GetRedirectPage("ok") ."&grant_type=authorization_code&client_id=". $options["soc_okID"] ."&client_secret=". $options["soc_odSecretKey"] ) ) ) ) ) {
            $token = json_decode( $data, true );

            $sign = md5('application_key='. $options["soc_odKey"] .'method=users.getCurrentUser'. md5( $token['access_token'] . $options["soc_odSecretKey"] ));

            if ( $data = @file_get_contents("http://api.odnoklassniki.ru/fb.do?method=users.getCurrentUser&access_token={$token['access_token']}&application_key={$options["soc_odKey"]}&sig={$sign}") ) {
                $data = json_decode( $data, true );
                if ( isset( $data['uid'] ) && $data['uid'] ) {
                    $this->bExtAuthID = "Odnoklassniki";

                    $this->userID = 'OKuser:' . $data['uid'];
                    $this->userNickname = $data['name'];
                    $this->userName = $data['first_name'];
                    $this->userLastName = $data['last_name'];
                    $this->userBDay = strtotime( $data['birthday'] );
                    $this->bLogin = "OKuser" . $data['uid'];
                    $this->bPersWWW = "http://odnoklassniki.ru/profile/" . $data['uid'];
                    $this->bXMLID = "OK" . $data['uid'];

                    if ( !empty($data["gender"]) )
                    {
                        $this->userSex = $data["gender"] == 'male' ? "M" : "F";
                    }

                    if ( !empty($data["pic_2"]) )
                    {
                        //  опирование необходимо, т.к. ссылка на файл картинки не проходит валидацию.
                        @mkdir( $_SERVER["DOCUMENT_ROOT"] . "/upload/temp/" );
                        @mkdir( $_SERVER["DOCUMENT_ROOT"] . "/upload/temp/avatars/" );

                        if ( $file = @file_get_contents( $data['pic_2'].'&name=/'.md5($data['pic_2']).'.jpg' ) ) {
                            $imageName = md5($data['pic_2'] . '&name=/' . md5($data['pic_2'])) . '.jpg';
                            $imageFile = fopen($_SERVER["DOCUMENT_ROOT"] . "/upload/temp/avatars/" . $imageName, "wb");
                            fwrite($imageFile, $file);
                            fclose($imageFile);

                            $this->userPhoto = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"] . "/upload/temp/avatars/" . $imageName);
                        }
                    }

                    return $this->Login();
                }
            }
        }
        return false;
    }

    public function Get ()
    {
        $options = BxmodAuth::GetOptions();

        $result = Array(
            "NAME" => GetMessage("BXMOD_AUTH_SOC_OK"),
            "TRANSLIT" => "odnoklassniki",
            "CLASS" => __CLASS__,
            "LINK" => "http://www.odnoklassniki.ru/oauth/authorize?client_id={$options["soc_okID"]}&response_type=code&redirect_uri=" . self::GetRedirectPage("ok")
        );

        return $result;
    }
}

class BxmodAuthSocServVK extends BxmodAuthSocServ
{
    public function SocLogin()
    {
        $options = BxmodAuth::GetOptions();
        
        // запрашиваем токен
        if ( isset( $_GET['code'] ) && $data = @file_get_contents( 'https://oauth.vk.com/access_token?client_id='. $options["soc_vkID"] .'&client_secret='. $options["soc_vkKey"] .'&code='. htmlspecialchars($_GET['code'], ENT_QUOTES) .'&redirect_uri=' . self::GetRedirectPage("vk") ) ) {
            $token = json_decode( $data, true );
            // получаем данные пользовател€
            if ( $data = @file_get_contents('https://api.vk.com/method/users.get?access_token=' . $token['access_token'] . '&uids='. $token['user_id'] .'&fields=uid,first_name,last_name,screen_name,sex,bdate,photo_big') ) {
                $data = json_decode( $data, true );
                if ( isset( $data['response'][0]['uid'] ) && $data['response'][0]['uid'] ) {
                    $data = $data['response'][0];
                    
                    $this->bExtAuthID = "VKontakte";
                    
                    $this->userID = 'VKuser:' . $data['uid'];
                    $this->userNickname = $data['screen_name'];
                    $this->userName = $data['first_name'];
                    $this->userLastName = $data['last_name'];
                    $this->bLogin = "VKuser" . $data['uid'];
                    $this->bPersWWW = "http://vk.com/id" . $data['uid'];
                    $this->bXMLID = $data['uid'];
                    
                    if ( !empty($data['bdate']) )
                    {
                        $data['bdate'] = explode(".", $data['bdate']);
                        if ( count($data['bdate']) == 3 )
                        {
                            $this->userBDay = mktime(0,0,0,intval($data['bdate'][1]),intval($data['bdate'][0]),intval($data['bdate'][2]));
                        }
                    }
                    if ( !empty($data["sex"]) )
                    {
                        $this->userSex = $data["sex"] == 2 ? "M" : "F";
                    }
                    
                    if ( !empty($data["photo_big"]) )
                    {
                        $this->userPhoto = CFile::MakeFileArray( $data["photo_big"] );
                    }
                    
                    // попытка авторизации
                    return $this->Login();
                }
            }
        }
        return false;
    }
    
    /** ѕолучить все данные об этом способе авторизации */
    public function Get ()
    {
        $options = BxmodAuth::GetOptions();
        
        $result = Array(
            "NAME" => GetMessage("BXMOD_AUTH_SOC_VK"),
            "TRANSLIT" => "vkontakte",
            "CLASS" => __CLASS__,
            "LINK" => "http://oauth.vk.com/authorize?client_id={$options["soc_vkID"]}&scope=groups&response_type=code&redirect_uri=" . self::GetRedirectPage("vk")
        );
        
        return $result;
    }
}

class BxmodAuthSocServYA extends BxmodAuthSocServ
{
    public function SocLogin()
    {
        $options = BxmodAuth::GetOptions();

        if ( isset( $_GET['code'] ) && $data = file_get_contents( 'https://oauth.yandex.ru/token', false, stream_context_create( array( 'http'=>array( 'method' => 'POST', 'header'  => 'Content-type: application/x-www-form-urlencoded', 'content' => "grant_type=authorization_code&code={$_GET['code']}&client_id={$options["soc_yaID"]}&client_secret={$options["soc_yaKey"]}" ) ) ) ) ) {
            $token = json_decode( $data, true );

            if ( $data = file_get_contents("https://login.yandex.ru/info?format=json&oauth_token={$token['access_token']}") ) {
                $data = str_replace( '": "', '":"', $data );
                $data = json_decode( $data, true );

                if ( isset( $data['id'] ) && $data['id'] ) {
                    
                    $this->bExtAuthID = "YandexOAuth";
                    
                    $userName = explode(" ", $data['real_name']);
                    $this->userName = $userName[0] ? $userName[0] : "";
                    $this->userLastName = $userName[1] ? $userName[1] : "";
                    
                    $this->userID = "Yandex:" . $data['id'];
                    $this->userEmail = $data['default_email'];
                    $this->userNickname = $data['display_name'];
                    $this->userBDay = strtotime( $data['birthday'] );
                    $this->bLogin = "openid.yandex.ru/" . $this->userNickname;
                    $this->bPersWWW = "http://openid.yandex.ru/" . $this->userNickname;
                    $this->bXMLID = "http://openid.yandex.ru/" . $this->userNickname;

                    if ( !empty($data["sex"]) )
                    {
                        $this->userSex = $data["sex"] == "male" ? "M" : "F";
                    }



                    return $this->Login();
                }
            }
        }
        return false;
    }
    
    /** ѕолучить все данные об этом способе авторизации */
    public function Get ()
    {
        $options = BxmodAuth::GetOptions();
        
        $result = Array(
            "NAME" => GetMessage("BXMOD_AUTH_SOC_YA"),
            "TRANSLIT" => "yandex",
            "CLASS" => __CLASS__,
            "LINK" => "https://oauth.yandex.ru/authorize?response_type=code&client_id=" . $options["soc_yaID"]
        );
        
        return $result;
    }
}

class BxmodAuthSocServTW extends BxmodAuthSocServ
{
    public function SocLogin()
    {
        $options = BxmodAuth::GetOptions();
        
        if ( $options["soc_tw"] != "Y" ) return false;
        
        $parameters = self::GetParams();
        $parameters["oauth_token"] = $_GET["oauth_token"];
        
        $url = "https://api.twitter.com/oauth/access_token?oauth_verifier={$_GET["oauth_verifier"]}&";
        
        foreach ( $parameters AS $k=>$v ) {
            if ( $k == "oauth_token_secret" ) continue;
            $url .= $k ."=". self::FixUrlencode($v) . "&";
        }
        
        $result = @file_get_contents( $url );
        parse_str( $result, $data );
        
        if ( isset( $data["user_id"] ) && isset( $data["screen_name"] ) ) {
            $this->userName = $data["screen_name"];
            $this->userLastName = "";
            
            $this->userID = "Twitter:" . $data['user_id'];
            $this->userEmail = $data['user_id'] . "@oauthTwitter";
            $this->userNickname = $data['screen_name'];
            $this->bLogin = "twitter.com/" . $data['user_id'];
            $this->bPersWWW = "https://twitter.com/" . $data["screen_name"];
            $this->bXMLID = "https://twitter.com/" . $data["screen_name"];
        
            return $this->Login();
        }
        
        return false;
    }
    
    public function Get ()
    {
        $result = Array(
            "NAME" => GetMessage("BXMOD_AUTH_SOC_TW"),
            "TRANSLIT" => "twitter",
            "CLASS" => __CLASS__,
            "LINK" => "/bitrix/tools/bxmod_auth.php?twitterAuth=true"
        );
        
        return $result;
    }
    
    public function GoToAuth ()
    {
        ob_clean();
        
        $url = "https://api.twitter.com/oauth/request_token";
        
        $parameters = self::AddSignature( $url, self::GetParams());
        
        $url .= "?";
        
        foreach ( $parameters AS $k=>$v ) $url .= $k ."=". self::FixUrlencode($v) . "&";
        
        parse_str( @file_get_contents( $url ), $data );
        
        $url = "https://api.twitter.com/oauth/authorize?oauth_token={$data["oauth_token"]}&";
        
        $parameters = self::AddSignature( $url, self::GetParams( $data["oauth_token_secret"] ));
        
        foreach ( $parameters AS $k=>$v ) {
            if ( $k == "oauth_token_secret" ) continue;
            $url .= $k ."=". self::FixUrlencode($v) . "&";
        }
        
        Header( "Location: {$url}" );
        
        exit;
    }
    
    public function GetParams ( $secret = false )
    {
        $options = BxmodAuth::GetOptions();
        
        $parameters = Array();
        $parameters["oauth_callback"] = "";
        $parameters["oauth_consumer_key"] = $options["soc_twID"];
        $parameters["oauth_nonce"] = md5(microtime() . mt_rand());
        $parameters["oauth_signature_method"] = "HMAC-SHA1";
        $parameters["oauth_timestamp"] = time();
        $parameters["oauth_version"] = "1.0";
        
        if ( $secret ) $parameters["oauth_version"] = $secret;
        
        return $parameters;
    }
    
    public function FixUrlencode ( $str ) {
        return str_replace( '+', ' ', str_replace('%7E', '~', rawurlencode($str)) );
    }
    
    public function AddSignature ( $url, $parameters ) {
        $options = BxmodAuth::GetOptions();
        
        $base_string = "";
        
        ksort( $parameters );
        
        foreach ( $parameters AS $k => $v ) {
            if ( $k == "oauth_token_secret" || $k == "user_id" ) continue;
            $base_string .= "{$k}=".self::FixUrlencode($v)."&";
        }
        
        $base_string = "GET&" . self::FixUrlencode($url) . "&" . self::FixUrlencode( substr( $base_string, 0, -1 ) );
        
        $key = self::FixUrlencode($options["soc_twKey"]) . "&";
        if ( isset( $parameters["oauth_token_secret"] ) ) $key .= self::FixUrlencode($parameters["oauth_token_secret"]);
        
        $parameters["oauth_signature"] = base64_encode(hash_hmac('sha1', $base_string, $key, true));
        
        return $parameters;
    }
}
?>