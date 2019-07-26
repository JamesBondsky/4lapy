<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Добролап");
?>
    <nav class="navbar navbar-expand-lg navbar-dark ftco_navbar ftco-navbar-light site-navbar-target" id="ftco-navbar">
	    <div class="container">
	      <button class="navbar-toggler js-fh5co-nav-toggle fh5co-nav-toggle" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
	        <span class="oi oi-menu"></span> Навигация
	      </button>

	      <div class="collapse navbar-collapse" id="ftco-nav">
	        <ul class="navbar-nav nav ml-auto">
	          <li class="nav-item"><a href="#needs" class="nav-link"><span>Помогаем вместе</span></a></li>
	          <li class="nav-item"><a href="#shelter" class="nav-link"><span>Приюты-участники</span></a></li>
	          <li class="nav-item"><a href="#how_get" class="nav-link"><span>Принять участие</span></a></li>
	          <li class="nav-item"><a href="#thanks" class="nav-link"><span>Добрые сюрпризы</span></a></li>
	          <li class="nav-item"><a href="#little" class="nav-link"><span>Маленькие друзья</span></a></li>
	          <li class="nav-item"><a href="#challenge" class="nav-link"><span>Челлендж</span></a></li>
	          <li class="nav-item"><a href="#raise" class="nav-link"><span>Едем помогать</span></a></li>
	          <li class="nav-item"><a href="#photos" class="nav-link"><span>Фотоотчеты</span></a></li>
	        </ul>
	      </div>
	    </div>
	  </nav>


    <section class="ftco-about img ftco-section ftco-no-pb" id="about-section">
    	<div class="container">
    		<div class="row d-flex">
    			<div class="col-md-6 col-lg-5 d-flex">
    				<div class="img-about img d-flex align-items-stretch">
    					<div class="overlay"></div>
	    				<div class="img d-flex align-self-stretch align-items-center" style="background-image:url(dobrolap/images/key_visual.png); background-size: contain; background-position: center bottom;">
	    				</div>
    				</div>
    			</div>
    			<div class="col-md-6 col-lg-7 pb-5">
    				<div class="row justify-content-start pb-3">
		          <div class="col-md-12 heading-section ftco-animate">
		          	<span class="subheading">IV ЕЖЕГОДНАЯ  Благотворительная акция «Добролап»</span>
			            <h1 class="mb-4 mt-3">ТВОРИМ ДОБРО ВМЕСТЕ</span></h1>
			            <p>
                            <a href="https://4lapy.ru/shares/blagotvoritelnaya-aktsiya-dobrolap-dlya-zhivotnykh-ikh-priyutov2.html" class="btn btn-primary py-3 px-4">ХОЧУ ПОМОЧЬ</a>
                            <a href="javascript:void(0);" class="btn btn-primary btn-primary-filled py-3 px-4 <?=($USER->IsAuthorized()) ? 'js-show-fan-form' : 'js-open-popup'?>" data-popup-id="authorization">ЗАРЕГИСТРИРОВАТЬ ФАН</a>
                        </p>
		          </div>
		        </div>
	        </div>
        </div>
    	</div>
    </section>

    <? $APPLICATION->IncludeComponent('articul:dobrolap.form', '', []); ?>

    <section class="ftco-section" id="needs">
			<div class="container">
				<div class="row justify-content-center pb-5">
                    <div class="col-md-12 heading-section text-center ftco-animate">
                        <h2 class="">Собираем необходимое</h2>
                        <h5 class="mb-4">ДЛЯ ПИТОМЦЕВ ИЗ 44 ПРИЮТОВ РОССИИ</h5>
                        <hr />
                        <div class="harvest_icon read_more_btn">
                            <a href="javascript:void(0);" class="btn btn-primary-filled py-3 px-4">ПОДРОБНЕЕ</a>
                    	</div>
                    	<div class="needs_note">
                        <h5 class="mb-4">С 1 августа  в компании «Четыре Лапы» стартует благотворительная инициатива ДОБРОЛАП. «Творим добро вместе!» -  под таким лозунгом уже 4 года вместе с 44 благотворительными организациями мы объединяем всех, кто не равнодушен к временно бездомным питомцам, чтобы помочь животным найти друзей и поддержать самым нужным. Принять участие можно на сайте «Добролап», нажав кнопку «Хочу помочь», или в любом зоомагазине «Четыре Лапы». Присоединяйтесь к команде «Добролап», узнавайте подробности на сайте, принимайте участие в челлендже #ЯДОБРОЛАП и следите за новостями в социальных сетях.</h5>
                    	</div>
                    </div>
                </div>
                <div class="row">
                    <? $APPLICATION->IncludeComponent('articul:dobrolap.necessary', '', []); ?>
                    <div class="harvest_icon">
                            <a href="https://4lapy.ru/shares/blagotvoritelnaya-aktsiya-dobrolap-dlya-zhivotnykh-ikh-priyutov2.html" class="btn btn-primary-filled py-3 px-4">ХОЧУ ПОМОЧЬ</a>
                    </div>
                </div>
			</div>
    </section>

		<section class="ftco-section ftco-counter img" id="helps">
    	<div class="container">

    		<div class="col-md-12 heading-section text-center ftco-animate">
	            <h2 class="">Мы помогаем</h2>
            	<hr />
          	</div>
			<div class="row d-md-flex align-items-center">
          <div class="col-md d-flex justify-content-center counter-wrap ftco-animate">
            <div class="block-18">
              <div class="text">
                <strong class="number" data-number="18538">0</strong>
                <span>Питомцам</span>
              </div>
            </div>
          </div>
          <div class="col-md d-flex justify-content-center counter-wrap ftco-animate">
            <div class="block-18">
              <div class="text">
              	<span class="free_place">из</span>
                <strong class="number" data-number="44">0</strong>
                <span>приютов</span>
              </div>
            </div>
          </div>
          <div class="col-md d-flex justify-content-center counter-wrap ftco-animate">
            <div class="block-18">
              <div class="text">
              	<span class="free_place">в</span>
                <strong class="number" data-number="20">0</strong>
                <span>городах</span>
              </div>
            </div>
          </div>
          <div class="cat_dog">
          	<img src="/dobrolap/images/help_bg_2.png" alt="" />
          </div>
        </div>
      </div>
    </section>

    <section class="ftco-section" id="shelter">
			<div class="container">
				<div class="row justify-content-center">
          <div class="col-md-12 heading-section text-center ftco-animate">
            <h2 class="">Приюты участники</h2>
            <hr />
            <h5 class="mb-4">Каждый год «Добролап» помогает найти семью и друзей тысячам домашних животных. Ни одна из благотворительных организаций не остается без внимания и поддержки. Более 40 фондов, приютов, волонтерских групп и центров реабилитации ждут команду Добролап, более 200 000 неравнодушных участников уже с нами. Присоединяйся!
</h5>
          </div>
        </div>
				<div class="row">
                    <? $APPLICATION->IncludeComponent('articul:dobrolap.shelters', '', []); ?>
				</div>
			</div>
			<div class="read_more">
				<a href="#" class="btn btn-primary py-3 px-4">Показать больше&nbsp;<span>^</span></a>
			</div>
		</section>

    <section class="ftco-section" id="how_get">
    	<div class="container">
    		<div class="row justify-content-center pb-5">
          <div class="col-md-12 heading-section text-center ftco-animate">
            <h2 class="">Принять участие легко</h2>
            	<hr />
          </div>
        </div>
    		<div class="row">
    			<div class="col-md-6">
    				<h4 class="subheader">в магазине «Четыре лапы»</h4>
    					<div class="col-md-12 animate-box">
							<div class="rule_wrap">

								<div class="rule_number">
									<img src="/dobrolap/images/01.png" alt="01" />
								</div>
								<div class="rule_icon">
									<img src="/dobrolap/images/icon_1.png" alt="купи подарок" />
								</div>
								<div class="rule_note">
									<span><strong>купи подарок</strong><br />для питомцев из приюта</span>
								</div>
							</div>
						</div>

						<div class="col-md-12 animate-box">
							<div class="rule_wrap">

								<div class="rule_number">
									<img src="/dobrolap/images/02.png" alt="02" />
								</div>
								<div class="rule_icon">
									<img src="/dobrolap/images/icon_2.png" alt="положи в корзину" />
								</div>
								<div class="rule_note">
									<span><strong>положи его</strong><br />в корзину #добролап</span>
								</div>
							</div>
						</div>

						<div class="col-md-12 animate-box">
							<div class="rule_wrap">

								<div class="rule_number">
									<img src="/dobrolap/images/03.png" alt="03" />
								</div>
								<div class="rule_icon">
									<img src="/dobrolap/images/icon_3.png" alt="получи сюрприз" />
								</div>
								<div class="rule_note">
									<span><strong>ПОЛУЧИ СЮРПРИЗ</strong><br />И МАГНИТ #ДОБРОЛАП НА КАССЕ</span>
								</div>
							</div>
						</div>

						<div class="col-md-12 animate-box">
							<div class="rule_wrap">

								<div class="rule_number">
									<img src="/dobrolap/images/04.png" alt="04" />
								</div>
								<div class="rule_icon">
									<img src="/dobrolap/images/icon_4.png" alt="следи за итогами" />
								</div>
								<div class="rule_note">
									<span><strong>СЛЕДИ</strong><br />ЗА ИТОГАМИ И ОТЧЕТАМИ</span>
								</div>
							</div>
						</div>
    			</div>

    			<div class="col-md-6 white-col">
    				<h4 class="subheader">на сайте&nbsp;&nbsp;<a href="https://4lapy.ru/" target="_blank"><img src="/dobrolap/images/4lapy.png" alt="" /></a></h4>
    				<div class="col-md-12 animate-box">
							<div class="rule_wrap">

								<div class="rule_number">
									<img src="/dobrolap/images/01.png" alt="01" />
								</div>
								<div class="rule_icon">
									<img src="/dobrolap/images/icon_5.png" alt="выбери товары" />
								</div>
								<div class="rule_note">
									<span><strong>ВЫБЕРИ ТОВАРЫ</strong><br />И ПОЛОЖИ В КОРЗИНУ</span>
								</div>
							</div>
						</div>

						<div class="col-md-12 animate-box">
							<div class="rule_wrap">

								<div class="rule_number">
									<img src="/dobrolap/images/02.png" alt="02" />
								</div>
								<div class="rule_icon">
									<img src="/dobrolap/images/icon_6.png" alt="ВЫБЕРИ ПРИЮТ" />
								</div>
								<div class="rule_note">
									<span><strong>ВЫБЕРИ ПРИЮТ</strong><br />ПРИ ОФОРМЛЕНИИ ЗАКАЗА</span>
								</div>
							</div>
						</div>

						<div class="col-md-12 animate-box">
							<div class="rule_wrap">

								<div class="rule_number">
									<img src="/dobrolap/images/03.png" alt="03" />
								</div>
								<div class="rule_icon">
									<img src="/dobrolap/images/icon_3.png" alt="получи сюрприз" />
								</div>
								<div class="rule_note">
									<span>ОПЛАТИ ЗАКАЗ,<br /><strong>ПОЛУЧИ СЮРПРИЗ</strong><br />И МАГНИТ #ДОБРОЛАП</span>
								</div>
							</div>
						</div>

						<div class="col-md-12 animate-box">
							<div class="rule_wrap">

								<div class="rule_number">
									<img src="/dobrolap/images/04.png" alt="04" />
								</div>
								<div class="rule_icon">
									<img src="/dobrolap/images/icon_4.png" alt="следи за итогами" />
								</div>
								<div class="rule_note">
									<span><strong>СЛЕДИ</strong><br />ЗА ИТОГАМИ И ОТЧЕТАМИ</span>
								</div>
							</div>
						</div>
    			</div>
    		</div>
    	</div>
    </section>

    <section class="ftco-section" id="thanks">
    	<div class="container">
    		<div class="row">

    			<div class="col-md-6 col-md-6-mobile">
    				<div class="row justify-content-center">
			          <div class="col-md-12 heading-section text-center ftco-animate">
			            <h2 class="">ДОБРЫЕ<br />СЮРПРИЗЫ</h2>
			            <hr />
                        <h5 class="mb-4">Каждому человеку под силу совершить доброе дело и сделать счастливым маленького пушистого друга. Тем более, что для этого надо совсем немного. На память о добром поступке каждый участник «Добролап» получит памятный магнит и один из приятных сюрпризов. Весь август в магазинах «Четыре лапы» и на сайте «Добролап».</h5>
			            <p>
                            <a href="javascript:void(0);" class="btn btn-primary-filled py-3 px-4 <?=($USER->IsAuthorized()) ? 'js-show-fan-form' : 'js-open-popup'?>" data-popup-id="authorization">ЗАРЕГИСТРИРОВАТЬ ФАН</a>
                            <a href="javascript:void(0);" class="btn btn-primary py-3 px-4 js-open-popup" data-popup-id="dobrolap_more_info_popup">ПОДРОБНЫЕ СВЕДЕНИЯ</a>
                        </p>
			          </div>
			        </div>
    			</div>


    			<div class="col-md-3">
    					<div class="col-md-12 animate-box">
							<div class="rule_wrap">
								<div class="rule_icon">
									<img src="/dobrolap/images/icon_7.png" alt="" />
								</div>
							</div>
						</div>

						<div class="col-md-12 animate-box">
							<div class="rule_wrap">
								<div class="rule_icon">
									<img src="/dobrolap/images/icon_9.png" alt="" />
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-3">
						<div class="col-md-12 animate-box">
							<div class="rule_wrap">
								<div class="rule_icon">
									<img src="/dobrolap/images/icon_8.png" alt="" />
								</div>
							</div>
						</div>

						<div class="col-md-12 animate-box">
							<div class="rule_wrap">
								<div class="rule_icon">
									<img src="/dobrolap/images/icon_10.png" alt="" />
								</div>
							</div>
						</div>
    			</div>

    			<div class="col-md-6 col-md-6-desktop">
    				<div class="row justify-content-center">
			          <div class="col-md-12 heading-section text-center ftco-animate">
			            <h2 class="">ДОБРЫЕ<br />СЮРПРИЗЫ</h2>
			            <hr />
                        <h5 class="mb-4">Каждому человеку под силу совершить доброе дело и сделать счастливым маленького пушистого друга. Тем более, что для этого надо совсем немного. На память о добром поступке каждый участник «Добролап» получит памятный магнит и один из приятных сюрпризов. Весь август в магазинах «Четыре лапы» и на сайте «Добролап».</h5>
			            <p>
                            <a href="javascript:void(0);" class="btn btn-primary-filled py-3 px-4 <?=($USER->IsAuthorized()) ? 'js-show-fan-form' : 'js-open-popup'?>" data-popup-id="authorization">ЗАРЕГИСТРИРОВАТЬ ФАН</a>
                            <a href="javascript:void(0);" class="btn btn-primary py-3 px-4 js-open-popup" data-popup-id="dobrolap_more_info_popup">ПОДРОБНЫЕ СВЕДЕНИЯ</a>
                        </p>
			          </div>
			        </div>
    			</div>
    		</div>
    	</div>
    </section>

    <section class="ftco-section ftco-no-pb ftco-no-pt" id="little">
    	<div class="container">
    		<div class="row">
    			<div class="col-md-6">
    				<div class="row justify-content-center">
			          <div class="col-md-12 heading-section text-center ftco-animate">
			            <h2 class="">большая помощь<br />для маленького друга</h2>
			            <hr />
			            <h5 class="mb-4">Для самых маленьких «Четыре лапы» подготовили удобные подарочные коробочки, в которые малыши могут положить подарок для питомца из приюта прямо в магазине и подписать адресата, чтобы потом увидеть на сайте счастливые мордочки питомцев в рубрике «Фотоотчет». Обязательно присоединяйтесь вместе с детьми: помощь маленького друга - это большое доброе сердце и счастье научится делать чудеса своими руками</h5>
			          </div>
			        </div>
    			</div>
    			<div class="col-md-6">
    					<div class="col-md-12 animate-box">
							<div class="rule_wrap">
								<div class="rule_icon">
									<img src="/dobrolap/images/little_boy.jpg" alt="" />
								</div>
							</div>
						</div>
					</div>
    		</div>
    	</div>
    </section>

    <section class="ftco-section" id="challenge">
    	<div class="col-md-12">
    				<div class="row justify-content-center">
			          <div class="col-md-12 heading-section text-center ftco-animate">
			            <h2 class="">Челлендж #я-добролап</h2>
			            <hr />
			            <h5 class="mb-4">Стань частью команды ДОБРОЛАП – включайся в челлендж : расскажи своим подписчикам о том, как помочь питомцам, у которых пока нет дома. Запиши видео или прикрепи фотографию. Обязательно поставь хештег #ядобролап. Делись и собирай «лайки»: авторы 10 самых популярных историй смогут превратить свои лайки «лайки» в бонусные баллы! Присоединяйтесь к команде и следите за новостями в социальных сетях.</h5>
			          </div>
			        </div>
    	</div>
    	<div class="b-container">
		    <section class="b-common-section">
		        <div class="b-common-section__title-box b-common-section__title-box--sale">
		            <h2 class="b-title b-title--sale">&nbsp;</h2>
		        </div>
		        <div class="b-common-section__content b-common-section__content--sale b-common-section__content--main-sale js-popular-product">
		            <div class="b-common-item">
		                <a href="/dobrolap/images/img01.jpg" data-lightbox="image-1" data-title="My caption"><img src="/dobrolap/images/img01.jpg" /></a>
		                <div class="carousel-note"><p class="mb-4">Lorem Ipsum<br/ >ДЛЯ ПИТОМЦЕВ ИЗ<br/ >40 ПРИЮТОВ РОССИИ</p></div>
		            </div>
		            <div class="b-common-item">
		                <a href="/dobrolap/images/img02.jpg" data-lightbox="image-2" data-title="My caption"><img src="/dobrolap/images/img02.jpg" /></a>
		                <div class="carousel-note"><p class="mb-4">Lorem Ipsum<br/ >ДЛЯ ПИТОМЦЕВ ИЗ<br/ >40 ПРИЮТОВ РОССИИ</p></div>
		            </div>
		            <div class="b-common-item">
		                <a href="/dobrolap/images/img03.jpg" data-lightbox="image-3" data-title="My caption"><img src="/dobrolap/images/img03.jpg" /></a>
		                <div class="carousel-note"><p class="mb-4">Lorem Ipsum<br/ >ДЛЯ ПИТОМЦЕВ ИЗ<br/ >40 ПРИЮТОВ РОССИИ</p></div>
		            </div>
		            <div class="b-common-item">
		                <a href="/dobrolap/images/img04.jpg" data-lightbox="image-3" data-title="My caption"><img src="/dobrolap/images/img04.jpg" /></a>
		                <div class="carousel-note"><p class="mb-4">Lorem Ipsum<br/ >ДЛЯ ПИТОМЦЕВ ИЗ<br/ >40 ПРИЮТОВ РОССИИ</p></div>
		            </div>
		            <div class="b-common-item">
		                <a href="/dobrolap/images/img05.jpg" data-lightbox="image-4" data-title="My caption"><img src="/dobrolap/images/img05.jpg" /></a>
		                <div class="carousel-note"><p class="mb-4">Lorem Ipsum<br/ >ДЛЯ ПИТОМЦЕВ ИЗ<br/ >40 ПРИЮТОВ РОССИИ</p></div>
		            </div>
		            <div class="b-common-item">
		                <a href="/dobrolap/images/img06.jpg" data-lightbox="image-5" data-title="My caption"><img src="/dobrolap/images/img06.jpg" /></a>
		                <div class="carousel-note"><p class="mb-4">Lorem Ipsum<br/ >ДЛЯ ПИТОМЦЕВ ИЗ<br/ >40 ПРИЮТОВ РОССИИ</p></div>
		            </div>
		            <div class="b-common-item">
		                <a href="/dobrolap/images/img07.jpg" data-lightbox="image-1" data-title="My caption"><img src="/dobrolap/images/img02.jpg" /></a>
		                <div class="carousel-note"><p class="mb-4">Lorem Ipsum<br/ >ДЛЯ ПИТОМЦЕВ ИЗ<br/ >40 ПРИЮТОВ РОССИИ</p></div>
		            </div>
		            <div class="b-common-item">
		                <a href="/dobrolap/images/img08.jpg" data-lightbox="image-1" data-title="My caption"><img src="/dobrolap/images/img03.jpg" /></a>
		                <div class="carousel-note"><p class="mb-4">Lorem Ipsum<br/ >ДЛЯ ПИТОМЦЕВ ИЗ<br/ >40 ПРИЮТОВ РОССИИ</p></div>
		            </div>
		        </div>
		    </section>
		</div>
    </section>

    <section class="ftco-section" id="raise">
    	<div class="col-md-12">
    				<div class="row justify-content-center">
			          <div class="col-md-12 heading-section text-center ftco-animate">
			            <h2 class="">едем помогать и везем паллету корма</h2>
			            <hr />
			            <h5 class="mb-4">Каждую неделю мы отправляемся по «добрым маршрутам», чтобы отвезти нужные и долгожданные подарки четверолапым друзьям из приютов. Примите участие в челлендже, расскажите друзьям и присоединяйтесь к нам. Вместе мы сможем больше!</h5>
			          </div>
			        </div>
    	</div>
    	<div class="home-slider  owl-carousel">
	      <div class="slider-item ">
	      	<div class="overlay"></div>
	        <div class="container">
	          <div class="row d-md-flex no-gutters slider-text align-items-end justify-content-end" data-scrollax-parent="true">
	          		<video controls poster="/dobrolap/images/29184619-preview.jpg">
					  <source src="/dobrolap/video/29184619-preview.mp4" type="video/mp4">
					</video>
	        	</div>
	        </div>
	      </div>
	      <div class="slider-item ">
	      	<div class="overlay"></div>
	        <div class="container">
	          <div class="row d-md-flex no-gutters slider-text align-items-end justify-content-end" data-scrollax-parent="true">
	          		<video controls poster="/dobrolap/images/1014142868-preview.jpg">
					  <source src="/dobrolap/video/1014142868-preview.mp4" type="video/mp4">
					</video>
	        	</div>
	        </div>
	      </div>
	      <div class="slider-item ">
	      	<div class="overlay"></div>
	        <div class="container">
	          <div class="row d-md-flex no-gutters slider-text align-items-end justify-content-end" data-scrollax-parent="true">
	          		<video controls poster="/dobrolap/images/1012398863-preview.jpg">
					  <source src="/dobrolap/video/1012398863-preview.mp4" type="video/mp4">
					</video>

					<span class='ion-ios-arrow-left'></span>
	        	</div>
	        </div>
	      </div>
	    </div>
    </section>

    <section class="ftco-section" id="photos">
    	<div class="col-md-12">
    				<div class="row justify-content-center">
			          <div class="col-md-12 heading-section text-center ftco-animate">
			            <h2 class="">фотоотчеты</h2>
			            <hr />
			            <h5 class="mb-4">Каждую неделю мы рассказываем о добрых событиях недели, делимся новостями и вместе радуемся счастливым историям питомцев, у которых появились друзья. Добро объединяет и делает нас лучше!</h5>
			          </div>
			        </div>
    	</div>
    	<div class="b-container">
		    <section class="b-common-section">
		        <div class="b-common-section__title-box b-common-section__title-box--sale">
		            <h2 class="b-title b-title--sale">&nbsp;</h2>
		        </div>
		        <div class="b-common-section__content b-common-section__content--sale b-common-section__content--main-sale js-popular-product">
		            <div class="b-common-item">
		                <a href="/dobrolap/images/img01.jpg" data-lightbox="image-1" data-title="My caption"><img src="/dobrolap/images/img01.jpg" /></a>
		                <div class="carousel-note"><p class="mb-4">Lorem Ipsum<br/ >ДЛЯ ПИТОМЦЕВ ИЗ<br/ >40 ПРИЮТОВ РОССИИ</p></div>
		            </div>
		            <div class="b-common-item">
		                <a href="/dobrolap/images/img02.jpg" data-lightbox="image-1" data-title="My caption"><img src="/dobrolap/images/img02.jpg" /></a>
		                <div class="carousel-note"><p class="mb-4">Lorem Ipsum<br/ >ДЛЯ ПИТОМЦЕВ ИЗ<br/ >40 ПРИЮТОВ РОССИИ</p></div>
		            </div>
		            <div class="b-common-item">
		                <a href="/dobrolap/images/img03.jpg" data-lightbox="image-1" data-title="My caption"><img src="/dobrolap/images/img03.jpg" /></a>
		                <div class="carousel-note"><p class="mb-4">Lorem Ipsum<br/ >ДЛЯ ПИТОМЦЕВ ИЗ<br/ >40 ПРИЮТОВ РОССИИ</p></div>
		            </div>
		            <div class="b-common-item">
		                <a href="/dobrolap/images/img04.jpg" data-lightbox="image-1" data-title="My caption"><img src="/dobrolap/images/img04.jpg" /></a>
		                <div class="carousel-note"><p class="mb-4">Lorem Ipsum<br/ >ДЛЯ ПИТОМЦЕВ ИЗ<br/ >40 ПРИЮТОВ РОССИИ</p></div>
		            </div>
		            <div class="b-common-item">
		                <a href="/dobrolap/images/img05.jpg" data-lightbox="image-1" data-title="My caption"><img src="/dobrolap/images/img05.jpg" /></a>
		                <div class="carousel-note"><p class="mb-4">Lorem Ipsum<br/ >ДЛЯ ПИТОМЦЕВ ИЗ<br/ >40 ПРИЮТОВ РОССИИ</p></div>
		            </div>
		            <div class="b-common-item">
		                <a href="/dobrolap/images/img06.jpg" data-lightbox="image-1" data-title="My caption"><img src="/dobrolap/images/img06.jpg" /></a>
		                <div class="carousel-note"><p class="mb-4">Lorem Ipsum<br/ >ДЛЯ ПИТОМЦЕВ ИЗ<br/ >40 ПРИЮТОВ РОССИИ</p></div>
		            </div>
		            <div class="b-common-item">
		                <a href="/dobrolap/images/img07.jpg" data-lightbox="image-1" data-title="My caption"><img src="/dobrolap/images/img02.jpg" /></a>
		                <div class="carousel-note"><p class="mb-4">Lorem Ipsum<br/ >ДЛЯ ПИТОМЦЕВ ИЗ<br/ >40 ПРИЮТОВ РОССИИ</p></div>
		            </div>
		            <div class="b-common-item">
		                <a href="/dobrolap/images/img08.jpg" data-lightbox="image-1" data-title="My caption"><img src="/dobrolap/images/img03.jpg" /></a>
		                <div class="carousel-note"><p class="mb-4">Lorem Ipsum<br/ >ДЛЯ ПИТОМЦЕВ ИЗ<br/ >40 ПРИЮТОВ РОССИИ</p></div>
		            </div>
		        </div>
		    </section>
		</div>
    </section><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>