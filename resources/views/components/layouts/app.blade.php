
@php
$AddingScripts = \App\Models\AddingScript::where('status',1)->get();
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">

    <title>فلوانت</title>
    <!-- add seo meta  -->
    @php
        $setting = App\Models\Setting::first() ?? null;
        $Consulting = App\Models\Consulting::first();
    @endphp
    <meta name="description" content="{{  $setting->meta_description }}">
    <meta name="keywords" content="
    @foreach ($setting->meta_keywords as $keyword)
    {{ $keyword }} ,
    @endforeach
    ">
    <meta name="author" content="فلوانت">
    <meta name="robots" content="index, follow">
    <meta name="revisit-after" content="7 days">
    <meta name="language" content="ar">
    <meta name="theme-color" content="#ffffff">



    <!-- Fav Icon -->
    <link rel="icon" href="{{ asset('front/assets/images/Fluent-LogoMark-Colored-RGB.png') }}" type="image/x-icon">


    <!-- Stylesheets List -->
    <link href="{{ asset('front/assets/css/bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('front/assets/css/owl.css') }}" rel="stylesheet">
    <link href="{{ asset('front/assets/css/font-awesome-all.css') }}" rel="stylesheet">
    <link href="{{ asset('front/assets/css/flaticon.css') }}" rel="stylesheet">

    <link href="{{ asset('front/assets/css/odometer.css') }}" rel="stylesheet">
    <link href="{{ asset('front/assets/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('front/assets/css/responsive.css') }}" rel="stylesheet">
    <link href="{{ asset('front/assets/css/custom.css') }}" rel="stylesheet">

    <style>
        .fl-wrapper[data-position$=-right] {
    right: .5em;
    z-index: 100000000000000000000;
}
    </style>

    @foreach ($AddingScripts as $script)
    {!! $script->script !!}
    @endforeach
@yield('style')
</head>


<!-- Boxed Wrapper -->

<body dir="rtl">



    <div class="boxed_wrapper">


        <!-- Main Header -->
        <header class="main-header header-two">
            <div class="outer-container">
                <div class="outer-box">
                    <div class="logo-box">
                        <figure class="logo"><a href="{{ route('home') }}"><img src="{{ asset('storage/'. $setting->headerlogo) }}" width="200" alt="شعار الشركة"></a></figure>
                    </div>
                    <div class="menu-area">
                        <div class="mobile-nav-toggler">
                            <i class="icon-bar"></i>
                            <i class="icon-bar"></i>
                            <i class="icon-bar"></i>
                        </div>
                        <nav class="main-menu navbar-expand-md navbar-light">
                            <div class="collapse navbar-collapse show" id="navbarSupportedContent">
                                <ul class="clearfix navigation">
                                    <li ><a href="{{ route('home') }}">الرئيسية</a></li>
                                    <li><a href="#AboutUs" class="menu-link-close">من نحن</a>
                                    <li><a href="#Services" class="menu-link-close">خدماتنا</a>
                                    <li><a href="#OurWorks" class="menu-link-close">اعمالنا </a>
                                    <li><a href="#OurPartenr" class="menu-link-close">شركاءنا </a>
                                    <li><a href="#ContactUs" class="menu-link-close">تواصل معنا </a></li>
                                </ul>
                            </div>
                        </nav>
                    </div>

                    @if (isset($Consulting->is_active) && $Consulting->is_active == true)
                    <a href="{{ $Consulting->button_link ?? null }}" class="px-5 py-3 btn theme-btn rounded-5 button-console-none"><span>{{ $Consulting->button_text ?? null }}</span></a>
                            @endif


                </div>
            </div>

            <div class="sticky-header">
                <div class="container">
                    <div class="outer-box">
                        <div class="logo-box">
                            <figure class="logo"><a href="{{ route('home') }}"><img src="{{ asset('storage/'. $setting->headerlogo) }}" width="200" alt="شعار الشركة"></a>
                            </figure>
                        </div>
                        <div class="menu-area">
                            <nav class="main-menu">

                            </nav>
                        </div>
                        @if (isset($Consulting->is_active) && $Consulting->is_active == true)
                        <a href="{{ $Consulting->button_link ?? null }}"
                            class="px-5 py-3 btn theme-btn rounded-5 button-console-none "><span>{{ $Consulting->button_text ?? null }}</span></a>
                                @endif

                    </div>
                </div>
            </div>
        </header>
        <!-- Main Header End -->


        <!-- Mobile Menu  -->
        <div class="mobile-menu">
            <div class="menu-backdrop"></div>
            <div class="close-btn"><i class="fas fa-times"></i></div>
            <nav class="menu-box">
                <div class="nav-logo"><a href="{{ route('home') }}"><img src="{{ asset('storage/'. $setting->headerlogo) }}" width="50" alt="شعار الشركة" title=""></a></div>
                <div class="menu-outer"><!--Here Menu Will Come Automatically Via Javascript / Same Menu as in Header-->
                </div>
                @if (isset($Consulting->is_active) && $Consulting->is_active == true)
                <a href="{{ $Consulting->button_link ?? null }}"
                    class="px-5 py-2 mt-4 btn theme-btn rounded-5 btn-sm me-3 button-console-action"><span>{{ $Consulting->button_text ?? null }}</span></a>
                        @endif
            </nav>
        </div>
        <!-- End Mobile Menu -->


        {{ $slot }}


        <footer class="py-5 footer footer-section-image" dir="ltr">
            <div class="container">
                <div class="row justify-content-center align-items-center">

                    <!-- Social Media Icons -->
                    <div class="text-center col-md-4 social-icons">
                        @if (isset($setting->social_links) && $setting->social_links != null)

                        @foreach ($setting->social_links as $link )

                        @if ($link['social_type'] === "Facebook")
                        <a href="{{ $link['name'] }}"><i class="fab fa-facebook"></i></a>
                        @endif
                        @if ($link['social_type'] === "Instagram")
                        <a href="{{ $link['name'] }}"><i class="fab fa-instagram"></i></a>
                        @endif
                        @if ($link['social_type'] === "Twitter")
                        <a href="{{ $link['name'] }}"><i class="fab fa-twitter"></i></a>
                        @endif
                        @if ($link['social_type'] === "LinkedIn")
                        <a href="{{ $link['name'] }}"><i class="fab fa-linkedin"></i></a>
                        @endif
                        @if ($link['social_type'] === "YouTube")
                        <a href="{{ $link['name'] }}"><i class="fab fa-youtube"></i></a>
                        @endif
                        @if ($link['social_type'] === "Snapchat")
                        <a href="{{ $link['name'] }}"><i class="fab fa-snapchat-square"></i></a>
                        @endif
                        @if ($link['social_type'] === "Telegram")
                        <a href="{{ $link['name'] }}"><i class="fab fa-telegram"></i></a>
                        @endif
                        @if ($link['social_type'] === "TikTok")
                        <a href="{{ $link['name'] }}"><i class="fab fa-tiktok"></i></a>
                        @endif
                        @if ($link['social_type'] === "WhatsApp")
                        <a href="{{ $link['name'] }}"><i class="fab fa-whatsapp"></i></a>
                        @endif

                        @endforeach
                        @endif
                    </div>

                    <!-- Copyright Text -->
                    <div class="text-center col-md-4">
                        <p class="copyright">
                            &copy; {{now()->format('Y')}} <span class="logo">FLUENT</span>. All rights reserved.
                        </p>
                    </div>

                    <!-- Logo Text in Arabic -->
                    <div class="text-center col-md-4">
                        <img class="logo" src="{{ asset('storage/'. $setting->headerlogo) }}" width="200" alt="شعار الشركة" title="">
                    </div>
                </div>
            </div>
        </footer>
        <!-- End desgin the footer    -->


        <!-- Scroll Top -->
        <button class="scroll-top scroll-to-target" data-target="html"><i class="icon-4"></i></button>

    </div>


    <!-- Jequery Plugins List -->
    <script src="{{ asset('front/assets/js/jquery.js')}}"></script>
    <script src="{{ asset('front/assets/js/bootstrap.min.js')}}"></script>
    <script src="{{ asset('front/assets/js/owl.js')}}"></script>
    <script src="{{ asset('front/assets/js/appear.js')}}"></script>
    <script src="{{ asset('front/assets/js/odometer.js')}}"></script>

    <!-- Custom Js -->
    <script src="{{ asset('front/assets/js/custom.js')}}"></script>

    <script>
        function openVideoModal(url) {
          let videoFrame = document.getElementById('videoFrame');
          let platformEmbedUrl = getEmbedUrl(url);

          if (platformEmbedUrl) {
            videoFrame.src = platformEmbedUrl;
            new bootstrap.Modal(document.getElementById('videoModal')).show();
          } else {
            alert("Unsupported video platform.");
          }
        }

        function getEmbedUrl(url) {
          if (url.includes("youtube.com") || url.includes("youtu.be")) {
            return url.replace("watch?v=", "embed/").replace("youtu.be/", "youtube.com/embed/");
          }
          if (url.includes("facebook.com")) {
            return `https://www.facebook.com/plugins/video.php?href=${encodeURIComponent(url)}`;
          }
          if (url.includes("tiktok.com")) {
            return `https://www.tiktok.com/embed/${url.split('/').pop()}`;
          }
          if (url.includes("instagram.com")) {
            return `${url}embed`;
          }
          return null;
        }

        $(".menu-link-close").on("click", function(event) {

    event.preventDefault();
    var target = $(this.getAttribute("href"));
    if (target.length) {
        $("html, body").stop().animate({
            scrollTop: target.offset().top
        }, 1000);
    }
    $("body").removeClass("mobile-menu-visible");
});


      </script>



@yield('scripts')



</body>
<!-- Boxed Wrapper End -->


</html>
