<?php

use FourPaws\App\Application;
use FourPaws\Helpers\ProtectorHelper;
use FourPaws\AppBundle\AjaxController\LandingController;
use FourPaws\PersonalBundle\Exception\RuntimeException;
use FourPaws\PersonalBundle\Service\ChanceService;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->SetPageProperty('title', 'Новогодний розыгрыш от компании Четыре Лапы');
$APPLICATION->SetPageProperty('description', 'ЗЗарегистрируйтесь для участия в акции и выигрывайте призы каждую неделю!');
$APPLICATION->SetTitle('Новогодний розыгрыш от компании Четыре Лапы');

$userChance = null;
/** @var ChanceService $chaceService */
$chanceService = Application::getInstance()->getContainer()->get(ChanceService::class);
?>

<?php if ($USER->IsAuthorized()) { ?>

	<section id="participate" data-id-section-landing="participate" class="participate-ny2020">
		<div class="participate-ny2020__container" data-wrap-data-form-participate-ny2020="true">
        <?php $arUser = \CUser::GetById($USER->GetID())->Fetch(); ?>

      <?php try {
        $userChance = $chanceService->getCurrentUserChances();
      } catch (RuntimeException $e) { ?>
      <div class="title-ny2020 title-ny2020_white">Принять участие</div>

      <div class="participate-ny2020__form-info">Все поля обязательны для заполнения</div>
      <form data-form-participate-ny2020="true"
            class="participate-ny2020__form js-form-validation"
            method="post"
            action="/ajax/personal/chance/register/"
            name=""
            enctype="multipart/form-data">
        <?php $token = ProtectorHelper::generateToken(ProtectorHelper::TYPE_GRANDIN_REQUEST_ADD); ?>

        <input class="js-no-valid" type="hidden" name="<?= $token['field'] ?>" value="<?= $token['token'] ?>">

        <div class="form-group">
          <input type="isLetter" id="SURNAME_REG_CHECK_NY" name="lastname" value="<?= $arUser['LAST_NAME'] ?: '' ?>" placeholder="Фамилия">
          <div class="b-error">
            <span class="js-message"></span>
          </div>
        </div>
        <div class="form-group">
          <input type="isLetter" id="NAME_REG_CHECK_NY" name="name" value="<?= $arUser['NAME'] ?: '' ?>" placeholder="Имя">
          <div class="b-error">
            <span class="js-message"></span>
          </div>
        </div>
        <div class="form-group">
          <input type="tel" id="PHONE_REG_CHECK_NY" name="phone" value="<?= $arUser['PERSONAL_PHONE'] ?: '' ?>" placeholder="Телефон" class="js-no-valid">
          <div class="b-error">
            <span class="js-message"></span>
          </div>
        </div>
        <div class="form-group">
          <input type="emailLanding" id="EMAIL_REG_CHECK_NY" name="email" value="<?= $arUser['EMAIL'] ?: '' ?>" placeholder="E-mail">
          <div class="b-error">
            <span class="js-message"></span>
          </div>
        </div>

        <div class="read-rules">
          <input type="checkbox" id="READ_RULES_REG_CHECK_NY" name="rules" value="Y" checked>
          <label for="READ_RULES_REG_CHECK_NY"><span></span> с правилами акции ознакомлен</label>
          <div class="b-error">
            <span class="js-message"></span>
          </div>
        </div>

        <div class="participate-ny2020__btn-form">
          <button type="submit" class="participate-ny2020__btn">Отправить</button>
        </div>
      </form>
    </div>
    <?php } ?>

    <div class="response-form-participate-ny2020" data-response-form-participate-ny2020="true" style="display: <?= ($userChance === null) ? 'none' : 'block' ?>">
      <div class="response-form-participate-ny2020__title">Спасибо!</div>
      <div class="response-form-participate-ny2020__subtitle">За участие в акции</div>

      <?php if($userChance === NULL || $userChance === 0) {?>
          <div class="response-form-participate-ny2020__descr" data-descr-response-form-participate-ny2020="true" style="display: <?= ($userChance === 0) ? 'block' : 'none' ?>">
              <p>Совершайте покупки на&nbsp;500&nbsp;руб. и&nbsp;более, увеличивайте шансы выиграть:</p>
              <ul class="response-form-participate-ny2020__prizes">
                  <li>
                      <span class="img">
                          <img src="/ny2020/img/prizes1.png" alt="">
                      </span>
                      <span class="text">200 термокружек;</span>
                  </li>
                  <li>
                      <span class="img">
                          <img src="/ny2020/img/prizes2.png" alt="">
                      </span>
                      <span class="text">4 iPhone 11 Pro;</span>
                  </li>
                  <li>
                      <span class="img">
                          <img src="/ny2020/img/prizes3.png" alt="">
                      </span>
                      <span class="text">поездку в Великий Устюг!</span>
                  </li>
              </ul>
          </div>
      <?php } ?>
      <?php if($userChance === NULL || $userChance !== 0) {?>
          <div class="response-form-participate-ny2020__info" data-result-response-form-participate-ny2020="true">
              <div class="response-form-participate-ny2020__odds">Мои шансы</div>
              <div class="response-form-participate-ny2020__count" data-odds-form-participate-ny2020="true"><?= $userChance ?? 0 ?></div>
              <div class="response-form-participate-ny2020__icon"></div>
          </div>
      <?php } ?>

    </div>
  </section>
<?php } ?>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>
