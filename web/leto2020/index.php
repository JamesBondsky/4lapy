<?php

use Bitrix\Main\Grid\Declension;
use FourPaws\App\Application;
use FourPaws\Helpers\ProtectorHelper;
use FourPaws\PersonalBundle\Exception\RuntimeException;
use FourPaws\PersonalBundle\Service\Chance2Service;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->SetPageProperty('title', 'Выиграй путешествие на 2-их в Таиланд!');
$APPLICATION->SetPageProperty('description', '');
$APPLICATION->SetTitle('Выиграй путешествие на 2-их в Таиланд!');

$userChance = null;
/** @var ChanceService $chaceService */
$chanceService = Application::getInstance()->getContainer()->get(Chance2Service::class);

$chanceDeclension = new Declension('шанс', 'шанса', 'шансов');
?>

<?php if ($USER->IsAuthorized()) { ?>

	<section id="participate" data-id-section-landing="participate" class="participate-leto2020">
        <?php try {
            $userChance = $chanceService->getCurrentUserChances();
        } catch (RuntimeException $e) { ?>
		<div class="b-container" data-wrap-form-participate-leto2020="true">
            <?php $arUser = \CUser::GetById($USER->GetID())->Fetch(); ?>

            <div class="title-leto2020">Зарегистрируйтесь и выиграйте призы</div>

            <div class="participate-leto2020__inner-wrap">
                <div class="participate-leto2020__inner">
                    <div class="participate-leto2020__message">Все поля обязательны для заполнения</div>
                    <form data-form-participate-leto2020="true"
                          class="participate-leto2020__form js-form-validation"
                          method="post"
                          action="/ajax/personal/chance/register-2/"
                          name=""
                          enctype="multipart/form-data">
                        <?php $token = ProtectorHelper::generateToken(ProtectorHelper::TYPE_GRANDIN_REQUEST_ADD); ?>

                        <input class="js-no-valid" type="hidden" name="<?= $token['field'] ?>" value="<?= $token['token'] ?>">

                        <div class="form-group">
                            <input type="isLetter" id="SURNAME_REG_CHECK_LETO" name="lastname" value="<?= $arUser['LAST_NAME'] ?: '' ?>" placeholder="Фамилия">
                            <div class="b-error">
                                <span class="js-message"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <input type="isLetter" id="NAME_REG_CHECK_LETO" name="name" value="<?= $arUser['NAME'] ?: '' ?>" placeholder="Имя">
                            <div class="b-error">
                                <span class="js-message"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <input type="tel" id="PHONE_REG_CHECK_LETO" name="phone" value="<?= $arUser['PERSONAL_PHONE'] ?: '' ?>" placeholder="Телефон" class="js-no-valid">
                            <div class="b-error">
                                <span class="js-message"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <input type="emailLanding" id="EMAIL_REG_CHECK_LETO" name="email" value="<?= $arUser['EMAIL'] ?: '' ?>" placeholder="E-mail">
                            <div class="b-error">
                                <span class="js-message"></span>
                            </div>
                        </div>

                        <div class="read-rules">
                            <input type="checkbox" id="READ_RULES_REG_CHECK_LETO" name="rules" value="Y" checked>
                            <label for="READ_RULES_REG_CHECK_LETO"><span></span> с правилами акции ознакомлен</label>
                            <div class="b-error">
                                <span class="js-message"></span>
                            </div>
                        </div>

                        <div class="participate-leto2020__btn-form">
                            <button type="submit" class="btn-leto2020">Принять участие</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php } ?>

        <?php if ($userChance === null || $userChance === 0) { ?>
        <div class="b-container" data-response-form-participate-leto2020="true" style="display: <?= ($userChance === 0) ? 'block' : 'none' ?>">
            <div class="title-leto2020">Спасибо за&nbsp;регистрацию в&nbsp;розыгрыше!</div>

            <div class="participate-leto2020__inner-wrap">
                <div class="participate-leto2020__inner">
                    <div class="response-form-participate-leto2020">
                        <span class="response-form-participate-leto2020__title">
                            Сейчас у Вас накоплено
                            <nobr>
                                <span class="response-form-participate-leto2020__count" data-odds-form-participate-leto2020="true">0</span>
                                шансов
                            </nobr>
                        </span>
                        <div class="response-form-participate-leto2020__footnote">Шансы будут начислены в&nbsp;течение <nobr>2-х</nobr> дней.</div>

                        <div class="response-form-participate-leto2020__subtitle">
                            Совершайте больше покупок от&nbsp;500&nbsp;руб и&nbsp;увеличивайте шансы выиграть призы!
                        </div>

                        <ul class="response-form-participate-leto2020__prizes">
                            <li>
                                <span class="img">
                                    <img src="/leto2020/img/power-bank_icon.png" alt="">
                                </span>
                                <span class="text"><b>200</b> power banks</span>
                            </li>
                            <li>
                                <span class="img">
                                    <img src="/leto2020/img/phone_icon.png" alt="">
                                </span>
                                <span class="text"><b>20</b> смартфонов</span>
                            </li>
                            <li>
                                <span class="img">
                                    <img src="/leto2020/img/tickets_icon.png" alt="">
                                </span>
                                <span class="text"><b>Путешествие</b> на&nbsp;<nobr>2-х</nobr> в&nbsp;Лето</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php } else { ?>
          <div class="b-container">
            <div class="title-leto2020">Спасибо за&nbsp;регистрацию в&nbsp;розыгрыше!</div>
            <div class="participate-leto2020__inner-wrap">
              <div class="participate-leto2020__inner">
                <div class="response-form-participate-leto2020">
                        <span class="response-form-participate-leto2020__title">
                            Сейчас у Вас накоплено
                            <nobr>
                                <span class="response-form-participate-leto2020__count"><?= $userChance ?></span>
                                <?= $chanceDeclension->get($userChance) ?>
                            </nobr>
                        </span>
                  <div class="response-form-participate-leto2020__subtitle">
                    Совершайте больше покупок от&nbsp;500&nbsp;руб и&nbsp;увеличивайте шансы выиграть призы!
                  </div>

                  <ul class="response-form-participate-leto2020__prizes">
                    <li>
                                <span class="img">
                                    <img src="/leto2020/img/power-bank_icon.png" alt="">
                                </span>
                      <span class="text"><b>200</b> power banks</span>
                    </li>
                    <li>
                                <span class="img">
                                    <img src="/leto2020/img/phone_icon.png" alt="">
                                </span>
                      <span class="text"><b>20</b> смартфонов</span>
                    </li>
                    <li>
                                <span class="img">
                                    <img src="/leto2020/img/tickets_icon.png" alt="">
                                </span>
                      <span class="text"><b>Путешествие</b> на&nbsp;<nobr>2-х</nobr> в&nbsp;Лето</span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        <?php } ?>
    </section>

<?php } ?>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>
