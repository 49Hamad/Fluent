<section class="glass-section-about-us" id="AboutUs">
    <!-- Main Content Box with Glassmorphism -->
    <div class="tabs-container">
        <ul class="nav nav-tabs justify-content-start" id="myTab" role="tablist">
            @foreach($abouts as $index => $about)
                <li class="nav-item" role="presentation">
                    <button class="nav-link @if($index === 0) active @endif"
                            id="tab-{{ $index }}"
                            data-bs-toggle="tab"
                            data-bs-target="#content-{{ $index }}"
                            type="button"
                            role="tab"
                            aria-controls="content-{{ $index }}"
                            aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                        {{ $about->title }}
                    </button>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="tab-content glass-card col-lg-8" id="myTabContent">
        @foreach($abouts as $index => $about)
            <div class="tab-pane fade @if($index === 0) show active @endif"
                 id="content-{{ $index }}"
                 role="tabpanel"
                 aria-labelledby="tab-{{ $index }}">
                <p>{{ $about->description }}</p>
            </div>
        @endforeach
    </div>
</section>
