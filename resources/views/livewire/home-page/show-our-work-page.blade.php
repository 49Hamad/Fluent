<section id="OurWorks">

    <div class="pattern-layer services-section">
        <div class="container" style=" overflow: hidden;">
            <div class="text-center auto">
                <span class="px-5 py-2 sub-title-two fs-6">{{ $OurWorkText->title ?? null}}</span>
                @if (isset($OurWorkText->description) && $OurWorkText->description != null)
                <h2 class="section-title">{{ $OurWorkText->description ?? null }} </h2>

                @endif
            </div>

            <!-- Owl Carousel for Services -->
            <div class="owl-carousel services-carousel" dir="ltr">

                @foreach ($OurWorks as $work )

                <div class="item">
                    <div class="service-card">
                        <img src="{{ asset('storage/'.$work->image) }}" loading="lazy" alt="Service 1" class="img-fluid">
                        <div class="service-info">
                            @if ($work->link != null)
                            <h3 class="service-title"><a href="{{ $work->link }}" class="text-dark" target="blank">{{ $work->title }}</a></h3>
                            @else
                            <h3 class="service-title">{{ $work->title }}</h3>

                            @endif

                        </div>
                    </div>
                </div>

                @endforeach

            </div>

        </div>
    </div>

    <livewire:home-page.show-call-to-action-page/>

</section>
