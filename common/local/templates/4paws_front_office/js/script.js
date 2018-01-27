$(document).ready(function () {
	
	$('#page').on('click', '#nextStep', function (ev) {
		ev.preventDefault();
		if ($('.err').length == 0) {
			$(this).removeAttr('disabled');
			nextStep('.form-page');
		}
	})

	$('#page').on('click', '.ajaxButton', function () {
		if (oGlobal.isCheckCode > 0) {
			$(this).attr('disabled', true).addClass('loading-cur');
			sendCheck($(this));
		} else {
			alert('Ой, Вы 2 раза ввели не тот номер телефона, пожалуйста, начните процесс регистрации с начала.');
		}
	})

	$('#page').on('click', '.check-code__btn', function (ev) {
		if (oGlobal.isCheckCode > 0) {
			checkCode($('input.check-code__field').val(), $('.form-page'), $(this));
		} else {
			alert('Ой, Вы 2 раза ввели не тот номер телефона, пожалуйста, начните процесс регистрации с начала.');
		};
		ev.preventDefault();
	})

	$('#page').on('change', '#email', function (ev) {
		var p = /^[-\w.]+@([A-z0-9][-A-z0-9]+\.)+[A-z]{2,4}$/;
		if (this.value != '' && !p.test(this.value)) {
			ev.preventDefault();
			alert('В поле Email введены недопустимые символы!');
		}
	})


	$('body').on('click', '.window-close', function () {
		$(this).parents('.window').hide();
	})

});

oGlobal = {
	isCheckCode: 2
}

function nextStep (sElem) {
	$(sElem).find('input[type=submit]').attr('disabled', true);
	$(sElem).find('.form-page__submit-wrap').addClass('loading');
	var oDataSerial = $(sElem).serializeArray(),
		oData = {
			'AJAX': 'Y'
		},
		sParam = '';
	$.each(oDataSerial, function (i, field) {
		oData[field.name] = field.value
	})
	sParam = $.param(oData);
	$.ajax({
		type: 'POST',
		dataType: 'html',
		url: $(sElem).attr('action'),
		data: oData,
		error: function(x, e) {
			alert('Error ' + x.status);
		},
		complete: function (xhr, status) {
			$(sElem).replaceWith(xhr.responseText);
			$('html, body').animate({ scrollTop: $(document).height() }, 200);
			$(sElem).find('#nextStep').removeAttr('disabled');
			$(sElem).find('.form-page__submit-wrap').removeClass('loading');
		}
	})
}

// function sendCheck (eBtnCheck) {
// 	var eField = eBtnCheck.siblings('input'),
// 		oDate = {action : eBtnCheck.attr('data-action')},
// 		sType = eField.attr('name'),
// 		eWinConfurm = $('#check-confirm-'+ sType),
// 		eBtnNext = eBtnCheck.parents('form').find('#nextStep');
// 	eBtnNext.attr('disabled', true);

// 	oDate[sType] = eField.val();
// 	eWinConfurm.find('b').html($('#'+ sType).val());

// 	$.ajax({
// 		type: 'POST',
// 		dataType: 'json',
// 		url: './?AJAX=Y',
// 		data: oDate,
// 		error: function(x, e) {
// 			alert('Error status ' + x.status);
// 		},
// 		complete: function(xhr, status){
// 			//alert(xhr.responseText);
// 			var jData = $.parseJSON(xhr.responseText);
// 			eBtnCheck.removeAttr('disabled').removeClass('loading-cur');
// 			if (jData.result) {
// 				eWinConfurm.find('input').val('');
// 				eWinConfurm.find('.check-code__msg').remove();
// 				eWinConfurm.find('.check-code__btn').data('type', sType);
// 				eWinConfurm.show();
// 				if(sType == 'email') {
// 					eBtnNext.hide();
// 					eBtnCheck.hide();
// 					eField.attr('readonly', true);
// 					var eRepeat = eBtnCheck.siblings().children('.email-repeat');
// 					eRepeat.after('<a class="'+ eRepeat.attr('class') +'" href="#">'+ eRepeat.html() +'</a>');
// 					eRepeat.remove();
// 				}
// 			} else {
// 				if (jData.error && !jData.error.hasOwnProperty('code')) {
// 					if(sType == 'phone'){
// 						var sDate = jData.error.replace('blocked to ',''),
// 							dCurDate = new Date(),
// 							dDate = new Date(sDate.replace(/(\d+).(\d+).(\d+)/, '$3/$2/$1')),
// 							iTime = dDate.getTime() - dCurDate.getTime(),
// 							iMin = Math.floor(iTime / 1000 / 60);
// 						alert('Извините, превышен лимит отправок! Пожалуйста, повторите через '+ iMin +' мин.');
// 					} else {
// 						if(sType == 'email') {
// 							alert('email jData: ', jData);
// 						}
// 					}
// 				} else {
// 					alert('Нет ответа от сервера. Объект jData: ', jData)
// 				}
// 			}
// 		}
// 	})
// }

// function checkCode (iCode, eForm, eBtn) {
// 	eBtn.attr('disabled', true).addClass('loading-cur');
// 	eBtn.parents('.window').find('.check-code__msg').remove();
// 	var oDate = {'code':iCode};

// 	if(eBtn.data('type') == 'email')
// 		oDate.action = 'verificationEmail'
// 	else if(eBtn.data('type') == 'phone')
// 		oDate.action = 'verificationPhone';

// 	$.ajax({
// 		type: 'POST',
// 		dataType: 'json',
// 		url: './?AJAX=Y',
// 		data: oDate,
// 		error: function(x, e) {
// 			alert('Ошибка отправки кода, status: ' + x.status)
// 		},
// 		complete: function(xhr, status){
// 			var jData = $.parseJSON(xhr.responseText);
// 			eBtn.removeAttr('disabled').removeClass('loading-cur');
// 			if (jData.result) {
// 				$('.check-confirm-phone-wrap').hide();
// 				eBtn.parents('.window').find('.check-code__msg').remove();
// 				eForm.find('input[type=submit]').removeAttr('disabled');
// 				nextStep('.form-page');
// 			} else {
// 				if (jData.error && jData.error == 'timeout') {
// 					window.location.reload()
// 				} else {
// 					if(eBtn.data('type') == 'phone') oGlobal.isCheckCode--;
// 					eBtn.removeAttr('disabled').parent().after('<p class="check-code__msg">Проверочный код неверный!</p>');
// 				}
// 			}
// 		}
// 	})
// }