<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Sale\Order;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\ReCaptchaBundle\Service\ReCaptchaService;
use FourPaws\UserBundle\Entity\User;

/**
 * @var CMain $APPLICATION
 * @var array $arParams
 * @var User  $user
 * @var Order $order
 */

$user  = $arParams['ADDITIONAL_DATA']['USER'];
$order = $arParams['ADDITIONAL_DATA']['ORDER'];

?>


<form class="b-interview js-interview"
      name="<?= $arResult['arForm']['SID'] ?>" data-url="/ajax/form/add/order_interview/<?= $order->getId() ?>/"
      method="post"
      enctype="multipart/form-data">
    <?= bitrix_sessid_post() ?>
    <input name="WEB_FORM_ID" value="<?= $arResult['arForm']['ID'] ?>" type="hidden">
    <h1 class="b-title b-title--h1 b-interview__title"><?= $user->getName(); ?>, помогите нам стать лучше!</h1>
    <div class="b-interview__wrap">
        <div class="b-interview__order">
            <?= str_replace('#ORDER_NUM#', $order->getFields()->get('ACCOUNT_NUMBER'), $arResult['FORM_DESCRIPTION']) ?>
        </div>
        <div class="b-interview__content">
            <?php
            foreach ($arResult['QUESTIONS'] as $fieldSid => $question) {
                if ($question['STRUCTURE'][0]['FIELD_TYPE'] === 'hidden') { ?>
                    <input type="hidden" name="form_hidden_<?= $question['STRUCTURE'][0]['ID'] ?>" value=""
                           data-code="<?= $fieldSid ?>">
                <?
                }
            }
            foreach ($arResult['FIELD_SET'] as $fieldSet) {
                $rate    = $fieldSet['RATE'];
                $comment = $fieldSet['COMMENT'] ?>
                <fieldset class="b-interview__question">
                    <div class="b-interview__question-title"><?= $comment['CAPTION'] ?></div>
                    <div class="b-interview__question-text"><?= $comment['PRINT_MESSAGE'] ?></div>
                    <div class="b-interview__question-label">Оценка</div>
                    <div class="b-star-rating b-interview__question-rating js-rating">
                        <div class="b-star-rating__wrap">
                            <?php
                            $cnt = count($rate['STRUCTURE']);
                            for ($i = $cnt; $i > 0; $i--) {
                                $elem    = $rate['STRUCTURE'][$i - 1];
                                $inputID = sprintf('%s - %s', $rate['INPUT_NAME'], $i) ?>
                                <input class="js-rating-input" type="radio" id="<?= $inputID ?>"
                                       name="<?= $rate['INPUT_NAME'] ?>" value="<?= $elem['ID'] ?>"
                                       data-rate="<?= $i ?>">
                                <label for="<?= $inputID ?>"></label>
                            <?
                            } ?>
                        </div>
                        <div class="b-star-rating__label">Ваша оценка:</div>
                        <div class="b-star-rating__field js-rating-field">0</div>
                    </div>
                    <div class="b-interview__question-label">Комментарий</div>
                    <div class="b-input">
                        <textarea class="b-input__input-field b-interview__question-textarea"
                                  name="<?= $comment['INPUT_NAME'] ?>"></textarea>
                    </div>
                </fieldset>
                <?php
            }
            if ($arResult['isUseCaptcha']) { ?>
                <div class="b-feedback-page__capcha">
                    <?php try {
                        echo App::getInstance()->getContainer()->get(ReCaptchaService::class)->getCaptcha();
                    } catch (ApplicationCreateException $e) {
                        /** ошибка - капчу не вывести */
                    } ?>
                </div>
            <?php } ?>

            <button class="b-button b-button--social b-button--green b-interview__submit"
                    type="submit"><?= $arResult['arForm']['BUTTON'] ?></button>
        </div>
    </div>
</form>