<?php

/** @var MainTemplate $template */
/** @var CMain $APPLICATION */
/** @noinspection PhpUnhandledExceptionInspection */

// достаточно большое количество условий для корректной реализации, будем пробовать.
// Смысл заключается в том, что показывается всего одна модалка
// TODO: сделать чек на данные, которые уже указаны. P.S в задании не было.

global $USER;
$modal_number = NULL;

if($USER->IsAuthorized()) {
    // срезаем пути - любой шаг заказа + баскет.
    if(!$template->isOrderPage() && !$template->isOrderInterviewPage() &&  !$template->isOrderDeliveryPage() && !$template->isPaymentPage() && !$template->isBasket())
    {
        $modal_counts = CUser::GetByID( $USER->GetID() )->Fetch()['UF_MODALS_CNTS'];
        if($modal_counts !== '3 3 3') { // модалки не по 3 штуки
            $modal_counts = explode(' ', $modal_counts);
            if($USER->GetParam('data_collect') == false){ // модалку в сессии еще не показали
                $user_data = CUser::GetByID( $USER->GetID() )->Fetch();
                if($user_data['UF_SESSION_CNTS'] % 3 == 0){ // Каждая 3-я сессия
                    // все равны, значит 3-ю показывали уже. => покажем 1-ю
                    if($modal_counts[0] == $modal_counts[1] && $modal_counts[1] == $modal_counts[2] && !$user_data['PERSONAL_PHONE'] &&
                        !$user_data['NAME']) $modal_number = 1;
                    // 1-я > 2-й, а 2-я == 3-й, значит 1-ю показали уже, покажем 2-ю.
                    if($modal_counts[0] > $modal_counts[1] && $modal_counts[1] == $modal_counts[2] && !$user_data['PERSONAL_PHONE'] &&
                        !$user_data['NAME'] && !$user_data['SURNAME'] && !$user_data['EMAIL']) $modal_number = 2;
                    // 1-я == 2-й, 2-я > 3-й, значит нужно показать 3-ю.
                    if($modal_counts[0] == $modal_counts[1] && $modal_counts[1] > $modal_counts[2]) $modal_number = 3;
                }
            }
        }
    }
} ?>
<? //if($modal_number == 1) { ?>
    <? $APPLICATION->IncludeComponent('fourpaws:personal.profile', 'popupCollectorName', [], null, ['HIDE_ICONS' => 'Y']); ?>
    <a class="js-add-query js-open-popup js-open-popup--account-tab" style="display: none;" id="data_collect" data-popup-id="collector-name" data-url="/ajax/personal/pets/add/"></a>
<? //}?>
<? //if($modal_number == 2) { ?>

<? //}?>
<? //if($modal_number == 3) { ?>
    <? //$APPLICATION->IncludeComponent('fourpaws:personal.pets', 'popup', ['COLLECTOR' => 'Y'], null, ['HIDE_ICONS' => 'Y']); ?>
    <!--<a class="js-add-query js-open-popup js-open-popup--account-tab" style="display: none;" id="data_collect" data-popup-id="edit-popup-pet" data-url="/ajax/personal/pets/add/"></a>-->
<? //}?>
<? if(1){ //$modal_timer?>
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

                let data = [<?=$modal_counts[0]?>, <?=$modal_counts[1]?>, <?=$modal_counts[2]?>];
                data[<?=$modal_number-1?>]++;

                //data.join(' ');
                // TODO: написать запрос и перезаписать данные модалки.
                // $USER->SetParam('data_collect', true); - в контроллер, перепишем куки, чтоб больше не лезло.
            }
            let timer = setInterval(function () {

                let time = parseInt(getCookie('modal_timer'));
                time++;
                document.cookie = "modal_timer="+time;
                console.log(getCookie('modal_timer'));

                if(parseInt(getCookie('modal_timer'), 10) === 9) serveModal(timer);
            }, 5000);
        });
    </script>
<? }?>