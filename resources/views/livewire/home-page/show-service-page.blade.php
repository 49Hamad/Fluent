@section('style')
<style>

    .arrow-container {
        border: solid 1px #ffc107
    }



.main-custom-card-svg {
    flex-shrink: 0;
    width: 80%;
    height: 100%;
    position: relative;
    transition-property: transform;
    overflow: hidden;
    margin: auto;
}
.arrow-container {
    position: absolute;
    left: 0;
    top: 0;
    max-width: 17%;
    padding: 0px;
    border-radius: 13px;
    max-height: 13%;
    display: flex
}
.arrow-container svg {
    transition: all 0.5s
}


     .main-custom-card-svg:hover .arrow-container svg {
        transform: rotate(0deg);
        transition: all 0.5s
    }

     .main-custom-card-svg:hover .arrow-container svg {
        fill: #ffc107!important
    }

     .main-custom-card-svg:hover .arrow-container path {
        fill: #ffc107!important
    }

    .last-arrow path {
        fill: #ffc107
    }


.card-top-service-image {
    position: absolute;
    z-index: 111;
    top: 20%;
    left: 14%;
    height: 100%;
    width: 100%;
}
.card-top-service-image img {
    width: 70%;

}
.content {
    position: absolute;
    z-index: 111;
    top: -50px;
    right: 0;
    height: 100%;
    width: 100%;
    display: flex;
    flex-direction: column;
    padding: 15px;
    align-items: center;
    justify-content: flex-end;
}
    a {
        color: #fff;
        font-size: 15px;
        font-weight: 900
    }

    h4 {
        text-align: start;
        font-weight: 900
    }

    h3 {
        text-align: end;
        font-weight: 900;
        font-size: 36px;
        line-height: 42px
    }
.last-arrow {
    width: 15%
}
.last-arrow path {
    fill: #000
}



</style>
@endsection
<section class="py-5" id="Services">
    <div class="container">
        <div class="text-center auto">
            <span class="px-5 py-2 sub-title-two fs-6">{{ $serviceText->title ?? null }}</span>
            @if (isset($serviceText->description) && $serviceText->description != null)
            <p>{{ $serviceText->description ?? null }}</p>

            @endif
            <br>
        </div>

        <div class="slider-container">

            <div class="owl-carousel owl-theme custom-card-services row auto owl-nav-none " dir="ltr">



                @foreach ($services as $service)
                <div class="mt-5 main-custom-card-svg color1 " role="group" aria-label="1 / 5"  >
                    <div class="arrow-container ">
                        <svg width="100%" viewBox="0 0 31 31" fill="none" xmlns="http://www.w3.org/2000/svg" >
                            <path d="M1.5998e-06 30C1.5998e-06 30.5523 0.447717 31 1 31L10 31C10.5523 31 11 30.5523 11 30C11 29.4477 10.5523 29 10 29L2 29L2 21C2 20.4477 1.55229 20 1 20C0.447717 20 1.5998e-06 20.4477 1.5998e-06 21L1.5998e-06 30ZM29.2929 0.292893L0.292895 29.2929L1.70711 30.7071L30.7071 1.70711L29.2929 0.292893Z" fill="#ffc107" ></path>
                        </svg>
                    </div>

                    <svg viewBox="0 0 407 508" fill="none" xmlns="http://www.w3.org/2000/svg" >
                        <path class="big-box " d="M81.6 20C81.6 9.28568 90.2857 0.6 101 0.6H387C397.714 0.6 406.4 9.28568 406.4 20V488C406.4 498.714 397.714 507.4 387 507.4H20C9.28567 507.4 0.6 498.714 0.6 488V213.5V100.5C0.6 89.7857 9.28568 81.1 20 81.1H61C72.3771 81.1 81.6 71.8771 81.6 60.5V20Z" stroke="#ffc107" stroke-width="1.5"></path>
                    </svg>
                    <div class="card-top-service-image">
                        <img src="{{ asset('storage/' . $service->image) }}" loading="lazy" class="img-service-hover-svg-card" alt="">

                    </div>
                    <div class="content ">
                        <div class="upper-div ">
                            <h4 >{{ $service->title ?? null }}</h4>
                        </div>
                        <div class="lower-div ">
                            <p data-bs-toggle="modal" data-bs-target="#modalId_{{ $service->id }}" s
                                class="mt-2 text-warning service-p" style="font-size:14px;cursor: pointer;position:relative; z-index:1001">
                                تعرف على المزيد </p>
                        </div>
                    </div>

                </div>
                @endforeach




            </div>

        </div>



    </div>

    @foreach ($services as $service)
    <div class="modal fade" id="modalId_{{ $service->id }}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
        role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered" role="document">
            <div class="modal-content"
                style="background-image: url({{ asset('front/assets/images/Imafgdggfe@2x.png') }}); background-size: cover; background-position: center;">
                <div class="modal-header">
                    <h5 class="modal-title modal-title-h2" id="modalTitleId"> {{ $service->title ?? null }}</h5>
                    <button type="button" class="btn-close me-5" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="    font-size: 16px;">
                {!! $service->description ?? null !!}
                </div>
                <div class="my-3 text-center">
                    <button type="button" class="btn btn-light rounded-5 btn-sm me-3"
                        style=" position: relative; display: inline-block; overflow: hidden;
                                 vertical-align: middle; font-size: 15px;line-height: 0px;font-weight: 700;
                                 text-align: center;border-radius: 6px; padding: 15px 15px; "

                        data-bs-dismiss="modal">
                        إغلاق
                    </button>
                </div>
            </div>
        </div>
    </div>

    @endforeach
</section>
