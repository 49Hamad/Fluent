<section class="glass-section-counters">
    <div class="container background">
        <div class="overlay">
            <section class="funfact-three">
                <div class="clearfix row align-items-center">
                    <div class="col-lg-6 col-md-12 col-sm-12">
                        <div class="text-box">
                            <h2 class="mb-4">{{ $numberTalk->title}}</h2>
                            <p>{{ $numberTalk->description}}</p>
                        </div>
                    </div>

                    @foreach ($numberTalk->counters as $counter)
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="funfact-block">
                            <div class="count-outer">
                                <span class="odometer" data-count="{{ $counter['numbers'] ?? 0 }}">00</span><span class="symble"></span>
                            </div>
                            <div class="funfact-text">
                                <div class="icon-box">
                                    <img src="{{ asset('storage/' . $counter['icon']) }}" style="padding: 10px;" alt="{{ $counter['title'] }}">
                                </div>
                                <p class="fs-6">{{ $counter['title'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
                </div>
            </section>
        </div>
    </div>

    <section class="clients-section" dir="ltr" id="OurPartenr">

        <div class="container">
            <div class="mt-3 text-center col-12" style="position: relative;
    top: 63px;">
                <span class="px-5 py-2 sub-title-two fs-6">{{ $OurPartner->title ?? null }}</span>
            </div>
            <div class="five-item-carousel owl-carousel owl-theme white-box-logo owl-nav-none owl-dots-none">
                @foreach($clients as $client)
                    <figure class="clients-box">
                        <a href="{{ $client->link }}" target="_blank">
                            <img src="{{ asset('storage/'.$client->logo) }}" alt="{{ $client->name }}">
                        </a>
                    </figure>
                @endforeach
            </div>
        </div>
    </section>
</section>
