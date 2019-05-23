<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->SetPageProperty('title', 'Фестиваль - онлайн сервис регистрации участников');
$APPLICATION->SetPageProperty('description', '');
$APPLICATION->SetTitle("Фестиваль - онлайн сервис регистрации участников");

?>

    <div class="top-nav">
        <a href="/fest-reg/" class="btn inline-block menu-item selected">Поиск участника</a>
        <a href="/fest-reg/reg/" class="btn inline-block menu-item">Регистрация</a>
    </div>

    <form class="form-page mb-l registration-form" action="" data-ajax-url="" data-result-container="#resultSearchCustomerFest" method="post" id="form0">
        <input type="hidden" name="formName" value="searchCustomerFest">
        <input type="hidden" name="action" value="postForm">
        <input type="hidden" name="sessid" value="">

        <div class="form-page__field-wrap">
            <label for="form0__promoID" class="form-page__label">
                ID
            </label>
            <input id="form0__promoID" name="promoID" value="" maxlength="100" class="form-page__field mb-l" type="text">
        </div>
        <div class="form-page__field-wrap">
            <label for="form0__phone" class="form-page__label">
                Мобильный телефон (10 знаков без 7 или 8 в формате 9ХХХХХХХХХ)
            </label>
            <input id="form0__phone" name="phone" value="" maxlength="10" class="form-page__field mb-l" type="text">
        </div>
        <div class="form-page__field-wrap">
            <label for="form0__cardNumberForHistory" class="form-page__label">Номер карты <sup>*</sup></label>
            <input id="form0__cardNumberForHistory" name="cardNumberForHistory" value="" maxlength="13" class="form-page__field mb-l" type="text">
        </div>

        <div class="form-page__submit-wrap">
            <input class="form-page__btn inline-block ajaxSubmitButton" type="submit" value="Поиск">
        </div>
    </form>

    <div id="resultSearchCustomerFest">
        <?/*<form class="form-page mb-l registration-form" action="" data-ajax-url="/ajax.php" data-result-container="#resultSearchCustomerFest" method="post" id="form1">
            <input type="hidden" name="formName" value="resultSearchCustomerFest">
            <input type="hidden" name="action" value="postForm">
            <input type="hidden" name="sessid" value="">

            <div class="form-page__field-wrap">
                <label for="form0__customerID" class="form-page__label">
                    ID
                </label>
                <input id="form0__customerID" name="customerID" value="" readonly="readonly" maxlength="100" class="form-page__field mb-l" type="text">
            </div>
            <div class="form-page__field-wrap">
                <label for="form1__firstName" class="form-page__label">
                    Имя
                    <span style="color: red;">*</span>
                </label>
                <input id="form1__firstName" name="firstName" value="" maxlength="100" class="form-page__field mb-l" type="text">
            </div>
            <div class="form-page__field-wrap">
                <label for="form1__lastName" class="form-page__label">
                    Фамилия
                </label>
                <input id="form1__lastName" name="lastName" value="" maxlength="100" class="form-page__field mb-l" type="text">
            </div>
            <div class="form-page__field-wrap">
                <label for="form1__phone" class="form-page__label">
                    Мобильный телефон (10 знаков без 7 или 8 в формате 9ХХХХХХХХХ)
                    <span style="color: red;">*</span>
                </label>
                <input id="form1__phone" name="phone" value="9188868175" maxlength="10" class="form-page__field mb-l" type="text">
            </div>
            <div class="form-page__field-wrap">
                <label for="form1__email" class="form-page__label">
                    Ваш email
                </label>
                <input id="form1__email" name="email" value="" maxlength="100" class="form-page__field mb-l _email" type="text">
            </div>
            <div class="form-page__field-wrap">
                <label for="form1__passportID" class="form-page__label">
                    Номер паспорта
                </label>
                <input id="form1__passportID" name="passportID" value="" maxlength="100" class="form-page__field mb-l" type="text">
            </div>

            <div class="form-page__submit-wrap">
                <input class="form-page__btn inline-block ajaxSubmitButton" type="submit" value="Зарегистрировать">
                <p><span style="color: red;">*</span>&nbsp;—&nbsp;обязательное поле</p>
            </div>
        </form> */?>
    </div>


    <script type="text/javascript">
        $(document).ready(
            function () {
                $('#page').on(
                    'change',
                    '.form-page ._email',
                    function (ev) {
                        var p = /^[-\w.]+@([A-z0-9][-A-z0-9]+\.)+[A-z]{2,4}$/;
                        if (this.value !== '' && !p.test(this.value)) {
                            ev.preventDefault();
                            alert('В поле Email введены недопустимые символы!');
                        }
                    }
                );
                var body = $('body');

                var isLoading = function() {
                    var actionContainer = $('#resultSearchCustomerFest');
                    return actionContainer.hasClass('loading');
                };

                var setLoading = function(val) {
                    var actionContainer = $('#resultSearchCustomerFest');
                    if (val === false) {
                        actionContainer.removeClass('loading');
                    } else {
                        actionContainer.addClass('loading');
                    }
                };

                var searchCustomerFormSubmit = function(submitForm) {
                    var ajaxUrl = submitForm.data('ajax-url');
                    var resultContainerSelector = submitForm.data('result-container');
                    var submitButton = $('input[type="submit"]', submitForm);

                    submitButton.attr('disabled', true);
                    submitForm.find('.form-page__submit-wrap').addClass('loading');

                    var formData = submitForm.serializeArray();

                    $.ajax(
                        {
                            type: 'POST',
                            dataType: 'html',
                            url: ajaxUrl,
                            data: formData,
                            error: function(x, e) {
                                alert('Error ' + x.status);
                            },
                            complete: function(xhr, status) {
                                $(resultContainerSelector).replaceWith(xhr.responseText);
                                $('html, body').animate(
                                    {
                                        scrollTop: $(document).height()
                                    },
                                    200
                                );
                                submitButton.removeAttr('disabled');
                                submitForm.find('.form-page__submit-wrap').removeClass('loading');
                            }
                        }
                    );
                };

                body.on(
                    'click',
                    '.ajaxSubmitButton',
                    function (event) {
                        event.preventDefault();

                        if (isLoading()) {
                            return;
                        }
                        setLoading();

                        var submitForm = $(this).closest('form');
                        searchCustomerFormSubmit(submitForm);
                    }
                );
            }
        );
    </script>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>