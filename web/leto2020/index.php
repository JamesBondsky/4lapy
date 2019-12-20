<?php

use FourPaws\App\Application;
use FourPaws\Helpers\ProtectorHelper;
use FourPaws\AppBundle\AjaxController\LandingController;
use FourPaws\PersonalBundle\Exception\RuntimeException;
use FourPaws\PersonalBundle\Service\ChanceService;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->SetPageProperty('title', '');
$APPLICATION->SetPageProperty('description', '');
$APPLICATION->SetTitle('');
?>

<?php if ($USER->IsAuthorized()) { ?>

	<section id="participate" data-id-section-landing="participate" class="participate-leto2020">
		<div class="b-container" data-wrap-form-participate-leto2020="true">
            <?php $arUser = \CUser::GetById($USER->GetID())->Fetch(); ?>

            <div class="title-leto2020">Зарегестрируйтесь и&nbsp;выйграйте призы</div>

            <div class="participate-leto2020__inner-wrap">
                <div class="participate-leto2020__inner">
                    <div class="participate-leto2020__message">Все поля обязательны для заполнения</div>
                    <form data-form-participate-leto2020="true"
                          class="participate-leto2020__form js-form-validation"
                          method="post"
                          action="/ajax/personal/chance/register/"
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
                            <button type="submit" class="participate-leto2020__btn">Принять участие</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="b-container" data-response-form-participate-leto2020="true" style="display: none;">
            <div class="title-leto2020">Спасибо за&nbsp;регистрацию в&nbsp;розыгрыше!</div>

            <div class="participate-leto2020__inner-wrap">
                <div class="participate-leto2020__inner">
                    <div class="response-form-participate-leto2020">
                        <span class="response-form-participate-leto2020__title">
                            Сейчас у Вас накоплено
                            <span class="response-form-participate-leto2020__count" data-odds-form-participate-leto2020="true">0</span>
                            шансов
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
                                <span class="text"><b>Путешествие</b> на&nbsp;<nobr>2-х</nobr> в&nbsp;Тайланд</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php } ?>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>
