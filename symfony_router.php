<?

use FourPaws\App\Application;
use Symfony\Component\HttpFoundation\Request;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include.php';

Application::handleRequest(Request::createFromGlobals());