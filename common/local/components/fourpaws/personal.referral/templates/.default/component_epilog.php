<script>
    var ajax_url = '/ajax/personal/referral/refresh_referrals/';
    $.ajax({
        url: ajax_url,
        method: "POST",
        data: {
            user_id: <?=$templateData['USER_ID']?>,
            item_ids: '<?=implode(',', $templateData['ITEM_IDS'])?>'
        },
        success: function (data) {
            if (data.success) {
                var mainData = data.data;
                if (mainData.reload) {
                    location.reload();
                }
                $('div.b-account-referal').ready(function () {
                    // обновляем количество в табах
                    if (!!mainData.counts) {
                        $('.js-tab-referal-item.js-count-all a span.b-tab-title__number').html('(' + mainData.counts.all + ')');
                        $('.js-tab-referal-item.js-count-active a span.b-tab-title__number').html('(' + mainData.counts.active + ')');
                        $('.js-tab-referal-item.js-count-moderated a span.b-tab-title__number').html('(' + mainData.counts.moderate + ')');
                    }

                    // обновляем количество бонусов
                    $('div.b-account-referal__full-number div.b-account-referal__text-number span.js-number').html(mainData.bonus);

                    //обновляем карточки
                    if (!!mainData.items) {
                        for (var i in mainData.items) {
                            var item = mainData.items[i];
                            var $referral = $('ul.js-referal-list li.js-item-referal.js-referral-' + item.id);

                            //смена бонусов
                            $referral.find('div.b-account-referal-item__bonus span.b-account-referal-item__number span.js-number').html(item.bonus);

                            //смена основной информации
                            $referral.find('div.b-account-referal-item__title').html(item.fio);
                            $referral.find('div.b-account-referal-item__info div.b-account-referal-item__info-text--number').html(item.phone);
                            $referral.find('div.b-account-referal-item__info div.b-account-referal-item__info-text--email').html(item.email);
                            $referral.find('div.b-account-referal-item__info div.b-account-referal-item__info-text--card').html(item.card);

                            //смена статуса модерации
                            var className = '';
                            var moderateText = '';
                            switch (item.moderated) {
                                case 'moderate':
                                    className = 'moderate';
                                    moderateText = 'На модерации';
                                    break;
                                case 'cancel':
                                    moderateText = 'Модерация отменена';
                                    break;
                                case 'active':
                                    className = 'active-referal';
                                    break;
                            }
                            $referral.find('div.b-account-referal-item__status--moderate').html(moderateText);
                            $referral.removeClass('moderate').removeClass('active-referal');
                            if (className.length > 0) {
                                $referral.addClass(className);
                            }
                        }
                    }
                });
            } else {
                console.log(data.message, 'Ошибка: ');
            }

        }
    })
</script>