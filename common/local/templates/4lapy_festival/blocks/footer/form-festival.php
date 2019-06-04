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

                <div class="form-group">
                    <div class="field">
                        <span class="required">*</span>
                        <input type="text" placeholder="ИМЯ" name="name" onblur="ga('send', 'pageview', '/virtualpage/name')" <?= $isAuthorized ? 'value="' . $currentUser->getName() . '"' : '' ?> required autocomplete="off" />
                    </div>
                </div>
                <div class="form-group">
                    <div class="field">
                        <span class="required">*</span>
                        <input type="text" placeholder="ФАМИЛИЯ" name="surname" onblur="ga('send', 'pageview', '/virtualpage/surname')" <?= $isAuthorized ? 'value="' . $currentUser->getLastName() . '"' : '' ?> required autocomplete="off" />
                    </div>
                </div>
                <div class="form-group">
                    <div class="field">
                        <span class="required">*</span>
                        <input type="email" placeholder="EMAIL" name="email" onblur="ga('send', 'pageview', '/virtualpage/email')" <?= $isAuthorized ? 'value="' . $currentUser->getEmail() . '"' : '' ?> required autocomplete="off" />
                    </div>
                </div>
                <div class="form-group">
                    <div class="field">
                        <span class="required">*</span>
                        <input type="phone" placeholder="ТЕЛЕФОН" name="phone" onblur="ga('send', 'pageview', '/virtualpage/phone')" <?= $isAuthorized ? 'value="' . $currentUser->getPersonalPhone() . '"' : '' ?> class="isPhone" required autocomplete="off" data-mask-phone />
                    </div>
                </div>
                <div class="form-group">
                    <div class="field field_checkbox">
                        <input type="checkbox" id="agree" name="rules" onchange="ga('send', 'pageview', '/virtualpage/agree')" required checked="checked" /> <label for="agree">я даю своё согласие на обработку персональных данных <span class="required">*</span></label>
                    </div>
                </div>
                <div class="form-group_submit">
                    <button class="join_btn" onsubmit="ga('send', 'event', 'fest_fillform', 'submit')">я пойду!</button>
                </div>
            </form>
        </div>
    </div>
    <div class="popwrap__link">
        <a href="javascript:void(0);" data-open-next-popup="true" data-popup-id="forgot-passport-number">Уже регистрировался, но забыл номер паспорта?</a>
    </div>
</section>