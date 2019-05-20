<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if ( !CModule::IncludeModule("bxmod.auth") ) return false;

$options = BxmodAuth::GetOptions();

$arSocServ = Array(
    "ya" => "BxmodAuthSocServYA",
    "vk" => "BxmodAuthSocServVK",
    "ok" => "BxmodAuthSocServOK",
    "gg" => "BxmodAuthSocServGG",
    "mr" => "BxmodAuthSocServMR",
    "fb" => "BxmodAuthSocServFB",
    "tw" => "BxmodAuthSocServTW"
);

if ( isset( $_GET["type"] ) && isset( $arSocServ[ $_GET["type"] ] ) )
{
    $socServ = new $arSocServ[ $_GET["type"] ];
    $loginResult = $socServ->SocLogin();
    // ≈сли авторизаци€ не удалась, то редирект на fail_url
    if ( !$loginResult ) {
        Header("Location: {$options["fail_url"]}");
        exit;
    }
}
elseif ( isset( $_GET["twitterAuth"] ) )
{
    
    BxmodAuthSocServTW::GoToAuth();
}
else
{
    Header("Location: {$options["fail_url"]}");
    exit;
}
?>