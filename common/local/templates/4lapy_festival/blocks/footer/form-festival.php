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
<section class="popwrap js-popup-section" data-popup="form-festival">
    <div class="btn-close btn-close--form js-close-popup"></div>
    <div class="formwrap">
        <div class="formitem">
            <p><b>Заполни короткую форму регистрации и&nbsp;получи на&nbsp;электронную почту приглашение с&nbsp;персональным кодом участника, это отличная возможность:</b></p>
            <ul>
                <li>Стать участником квеста и выиграть поездку в Париж!</li>
                <li>Принять участие в розыгрыше более 100 призов</li>
                <li>Воспользоваться праздничной скидкой 10% </li>
                <li>Получить Паспорт участника на VIP-стойке регистрации</li>
            </ul>
        </div>
        <div class="formitem">
            <form action="/ajax/landing/festival/user/add/" method="post">
                <? $token = ProtectorHelper::generateToken(ProtectorHelper::TYPE_FESTIVAL_REQUEST_ADD); ?>
	            <input class="js-no-valid" type="hidden" name="<?=$token['field']?>" value="<?=$token['token']?>">
	            <input class="js-no-valid" type="hidden" name="landingType" value="<?= LandingController::$festivalLanding ?>">
                <input type="text" placeholder="ИМЯ" name="name" onblur="ga('send', 'pageview', '/virtualpage/name')" <?= $isAuthorized ? 'value="' . $currentUser->getName() . '"' : '' ?> />
                <input type="text" placeholder="ФАМИЛИЯ" name="surname" onblur="ga('send', 'pageview', '/virtualpage/surname')" <?= $isAuthorized ? 'value="' . $currentUser->getLastName() . '"' : '' ?> />
                <input type="email" placeholder="EMAIL" name="email" onblur="ga('send', 'pageview', '/virtualpage/email')" <?= $isAuthorized ? 'value="' . $currentUser->getEmail() . '"' : '' ?> />
                <input type="phone" placeholder="ТЕЛЕФОН" name="phone" onblur="ga('send', 'pageview', '/virtualpage/phone')" <?= $isAuthorized ? 'value="' . $currentUser->getPersonalPhone() . '"' : '' ?> />

                <input type="checkbox" id="agree" name="rules" onchange="ga('send', 'pageview', '/virtualpage/agree')" data-checkbox-form-festival="agree-personal-data-processing" /> <label for="agree">я даю своё согласие на обработку персональных данных</label>
                <button class="join_btn" onsubmit="ga('send', 'event', 'fest_go', 'submit')">я пойду!</button>
            </form>
        </div>
    </div>
</section>