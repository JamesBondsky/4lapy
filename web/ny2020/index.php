<?php

use FourPaws\App\Application;
use FourPaws\Helpers\ProtectorHelper;
use FourPaws\AppBundle\AjaxController\LandingController;
use FourPaws\PersonalBundle\Exception\RuntimeException;
use FourPaws\PersonalBundle\Service\ChanceService;


require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->SetPageProperty('title', 'Путешествие на 4-х человек на родину Деда Мороза!');
$APPLICATION->SetPageProperty('description', '');
$APPLICATION->SetTitle('Путешествие на 4-х человек на родину Деда Мороза!');

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
        <input class="js-no-valid" type="hidden" name="landingType" value="<?= LandingController::$mealfeelLanding ?>">

        <div class="form-group">
          <input type="text" id="SURNAME_REG_CHECK_NY" class="js-small-input" name="lastname" value="<?= $arUser['LAST_NAME'] ?: '' ?>" placeholder="Фамилия" <?= (!empty($arUser['LAST_NAME'])) ? 'disabled="disabled"' : '' ?>>
          <div class="b-error">
            <span class="js-message"></span>
          </div>
        </div>
        <div class="form-group">
          <input type="text" id="NAME_REG_CHECK_NY" class="js-small-input" name="name" value="<?= $arUser['NAME'] ?: '' ?>" placeholder="Имя" <?= (!empty($arUser['NAME'])) ? 'disabled="disabled"' : '' ?>>
          <div class="b-error">
            <span class="js-message"></span>
          </div>
        </div>
        <div class="form-group">
          <input type="tel" id="PHONE_REG_CHECK_NY" name="phone" value="<?= $arUser['PERSONAL_PHONE'] ?: '' ?>" placeholder="Телефон" disabled="disabled" class="js-no-valid">
          <div class="b-error">
            <span class="js-message"></span>
          </div>
        </div>
        <div class="form-group">
          <input type="emailLanding" id="EMAIL_REG_CHECK_NY" name="email" value="<?= $arUser['EMAIL'] ?: '' ?>" placeholder="E-mail" <?= (!empty($arUser['EMAIL'])) ? 'disabled="disabled"' : '' ?>>
          <div class="b-error">
            <span class="js-message"></span>
          </div>
        </div>

        <div class="read-rules">
          <input type="checkbox" id="READ_RULES_REG_CHECK_NY" name="rules" value="Y" checked>
          <label for="READ_RULES_REG_CHECK_NY"><span></span> <a href="/" target="_blank">с правилами акции ознакомлен</a></label>
          <div class="b-error">
            <span class="js-message"></span>
          </div>
        </div>

        <div class="participate-ny2020__btn-form">
          <button type="submit" class="participate-ny2020__btn">Отправить</button>
        </div>
      </form>

      <div class="participate-ny2020__primary">
        <p>Личные данные, вводимые при регистрации в&nbsp;акции, должны совпадать с&nbsp;личными данными, к&nbsp;которым привязана бонусная карта Четыре лапы, также используемая для&nbsp;регистрации в&nbsp;акции.</p>
        <p>Участники, у&nbsp;которых указанная информация не&nbsp;совпадает, автоматически выбывают из&nbsp;общего списка зарегистрированных участников для начисления бонусов и&nbsp;розыгрыша призов.</p>
      </div>
    </div>
    <?php } ?>

    <div class="response-form-participate-ny2020" data-response-form-participate-ny2020="true" style="display: <?= ($userChance === null) ? 'none' : 'block' ?>">
      <div class="response-form-participate-ny2020__title">Спасибо!</div>
      <div class="response-form-participate-ny2020__subtitle">За участие в акции</div>
      <div class="response-form-participate-ny2020__info">
        <div class="response-form-participate-ny2020__odds">Мои шансы</div>
        <div class="response-form-participate-ny2020__count" data-odds-form-participate-ny2020="true"><?= $userChance ?? 0 ?></div>
        <div class="response-form-participate-ny2020__icon"></div>
      </div>
    </div>
  </section>
<?php } ?>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>
