<?

use FourPaws\App\Application;
use FourPaws\AppBundle\AjaxController\LandingController;
use FourPaws\Helpers\ProtectorHelper;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;

$container = Application::getInstance()->getContainer();
/** @var \FourPaws\UserBundle\Service\UserService $userService */
$userService = $container->get(CurrentUserProviderInterface::class);
$isAuthorized = $userService->isAuthorized();
if ($isAuthorized)
{
	$currentUser = $userService->getCurrentUser();
}
?>
<section class="popwrap2 js-popup-section" data-popup="form-festival">
    <div class="btn-close btn-close--form js-close-popup"></div>
    <div class="formwrap">
	    <div class="formitem"><p>Мероприятие завершено</p></div>
</section>