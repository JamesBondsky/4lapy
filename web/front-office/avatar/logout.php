<?php
/**
 * Выход из режима аватара
 */
define('NOT_CHECK_PERMISSIONS', true);
define('STOP_STATISTICS', true);
define('DisableEventsCheck', true);
define('NO_AGENT_CHECK', true);
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

try {
    \FourPaws\App\Application::getInstance()->getContainer()->get(\FourPaws\UserBundle\Service\CurrentUserProviderInterface::class)->avatarLogout();
} catch (\Exception $exception) {}
LocalRedirect($GLOBALS['APPLICATION']->GetCurDir());

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
