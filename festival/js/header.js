var Header = function () {

    // detect mobile device
    var isMobileDevice = function() {
        return  ((
            navigator.userAgent.match(/Android/i) ||
            navigator.userAgent.match(/BlackBerry/i) ||
            navigator.userAgent.match(/iPhone|iPad|iPod/i) ||
            navigator.userAgent.match(/Opera Mini/i) ||
            navigator.userAgent.match(/IEMobile/i)
        ) ? true : false);
    }

    // handle on page scroll
    var handleHeaderOnScroll = function() {
        if ($(window).scrollTop() > 60) {
            $('body').addClass("page_on_scroll");

            if ($('[data-header]').hasClass('navbar-static-top')){
                $('[data-header]').removeClass('navbar-static-top');
                $('[data-header]').addClass('navbar-fixed-top');
            }
        } else {
            $('body').removeClass("page_on_scroll");
            if ($('[data-header]').hasClass('navbar-fixed-top')){
                $('[data-header]').removeClass('navbar-fixed-top');
                $('[data-header]').addClass('navbar-static-top');
            }
        }
    }

    // Handle Header
    var handleOnePageHeader = function() {

        // jQuery for page scrolling feature - requires jQuery Easing plugin
        $('.js_nav-item a').bind('click', function(event) {
            event.preventDefault();

            var $offset = 0;
            $offset = $(".navbar-fixed-top").height()-20;

            if ($('body').hasClass('is_open_banner_top')){
                $offset = $offset + $('[data-banner-top]').height();
            }

            var $position = $($(this).attr('href')).offset().top;
            $('html, body').stop().animate({
                scrollTop: $position - $offset
            }, 600);
        });

        // Collapse Navbar When It's Clickicked
        $(window).scroll(function() {
            $('.navbar-collapse.in').collapse('hide');
        });
    }

    // Handle Banner Top
    var handleBannerTop = function() {


        var filetime = $('[data-banner-top]').data('banner-top-filetime');
        if (!$.cookie('hide_banner_top') || !!!sessionStorage.getItem('hide_banner_top') || ($.cookie('hide_banner_top') && $.cookie('hide_banner_top') != filetime) || (!!sessionStorage.getItem('hide_banner_top') && sessionStorage.getItem('hide_banner_top') != filetime)) {
            $('[data-banner-top]').removeClass('hidden');
            $('body').addClass('is_open_banner_top');
        }

        $('[data-banner-top-close]').on('click', function(event) {
            event.preventDefault();

            $('[data-banner-top]').addClass('hidden');
            $('body').removeClass('is_open_banner_top');

            var filetime = $('[data-banner-top]').data('banner-top-filetime');
            $.cookie('hide_banner_top', filetime, {
                path: '/'
            });
            sessionStorage.setItem('hide_banner_top', filetime);
        });
    }

    return {
        init: function () {
            // initial setup for fixed header
            handleHeaderOnScroll();
            handleOnePageHeader(); // initial header
            handleBannerTop();$

            // handle minimized header on page scroll
            $(window).scroll(function() {
                handleHeaderOnScroll();
            });
        },

        // To get the correct viewport width based on  http://andylangton.co.uk/articles/javascript/get-viewport-size-javascript/
        getViewPort: function() {
            var e = window,
                a = 'inner';
            if (!('innerWidth' in window)) {
                a = 'client';
                e = document.documentElement || document.body;
            }

            return {
                width: e[a + 'Width'],
                height: e[a + 'Height']
            };
        },
    };

}();

$(document).ready(function() {
    Header.init();
});