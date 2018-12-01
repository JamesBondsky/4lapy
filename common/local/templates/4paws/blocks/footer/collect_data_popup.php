<?php

/** @var MainTemplate $template */
/** @var CMain $APPLICATION */
/** @noinspection PhpUnhandledExceptionInspection */

global $USER;
$modal_number = NULL;

if($USER->IsAuthorized()) {
    // срезаем пути - любой шаг заказа + баскет.
    if(!$template->isOrderPage() && !$template->isOrderInterviewPage() &&  !$template->isOrderDeliveryPage() && !$template->isPaymentPage() && !$template->isBasket())
    {
        $modal_counts = CUser::GetByID( $USER->GetID() )->Fetch()['UF_MODALS_CNTS'];
        if($modal_counts != '3 3 3') // модалки не по 3 штуки
        {
            $modal_counts = explode(' ', $modal_counts);
            if($USER->GetParam('data_collect') != 'Y') // модалку в сессии еще не показали
            {
                $user_data = CUser::GetByID( $USER->GetID() )->Fetch();
                if($user_data['UF_SESSION_CNTS'] % 3 == 0) // Каждая 3-я сессия
                {
                    if($modal_counts[0] == $modal_counts[1] && $modal_counts[1] == $modal_counts[2] && !$user_data['NAME'] &&
                        !$user_data['PERSONAL_PHONE']) $modal_number = 1;

                    if($modal_counts[0] > $modal_counts[1] && $modal_counts[1] == $modal_counts[2] && !$user_data['PERSONAL_PHONE'] &&
                        !$user_data['NAME'] && !$user_data['LAST_NAME'] && !$user_data['EMAIL']) $modal_number = 2;

                    $pets = new FourPawsPersonalCabinetPetsComponent();
                    if($modal_counts[0] == $modal_counts[1] && $modal_counts[1] > $modal_counts[2] && count($pets->hasOwnPets())) $modal_number = 3;
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
<? $APPLICATION->IncludeComponent('fourpaws:personal.pets', 'popup_collector', [], null, ['HIDE_ICONS' => 'Y']); ?>
<? if(1){ //$modal_number?>
    <script>
        // заглушка для вызова формы - вынесено во одно место, чтобы было удобнее исправлять и не менять шаблоны.
        $(document).ready(function () {
            function getCookie(name) {
                var matches = document.cookie.match(new RegExp(
                    "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
                ));
                return matches ? decodeURIComponent(matches[1]) : 0;
            }
            function serveModal(timer){
                clearInterval(timer);
                $('#data_collect').trigger('click');

                let modals_counter = [<?=$modal_counts[0]?>, <?=$modal_counts[1]?>, <?=$modal_counts[2]?>];
                modals_counter[<?=$modal_number-1?>]++;

                // отправим новые счетчики модалок, только после показа.
                $.ajax({
                    method: "POST",
                    url: "/ajax/personal/profile/disableModalPersist/",
                    data: { modals: modals_counter.join(' ') }
                });
            }
            let timer = setInterval(function () {
                let time = parseInt(getCookie('modal_timer'));
                time++;
                document.cookie = "modal_timer="+time+"; path=/;";
                if(parseInt(getCookie('modal_timer'), 10) === 9) serveModal(timer);
            }, 5000);
        });
    </script>
<? }?>