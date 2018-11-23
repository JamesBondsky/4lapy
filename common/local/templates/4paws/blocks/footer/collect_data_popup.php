<?php

/** @var MainTemplate $template */
/** @var CMain $APPLICATION */
/** @noinspection PhpUnhandledExceptionInspection */

// достаточно большое количество условий для корректной реализации, будем пробовать.
// Смысл заключается в том, что показывается всего одна модалка
// TODO: сделать чек на данные, которые уже указаны.

global $USER;

if($USER->IsAuthorized()) {
    // срезаем пути - любой шаг заказа + баскет.

    if(!$template->isOrderPage() && !$template->isOrderInterviewPage() &&  !$template->isOrderDeliveryPage() && !$template->isPaymentPage() && !$template->isBasket())
    {
        $modal_counts = CUser::GetByID( $USER->GetID() )->Fetch()['UF_MODALS_CNTS'];

        if($modal_counts !== '3 3 3') { // модалки не по 3 штуки

            $modal_counts = explode(' ', $modal_counts);

            if($USER->GetParam('data_collect') == false){ // модалку в сессии еще не показали
                //$USER->SetParam('data_collect', true);
            }
        }
    }
}

