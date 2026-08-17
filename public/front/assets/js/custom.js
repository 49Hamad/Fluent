(function ($){"use strict";// 01. Preloader and All after loader


    // 02. scroll top specific
    if ($(".scroll-to-target").length){$(".scroll-to-target").on("click",function (){var target = $(this).attr("data-target");// animate
        $("html,body").animate({scrollTop:$(target).offset().top,}
  ,10
        )}
  )}
  // 03. header-style
    function headerStyle(){if ($(".main-header").length){var windowpos = $(window).scrollTop();var siteHeader = $(".main-header");var scrollLink = $(".scroll-top");if (windowpos >= 110){siteHeader.addClass("fixed-header");scrollLink.addClass("open")}
  else{siteHeader.removeClass("fixed-header");scrollLink.removeClass("open")}}}
  headerStyle();// submenu
    if ($(".main-header li.dropdown ul").length){$(".main-header .navigation li.dropdown").append(
        '<div class="dropdown-btn"><span class="fas fa-angle-down"></span></div>'
      )}
  // 04. mobile menu
    if ($(".mobile-menu").length){var mobileMenuContent = $(".main-header .menu-area .main-menu").html();$(".mobile-menu .menu-box .menu-outer").append(mobileMenuContent);$(".sticky-header .main-menu").append(mobileMenuContent);//Dropdown Button
      $(".mobile-menu li.dropdown .dropdown-btn").on("click",function (){$(this).toggleClass("open");$(this).prev("ul").slideToggle(500)}
  );//Dropdown Button
      $(".mobile-menu li.dropdown .dropdown-btn").on("click",function (){$(this).prev(".megamenu").slideToggle(900)}
  );//Menu Toggle Btn
      $(".mobile-nav-toggler").on("click",function (){$("body").addClass("mobile-menu-visible")}
  );//Menu Toggle Btn
      $(".mobile-menu .menu-backdrop,.mobile-menu .close-btn").on(
        "click",function (){$("body").removeClass("mobile-menu-visible")}
  )}
  // 05. wow animation
    if ($(".wow").length){var wow = new WOW({mobile:false,}
  );wow.init()}
  // 06. odometer
    if ($(".odometer").length){var odo = $(".odometer");odo.each(function (){$(this).appear(function (){var countNumber = $(this).attr("data-count");$(this).html(countNumber)}
  )}
  )}
  // 08. custom tab
    if ($(".tabs-box").length){$(".tabs-box .tab-buttons .tab-btn").on("click",function (e){e.preventDefault();var target = $($(this).attr("data-tab"));if ($(target).is(":visible")){return false}
  else{target
            .parents(".tabs-box")
            .find(".tab-buttons")
            .find(".tab-btn")
            .removeClass("active-btn");$(this).addClass("active-btn");target
            .parents(".tabs-box")
            .find(".tabs-content")
            .find(".tab")
            .fadeOut(0);target
            .parents(".tabs-box")
            .find(".tabs-content")
            .find(".tab")
            .removeClass("active-tab");$(target).fadeIn(100);$(target).addClass("active-tab")}}
  )}
  // 09. accordion
    if ($(".accordion-box").length){$(".accordion-box").on("click",".acc-btn",function (){var outerBox = $(this).parents(".accordion-box");var target = $(this).parents(".accordion");if ($(this).hasClass("active") !== true){$(outerBox).find(".accordion .acc-btn").removeClass("active")}
  if ($(this).next(".acc-content").is(":visible")){return false}
  else{$(this).addClass("active");$(outerBox).children(".accordion").removeClass("active-block");$(outerBox).find(".accordion").children(".acc-content").slideUp(300);target.addClass("active-block");$(this).next(".acc-content").slideDown(300)}}
  )}
  // 10. two-item-carousel
    if ($(".two-item-carousel").length){$(".two-item-carousel").owlCarousel({loop:true,margin:30,nav:false,smartSpeed:500,autoplay:1000,responsive:{0:{items:1,}
  ,480:{items:1,}
  ,600:{items:1,}
  ,800:{items:2,}
  ,1200:{items:2,}
  ,}
  ,}
  )}
  const owlCarousel = $('.custom-card-services');owlCarousel.owlCarousel({loop:true,margin:30,nav:true,dots:true,autoplay:true,autoplayTimeout:3000,autoplayHoverPause:true,smartSpeed:800,navText:[
            '<span class="nav-arrow prev-arrow">&#10094;</span>','<span class="nav-arrow next-arrow">&#10095;</span>'
        ],responsive:{0:{items:1,}
  ,480:{items:1,}
  ,600:{items:2,}
  ,800:{items:2,}
  ,1200:{items:3,}
  ,}
  ,}
  );if ($(".services-carousel").length){$(".services-carousel").owlCarousel({center:true,items:3,loop:true,autoplayTimeout:2000,smartSpeed:500,autoplay:true,margin:20,responsive:{0:{items:1}
  ,768:{items:2}
  ,992:{items:3}}}
  )}
  // 12. five-item-carousel
    if ($(".five-item-carousel").length){$(".five-item-carousel").owlCarousel({loop:true,margin:30,nav:true,smartSpeed:500,autoplayTimeout:1000,autoplay:2000,navText:[
          '<span class="icon-28"></span>','<span class="icon-29"></span>',],responsive:{0:{items:2,}
  ,480:{items:2,}
  ,600:{items:3,}
  ,800:{items:4,}
  ,1200:{items:5,}
  ,}
  ,}
  )}
  jQuery(document).on("ready",function (){(function ($){}
  )(jQuery)}
  );$(window).on("scroll",function (){headerStyle()}
  )}
  )(window.jQuery);
