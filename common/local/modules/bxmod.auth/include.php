<?
#################################
#   Developer: Lynnik Danil     #
#   Site: http://bxmod.ru       #
#   E-mail: support@bxmod.ru    #
#################################

$arClassesList = array(
    "BxmodAuth" => "classes/mysql/BxmodAuth.php",
    "BxmodAuthSocServ" => "classes/mysql/BxmodAuthSocServ.php",
    "BxmodAuthSocServFB" => "classes/mysql/BxmodAuthSocServ.php",
    "BxmodAuthSocServGG" => "classes/mysql/BxmodAuthSocServ.php",
    "BxmodAuthSocServMR" => "classes/mysql/BxmodAuthSocServ.php",
    "BxmodAuthSocServOK" => "classes/mysql/BxmodAuthSocServ.php",
    "BxmodAuthSocServVK" => "classes/mysql/BxmodAuthSocServ.php",
    "BxmodAuthSocServYA" => "classes/mysql/BxmodAuthSocServ.php",
    "BxmodAuthSocServTW" => "classes/mysql/BxmodAuthSocServ.php"
);

$moduleId = "bxmod.auth";

if (method_exists(CModule, "AddAutoloadClasses"))
{
    CModule::AddAutoloadClasses( $moduleId, $arClassesList );
}
else
{
    foreach ($arClassesList as $sClassName => $sClassFile)
    {
        require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/{$moduleId}/{$sClassFile}");
    }
}?>