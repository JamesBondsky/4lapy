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
                        <input type="text" placeholder="ИМЯ" name="name" data-field-form-festival="true" <?= $isAuthorized ? 'value="' . $currentUser->getName() . '"' : '' ?> required autocomplete="off" />
                    </div>
                </div>
                <div class="form-group">
                    <div class="field">
                        <span class="required">*</span>
                        <input type="text" placeholder="ФАМИЛИЯ" name="surname" data-field-form-festival="true" <?= $isAuthorized ? 'value="' . $currentUser->getLastName() . '"' : '' ?> required autocomplete="off" />
                    </div>
                </div>
                <div class="form-group">
                    <div class="field">
                        <span class="required">*</span>
                        <input type="email" placeholder="EMAIL" name="email" data-field-form-festival="true" <?= $isAuthorized ? 'value="' . $currentUser->getEmail() . '"' : '' ?> required autocomplete="off" />
                    </div>
                </div>
                <div class="form-group">
                    <div class="field">
                        <span class="required">*</span>
                        <input type="phone" placeholder="ТЕЛЕФОН" name="phone" data-field-form-festival="true" <?= $isAuthorized ? 'value="' . $currentUser->getPersonalPhone() . '"' : '' ?> class="isPhone" required autocomplete="off" data-mask-phone />
                    </div>
                </div>
                <div class="form-group">
                    <div class="field field_checkbox">
                        <input type="checkbox" id="agree" name="rules" data-checkbox-form-festival="agree-personal-data-processing" required /> <label for="agree">я даю своё согласие на обработку персональных данных <span class="required">*</span></label>
                    </div>
                </div>
                <div class="form-group_submit">
                    <button class="join_btn">я пойду!</button>
                </div>
            </form>
        </div>
    </div>
</section>