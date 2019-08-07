<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>








<? if($arResult['USER_ID'] > 0) { ?>
<section class="ftco-section" id="fanreg">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="row justify-content-center">
                    <div class="col-md-12 heading-section text-center ftco-animate">
                        <h2 class="">ЗАРЕГИСТРИРОВАТЬ<br />ФАН-БОНУС</h2>
                        <hr />
                        <h5 class="mb-4">Для участия в&nbsp;розыгрыше зарегистрируйте ваш <nobr>фан-бонус</nobr>.</h5>
                        <h5 class="mb-4">Если ваш <nobr>фан-бонус</nobr>&nbsp;&mdash; это скидка на&nbsp;лакомства или аксессуары,
                            то&nbsp;просто используйте его для получения скидки в&nbsp;корзине на&nbsp;сайте, в&nbsp;мобильном приложение
                            или&nbsp;на&nbsp;кассе.</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <form class="form-fan-register">
                    <div class="row justify-content-center">
                        <div class="col-md-12 heading-section text-left ftco-animate">
                            <p>введите промокод в поле ниже</p>
                            <p>
                                <input type="text" name="check_number" placeholder="s719d1f2972" max="11" required/>
                                <a href="javascript:void(0);" class="btn btn-primary btn-primary-filled py-3 px-4 js-submit-form">ЗАРЕГИСТРИРОВАТЬ ФАН-БОНУС</a>
                            </p>
                        </div>
                    </div>
                    <div class="response-messsage" style="color: red"></div>
                </form>
            </div>
        </div>
    </div>
</section>






<? } ?>