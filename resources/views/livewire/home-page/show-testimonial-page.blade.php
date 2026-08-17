<section class="testimonial-two">
    <div class="auto-container">
        <div class="text-center section-title">
            <span class="px-5 py-2 sub-title-two fs-6">آراء عملائنا</span>
            <h2>آراء عملائنا</h2>
        </div>
        <div class="rounded two-item-carousel owl-carousel owl-theme owl-nav-none" dir="ltr">
            @foreach($FormEvaluations as $evaluation)
            <div class="rounded testimonial-block-two client-background-color">
                <div class="inner-box" dir="rtl">
                    <ul class="rating">
                        @for($i = 0; $i < 5; $i++)
                            <li><i class="icon-8"></i></li>
                        @endfor
                    </ul>
                    <p style="font-size: 14px;">
                        {{ $evaluation->feedback }}
                    </p>

                    <div class="d-flex align-items-center justify-content-between ">
                        <div class="d-flex align-items-center ">
                            <img src="{{ asset('front/assets/images/client-icon/1.png') }}"loading="lazy" width="50" alt="Author"
                                 class="rounded-circle author-image">
                            <div class="text-end me-3">
                                <h5 class="mb-2 author-name">{{ $evaluation->client_name }}</h5>
                                <span class="author-title fs-6">{{ $evaluation->company_name }}</span>
                            </div>
                        </div>
                        <div>
                            <img src="{{ asset('front/assets/images/icon-70.png') }}" loading="lazy" width="50" alt="Icon" class="icon-image">
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
