<?php

/** @var MainTemplate $template */
/** @var CMain $APPLICATION */
/** @noinspection PhpUnhandledExceptionInspection */

global $USER;
$modal_number = NULL;
$user_class = new \CUser;

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application as App;
use FourPaws\PersonalBundle\Service\PersonalOffersService;

if($USER->IsAuthorized()) {
    // срезаем пути - любой шаг заказа + баскет.
    if(!$template->isOrderPage() && !$template->isOrderInterviewPage() &&  !$template->isOrderDeliveryPage() && !$template->isPaymentPage() && !$template->isBasket())
    {
        $modal_counts_txt = $modal_counts_txt ?? CUser::GetByID( $USER->GetID() )->Fetch()['UF_MODALS_CNTS'];
        $modal_counts = $modal_counts ?? explode(' ', $modal_counts_txt);
        if($modal_counts != '3 3 3 3') // модалки не по 3 штуки //сравнение массива и строки - равносильно if (true)
        {
            /** @var PersonalOffersService $personalOffersService */
            $personalOffersService = $personalOffersService ?? App::getInstance()->getContainer()->get('personal_offers.service');
            $userId = $userId ?? $USER->GetID();
            $userPersonalOffers = $userPersonalOffers ?? $personalOffersService->getActiveUserCoupons($userId, true, true);

            /** @var ArrayCollection $coupons */
            $coupons = $coupons ?? $userPersonalOffers['coupons'];

            if($USER->GetParam('data_collect') !== 'Y') // модалку в сессии еще не показали
            {
                if ($coupons->isEmpty() || $modal_counts[3] > 2) {
	                $user_data = CUser::GetByID($userId)->Fetch();
	                if($user_data['UF_SESSION_CNTS'] % 3 == 1) // Каждая 3-я сессия
	                {
	                    if($user_data['NAME'] && $user_data['PERSONAL_PHONE'] || $modal_counts[0] > 2)
	                    {
	                        if($user_data['LAST_NAME'] && $user_data['EMAIL'] || $modal_counts[1] > 2)
	                        {
	                            $container = App::getInstance()->getContainer();
	                            $pets = $container->get('pet.service');

	                            if(count($pets->getCurUserPets()) || $modal_counts[2] > 2) {
	                                $user_class->Update($USER->GetID(), ['UF_MODALS_CNTS' => '3 3 3 3']);
	                            }

	                            else{
	                                $modal_number = 3;
	                            }
	                        }
	                        else {
	                            $modal_number = 2;
	                        }
	                    }
	                    else {
	                        $modal_number = 1;
	                    }
	                }
	            }
	            else {
	                $modal_number = 4;
	            }
            }
        }
    }
} ?>
<? if($modal_number == 1) { ?>
    <? $APPLICATION->IncludeComponent('fourpaws:personal.profile', 'popupCollectorName', [], null, ['HIDE_ICONS' => 'Y']); ?>
    <a class="js-add-query js-open-popup js-open-popup--account-tab" style="display: none;" id="data_collect" data-popup-id="collector-name"></a>
<? } ?>
<? if($modal_number == 2) { ?>
    <? $APPLICATION->IncludeComponent('fourpaws:personal.profile', 'popupCollectorName', ['TYPE' => 'more'], null, ['HIDE_ICONS' => 'Y']); ?>
    <a class="js-add-query js-open-popup js-open-popup--account-tab" style="display: none;" id="data_collect" data-popup-id="collector-name"></a>
<? } ?>
<? if($modal_number == 3) { ?>
    <? $APPLICATION->IncludeComponent('fourpaws:personal.pets', 'popup_collector', [], null, ['HIDE_ICONS' => 'Y']); ?>
<? } ?>

<? if($modal_number) { ?>
    <script>
        // заглушка для вызова формы - вынесено во одно место, чтобы было удобнее исправлять и не менять шаблоны.
        $(document).ready(function () {
            if(parseInt(getCookie('modal_timer'), 10) >= <?= $modal_number === 4 ? 2 : 9?>) document.cookie = "modal_timer=0; path=/;";
            function getCookie(name) {
                var matches = document.cookie.match(new RegExp(
                    "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
                ));
                return matches ? decodeURIComponent(matches[1]) : 0;
            }
            function serveModal(timer){
                clearInterval(timer);
                $('#data_collect').trigger('click');

                if(<?= $modal_number === 4 ? '1' : '0'?>) {
                    Coupon.showPersonPopup();
                }

                $('form.collector-form input').each(function () {
                    if($(this).val().length > 2) $(this).attr('readonly', "");
                });

                let modals_counter = [<?=$modal_counts[0]?>, <?=$modal_counts[1]?>, <?=$modal_counts[2]?>, <?=$modal_counts[3] ?? 0 ?>];
                modals_counter[<?=$modal_number-1?>]++;

                // отправим новые счетчики модалок, только после показа.
                $.ajax({
                    method: "POST",
                    url: "/ajax/personal/profile/disableModalPersist/",
                    data: { modals: modals_counter }
                });
            }
            let timer = setInterval(function () {
                let time = parseInt(getCookie('modal_timer'));
                if($('.b-popup-wrapper').hasClass('active') !== true) time++;
                document.cookie = "modal_timer="+time+"; path=/;";
                if(parseInt(getCookie('modal_timer'), 10) === <?= $modal_number === 4 ? 2 : 9?>) serveModal(timer);
            }, 5000);
        });
    </script>
<? }?>