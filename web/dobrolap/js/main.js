AOS.init({
 	duration: 800,
 	easing: 'slide'
 });

 var unlock;

(function($) {

	"use strict";

	$(window).stellar({
    responsive: true,
    parallaxBackgrounds: true,
    parallaxElements: true,
    horizontalScrolling: false,
    hideDistantElements: false,
    scrollProperty: 'scroll'
  });


	var fullHeight = function() {

		$('.js-fullheight').css('height', $(window).height());
		$(window).resize(function(){
			$('.js-fullheight').css('height', $(window).height());
		});

	};
	fullHeight();

	// loader
	var loader = function() {
		setTimeout(function() { 
			if($('#ftco-loader').length > 0) {
				$('#ftco-loader').removeClass('show');
			}
		}, 1);
	};
	loader();

	// Scrollax
   $.Scrollax();



   // Burger Menu
	var burgerMenu = function() {

		$('body').on('click', '.js-fh5co-nav-toggle', function(event){

			event.preventDefault();

			if ( $('#ftco-nav').is(':visible') ) {
				$(this).removeClass('active');
			} else {
				$(this).addClass('active');	
			}

			
			
		});

	};
	burgerMenu();


	var onePageClick = function() {


		$('#ftco-nav').on('click', 'a[href^="#"]', function (event) {
	    event.preventDefault();

	    var href = $.attr(this, 'href');

	    $('html, body').animate({
	        scrollTop: $($.attr(this, 'href')).offset().top - 70
	    }, 500, function() {
	    	// window.location.hash = href;
	    });
		});

	};

	onePageClick();
	

	var carousel = function() {
		$('.home-slider').owlCarousel({
	    loop:true,
	    autoplay: false,
	    margin:0,
	    lazyLoad:true,
	    animateOut: 'fadeOut',
	    animateIn: 'fadeIn',
	    nav:true,
	    video:true,
	    autoplayHoverPause: true,
	    items: 1,
	    navText : ["<span class='ion-ios-arrow-left'></span>","<span class='ion-ios-arrow-right'></span>"],
	    responsive:{
	      0:{
	        items:1
	      },
	      600:{
	        items:1
	      },
	      1000:{
	        items:1
	      }
	    }
		});
	};
	carousel();

	$('nav .dropdown').hover(function(){
		var $this = $(this);
		// 	 timer;
		// clearTimeout(timer);
		$this.addClass('show');
		$this.find('> a').attr('aria-expanded', true);
		// $this.find('.dropdown-menu').addClass('animated-fast fadeInUp show');
		$this.find('.dropdown-menu').addClass('show');
	}, function(){
		var $this = $(this);
			// timer;
		// timer = setTimeout(function(){
			$this.removeClass('show');
			$this.find('> a').attr('aria-expanded', false);
			// $this.find('.dropdown-menu').removeClass('animated-fast fadeInUp show');
			$this.find('.dropdown-menu').removeClass('show');
		// }, 100);
	});


	$('#dropdown04').on('show.bs.dropdown', function () {
	  console.log('show');
	});

	// scroll
	var scrollWindow = function() {
		$(window).scroll(function(){
			var $w = $(this),
					st = $w.scrollTop(),
					navbar = $('.ftco_navbar'),
					sd = $('.js-scroll-wrap');

			if (st > 150) {
				if ( !navbar.hasClass('scrolled') ) {
					navbar.addClass('scrolled');	
				}
			} 
			if (st < 150) {
				if ( navbar.hasClass('scrolled') ) {
					navbar.removeClass('scrolled sleep');
				}
			} 
			if ( st > 350 ) {
				if ( !navbar.hasClass('awake') ) {
					navbar.addClass('awake');	
				}
				
				if(sd.length > 0) {
					sd.addClass('sleep');
				}
			}
			if ( st < 350 ) {
				if ( navbar.hasClass('awake') ) {
					navbar.removeClass('awake');
					navbar.addClass('sleep');
				}
				if(sd.length > 0) {
					sd.removeClass('sleep');
				}
			}
		});
	};
	scrollWindow();

	
	var windowHeight = $(window).height();
 
	$(document).on('scroll', function() {
		$('.ftco-counter').each(function() {
			var self = $(this),
			height = self.offset().top + self.height();
			if ($(document).scrollTop() + windowHeight >= height) {
				var counter = function() {
		
					$('#section-counter, .hero-wrap, .ftco-counter, .ftco-about').waypoint( function( direction ) {

						if( direction === 'down' && !$(this.element).hasClass('ftco-animated') ) {

							var comma_separator_number_step = $.animateNumber.numberStepFactories.separator(' ')
							$('.number').each(function(){
								var $this = $(this),
									num = $this.data('number');
									console.log(num);
								$this.animateNumber(
								  {
								    number: num,
								    numberStep: comma_separator_number_step
								  }, 7000
								);
							});
							
						}

					} , { offset: '95%' } );

				}
				counter();
			}
		});
	});


	


	var contentWayPoint = function() {
		var i = 0;
		$('.ftco-animate').waypoint( function( direction ) {

			if( direction === 'down' && !$(this.element).hasClass('ftco-animated') ) {
				
				i++;

				$(this.element).addClass('item-animate');
				setTimeout(function(){

					$('body .ftco-animate.item-animate').each(function(k){
						var el = $(this);
						setTimeout( function () {
							var effect = el.data('animate-effect');
							if ( effect === 'fadeIn') {
								el.addClass('fadeIn ftco-animated');
							} else if ( effect === 'fadeInLeft') {
								el.addClass('fadeInLeft ftco-animated');
							} else if ( effect === 'fadeInRight') {
								el.addClass('fadeInRight ftco-animated');
							} else {
								el.addClass('fadeInUp ftco-animated');
							}
							el.removeClass('item-animate');
						},  k * 50, 'easeInOutExpo' );
					});
					
				}, 100);
				
			}

		} , { offset: '95%' } );
	};
	contentWayPoint();

	// magnific popup
	$('.image-popup').magnificPopup({
    type: 'image',
    closeOnContentClick: true,
    closeBtnInside: false,
    fixedContentPos: true,
    mainClass: 'mfp-no-margins mfp-with-zoom', // class to remove default margin from left and right side
     gallery: {
      enabled: true,
      navigateByImgClick: true,
      preload: [0,1] // Will preload 0 - before current, and 1 after the current image
    },
    image: {
      verticalFit: true
    },
    zoom: {
      enabled: true,
      duration: 300 // don't foget to change the duration also in CSS
    }
  });

  $('.popup-youtube, .popup-vimeo, .popup-gmaps').magnificPopup({
    disableOn: 700,
    type: 'iframe',
    mainClass: 'mfp-fade',
    removalDelay: 160,
    preloader: false,

    fixedContentPos: false
  });

  	//--  разворачиваем/сворачиваем полный список приютов
	$(".read_more a").click(function(){
		$(".roll_block").slideToggle("slow");
		$(this).text($(this).text() == 'Свернуть ▲' ? 'Показать больше ▼' : 'Свернуть ▲');
		if ($(this).hasClass('see_more')) {
			$(this).removeClass('see_more');
			$(this).addClass('see_less');
		} else {
			$(this).removeClass('see_less');
			$(this).addClass('see_more');
		}
		var scrollTop = $('#shelter').offset().top;
		console.log('position is' + scrollTop);
		if ($(this).hasClass('see_more')) {
			$(document).scrollTop(scrollTop);	
			console.log('position is' + scrollTop);
		}
		return false;
	});

	//--  разворачиваем/сворачиваем текст приютов
	$(".read_more_btn").click(function(){
		$(".needs_note").slideToggle("slow");
		return false;
	});

	//-- показываем блок регистрации фана
	// $(".js-show-fan-form").click(function(){
	// 	$("#fanreg").slideToggle("slow");
	// 	return false;
	// });


	$(document).ready(function() {
      var owl = $('.owl-carousel');
      owl.owlCarousel({
        nav: true,
      
      })
    });

	//--закидываем пользователя наверх, если он нажал кнопку внизу

    // var scrollTop = $('#fanreg').offset().top;
	//
	// $('#thanks .btn-primary-filled').click(function(){
	// 	$(document).scrollTop(scrollTop);
	// })

	
	//--работа со всплывающим окном приютов
	$('[data-popup-id="shelter_popup"].js-open-popup, [data-popup="dobrolap_more_info_popup"] .js-close-popup').on('click', function () {
      unlock = locky.lockyOn('.js-popup-wrapper');
      $('html').css('overflow-y', 'hidden');
    });

	$('[data-popup="shelter_popup"] .js-close-popup, [data-popup="dobrolap_more_info_popup"] .js-close-popup').on('click', function () {
	  unlock();
	  $('html').removeAttr('style');
	});



	$('.js-popup-wrapper').on('click', function () {
	  var $this = $(this);

	  if($this.find('[data-popup="shelter_popup"].opened')) {
	    unlock();
	    $('html').removeAttr('style');
	  }
	});

})(jQuery);