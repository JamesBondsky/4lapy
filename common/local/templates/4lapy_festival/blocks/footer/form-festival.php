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
            <p><b>До фестиваля «Четыре лапы» осталось совсем чуть-чуть. Заполни короткую форму регистрации, и мы отправим на электронную почту приглашение с персональным кодом участника.</b></p>
            <p>Это отличная возможность:</p>
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
                <input type="text" placeholder="ИМЯ" name="name" <?= $isAuthorized ? 'value="' . $currentUser->getName() . '"' : '' ?> />
                <input type="text" placeholder="ФАМИЛИЯ" name="surname" <?= $isAuthorized ? 'value="' . $currentUser->getLastName() . '"' : '' ?> />
                <input type="email" placeholder="EMAIL" name="email" <?= $isAuthorized ? 'value="' . $currentUser->getEmail() . '"' : '' ?> />
                <input type="phone" placeholder="ТЕЛЕФОН" name="phone" <?= $isAuthorized ? 'value="' . $currentUser->getPersonalPhone() . '"' : '' ?> />

                <input type="checkbox" id="agree" name="rules" /> <label for="agree">Я принимаю условия пользовательского соглашения, правил, политики обработки персональных данных, даю согласие на обработку персональных данных.</label>
                <button class="join_btn">я пойду!</button>
            </form>
        </div>
    </div>
</section>