
@if (isset($achievement) && $achievement != null)


<section class="background-achievements">

    <div class="container">
        <div class="text-center auto">
            <span class="px-5 py-2 sub-title-two fs-6">{{ $achievement->title }}</span>
            <h4> {{ $achievement->description }}</h4>
        </div>
        <div class="overlay">


            <div class="container mt-5">
                <div class="row align-items-center">
                    <div class="col-lg-6 col-md-12">
                        <div class="row">
                            @php
                                $counters = 0 ;
                            @endphp
                            @foreach ($achievement->achievements as $achiev)
                            @php
                                $counters++;
                            @endphp
                            <div class="col-md-6">
                                <div class="info-box" data-number="{{ $counters }}">
                                    <p>{{ $achiev['title'] }}</p>
                                </div>
                            </div>

                            @endforeach

                        </div>
                    </div>
                    <div class="m-auto mt-5 text-center col-lg-6 col-md-12 d-flex d-none d-sm-block">

                        <div class="handshake-container">
                            <img src="{{ asset('storage/'.$achievement->image) }}" loading="lazy" class="img-fluid w-75" alt="{{ $achievement->title }}">
                        </div>
                    </div>
                </div>
            </div>






        </div>
    </div>


</section>


@endif
