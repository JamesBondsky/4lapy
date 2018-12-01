<?
#################################
#   Developer: Lynnik Danil     #
#   Site: http://bxmod.ru       #
#   E-mail: support@bxmod.ru    #
#################################

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

// Без модуля компонент не работает
if ( !CModule::IncludeModule("bxmod.auth") ) return false;

if ( !function_exists("bxmodAuthResponse") )
{
    /**
    * Функция перезаписывает ответы модуля для привязки этих ответов к полям формы при отдаче Ajax`ом. Чистит буфер и отдает в него соответствующий ответ
    * 
    * @param array $response Ответ модуля авторизации
    * @param array $replace Массив сопоставления полей ответа и полей ввода
    */
    function bxmodAuthResponse ( $response, $replace = Array() )
    {
        global $APPLICATION;
        
        $APPLICATION->RestartBuffer();
        
        if ( $response["TYPE"] == "Error" && !empty( $replace ) && $response["FIELD"] )
        {
            foreach ( $replace AS $k=>$v )
            {
                $response["FIELD"] = str_replace($k, $v, $response["FIELD"]);
            }
            
            die("{$response["FIELD"]}[Error]:{$response["DESC"]}");
        }

        die( $response["TYPE"] );
    }
}

$arResult = Array();

// Признак необходимости авторизации
$arParams["LOGIN_FORM"] = true;
// Поведение диалога после авторизации, регистрации, восстановления. RELOAD - перезагрузить страницу, AJAX - подгрузить меню пользователя, скрыть диалог авторизации
$arParams["SUCCESS_TYPE"] = $arParams["SUCCESS_TYPE"] == "RELOAD" ? "RELOAD" : "AJAX";
// Время показа сообщения после авторизации, регистрации, восстановления
$arParams["SUCCESS_RELOAD_TIME"] = intval( $arParams["SUCCESS_RELOAD_TIME"] ) ? intval($arParams["SUCCESS_RELOAD_TIME"]) : 5;

// Если пользователь уже авторизован
if( $USER->IsAuthorized() )
{
    // Признак необходимости авторизации
    $arParams["LOGIN_FORM"] = false;
    // Ссылка для выхода (logout)
    $arResult["LOGOUT_LINK"] = $APPLICATION->GetCurPageParam("logout=yes", Array( "logout" ) );
    
    $arResult["USER_LOGIN"] = $USER->GetFullName();
    if ( !$arResult["USER_LOGIN"] ) $arResult["USER_LOGIN"] = $USER->GetLogin();
    if ( !$arResult["USER_LOGIN"] ) $arResult["USER_LOGIN"] = $USER->GetEmail();
}
// Если пользователь еще не авторизован
else
{
    // все настройки модуля авторизации
    $arParams["OPTIONS"] = BxmodAuth::GetOptions();
    
    // Название параметра передачи USER_CHECKWORD при восстановлении доступа
    $arParams["GET_RESTORE_CHECKWORD"] = $arParams["OPTIONS"]["GET_RESTORE_CHECKWORD"] ? $arParams["OPTIONS"]["GET_RESTORE_CHECKWORD"] : "USER_CHECKWORD";
    // Название параметра передачи USER_ID при восстановлении доступа
    $arParams["GET_RESTORE_USER_ID"] = $arParams["OPTIONS"]["GET_RESTORE_USER_ID"] ? $arParams["OPTIONS"]["GET_RESTORE_USER_ID"] : "USER_ID";
    // Название параметра передачи USER_ID при подтверждении регистрации
    $arParams["GET_CONFIRM_USER_ID"] = $arParams["OPTIONS"]["GET_CONFIRM_USER_ID"] ? $arParams["OPTIONS"]["GET_CONFIRM_USER_ID"] : "confirm_user_id";
    // Название параметра передачи CONFIRM_CODE при подтверждении регистрации
    $arParams["GET_CONFIRM_CODE"] = $arParams["OPTIONS"]["GET_CONFIRM_CODE"] ? $arParams["OPTIONS"]["GET_CONFIRM_CODE"] : "confirm_code";
    // Адрес страницы для ее перезагрузки
    $arParams["DOCUMENT_LOCATION"] = $APPLICATION->GetCurPageParam(false, Array("logout", $arParams["GET_RESTORE_CHECKWORD"], $arParams["GET_RESTORE_USER_ID"], $arParams["GET_CONFIRM_USER_ID"], $arParams["GET_CONFIRM_CODE"]));
    
    // Подключаем jQuery
    CJSCore::Init(array("jquery"));
    
    // каптча при авторизации
    if ( $arParams["OPTIONS"]["EMAIL_CAPTCHA"] == "Y" )
    {
        $arResult["CAPTCHA_LOGIN_CODE"] = $APPLICATION->CaptchaGetCode();
    }
    
    // каптча при восстановлении
    if ( $arParams["OPTIONS"]["EMAIL_RESTORE_CAPTCHA"] == "Y" )
    {
        $arResult["CAPTCHA_RESTORE_CODE"] = $APPLICATION->CaptchaGetCode();
    }
    
    // Если в куках есть логин пользователя
    $arResult["~USER_LOGIN"] = $_COOKIE[COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_LOGIN"];
    $arResult["USER_LOGIN"] = htmlspecialcharsbx($arResult["~USER_LOGIN"]);
    // Если в куках не мыло и не телефон, то пытаемся отыскать пользователя с таким логином
    // Это сделано для того, что бы в поле e-mail подставлять именно e-mail пользователя, а не его логин
    if ( $arResult["USER_LOGIN"] && (
        substr($arResult["USER_LOGIN"], 0, 2) == "G_" ||
        substr($arResult["USER_LOGIN"], 0, 3) == "MM_" ||
        substr($arResult["USER_LOGIN"], 0, 3) == "FB_" ||
        !$loginType = BxmodAuth::CheckLoginType( $arResult["USER_LOGIN"] ) ) )
    {
        $rsUser = CUser::GetByLogin( $arResult["USER_LOGIN"] );
        if ( $arUser = $rsUser->Fetch() )
        {
            $arResult["USER_LOGIN"] = htmlspecialcharsbx($arUser["EMAIL"]);
        }
    }
    
    // возможные для входа социальные сервисы
    if ( $arParams["OPTIONS"]["USE_SOCIAL"] == "Y" )
    {
        $arResult["AUTH_SERVICES"] = BxmodAuth::GetSocList();
    }
    
    // пользователь пришел по ссылке подтверждения регистрации по email
    if ( isset( $_GET[$arParams["GET_CONFIRM_USER_ID"]] ) && isset( $_GET[$arParams["GET_CONFIRM_CODE"]] ) && !defined("BXMOD_AUTH_OPEN_DIALOG") )
    {
        // Константа объявляется для того, что бы не отображались другие диалоги авторизации, если их несколько на странице
        define("BXMOD_AUTH_OPEN_DIALOG", true);
        
        $arResult["CONFIRM_EMAIL"] = Array(
            "SUCCESS" => "N"
        );
        
        // пытаемся подтвердить регистрацию
        $regResult = BxmodAuth::Confirm( false, $_GET[$arParams["GET_CONFIRM_CODE"]], $_GET[ $arParams["GET_CONFIRM_USER_ID"] ] );
        if ( $regResult["TYPE"] == "Register" )
        {
            $arResult["CONFIRM_EMAIL"]["SUCCESS"] = "Y";
        }
    }
    // пользователь перешел по ссылке восстановления пароля
    elseif ( isset( $_GET[ $arParams["GET_RESTORE_CHECKWORD"] ] ) && isset( $_GET[ $arParams["GET_RESTORE_USER_ID"] ] ) && !defined("BXMOD_AUTH_OPEN_DIALOG") )
    {
        // Константа объявляется для того, что бы не отображались другие диалоги авторизации, если их несколько на странице
        define("BXMOD_AUTH_OPEN_DIALOG", true);
        
        // Ищем пользователя в БД
        $rsUser = CUser::GetByID( $_GET[ $arParams["GET_RESTORE_USER_ID"] ] );
        
        // Если пользователь найден
        if ( $arUser = $rsUser->Fetch() )
        {
            // Данные для открытия диалога авторизации и восстановления пароля
            $arResult["RESTORE_PASS"] = Array(
                // Логин пользователя
                "LOGIN" => $arUser["LOGIN"],
                // Тип логина пользователя
                "LOGIN_TYPE" => BxmodAuth::CheckLoginType( $arUser["LOGIN"] ),
                // Код подтверждения восстановления доступа
                "CHECKWORD" => isset( $_GET[ $arParams["GET_RESTORE_CHECKWORD"] ] ) ? htmlspecialchars( $_GET[ $arParams["GET_RESTORE_CHECKWORD"] ], ENT_QUOTES ) : ""
            );
        }
    }
    
    // авторизация / регистрация
    if ( isset( $_POST["bxmodAuthEmail"] ) )
    {
        $result = BxmodAuth::Login (
            $_POST["bxmodAuthEmail"],
            $_POST["bxmodAuthPass"],
            isset( $_POST["bxmodAuthCaptcha"] ) ? $_POST["bxmodAuthCaptcha"] : false,
            isset( $_POST["bxmodAuthCaptchaSid"] ) ? $_POST["bxmodAuthCaptchaSid"] : false,
            isset( $_POST["bxmodAuthRemember"] )
        );
        
        bxmodAuthResponse( $result, Array("login" => "bxmodAuthEmail", "password" => "bxmodAuthPass", "captcha" => "bxmodAuthCaptcha") );
    }
    // подтверждение email / номера телефона
    elseif ( isset( $_POST["bxmodAuthConfirmCode"] ) )
    {
        $result = BxmodAuth::Confirm (
            $_POST["bxmodAuthConfirmLogin"],
            $_POST["bxmodAuthConfirmCode"]
        );
        
        bxmodAuthResponse( $result, Array( "confirm_code" => "bxmodAuthConfirmCode" ) );
    }
    // запрос восстановления доступа
    elseif ( isset( $_POST["bxmodAuthRestoreEmail"] ) )
    {
        $result = BxmodAuth::InitRestore (
            $_POST["bxmodAuthRestoreEmail"],
            isset( $_POST["bxmodAuthRestoreCaptcha"] ) ? $_POST["bxmodAuthRestoreCaptcha"] : false,
            isset( $_POST["bxmodAuthRestoreCaptchaSid"] ) ? $_POST["bxmodAuthRestoreCaptchaSid"] : false
        );
        
        bxmodAuthResponse( $result, Array( "login" => "bxmodAuthRestoreEmail", "captcha" => "bxmodAuthRestoreCaptcha" ) );
    }
    // смена пароля
    elseif ( isset( $_POST["bxmodAuthRestoreCode"] ) )
    {
        $result = BxmodAuth::Restore (
            $_POST["bxmodAuthRestoreLogin"],
            $_POST["bxmodAuthRestoreCode"],
            $_POST["bxmodAuthRestorePassword"],
            $_POST["bxmodAuthRestoreRePassword"]
        );
        
        bxmodAuthResponse( $result, Array( "restore_code" => "bxmodAuthRestoreCode", "password" => "bxmodAuthRestorePassword" ) );
    }
    // обновление каптчи
    elseif ( isset( $_GET['reCaptcha'] ) )
    {
        $APPLICATION->RestartBuffer();
        die( $APPLICATION->CaptchaGetCode() );
    }
}

$this->IncludeComponentTemplate();
?>