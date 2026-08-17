 <section class="features-section">
            <div class="container">
                <div class="row align-items-center show-lgs ">
                    <!-- Right side with text -->
                    <div class="col-lg-6 col-md-12 features-section-text">
                        <h1 class="mb-4 display-4">{{ $why_choose_us->main_title }}</h1>
                        <h2 class="mb-4" style="line-height: 4rem;">{{ $why_choose_us->sub_title }}</h2>
                        <p style="font-family: 'Alexandria-Regular'; font-size:14px">
                            {{ $why_choose_us->description }}
                        </p>

                        <div class="mb-5 text-center btn-box">
                            <a href="{{ $why_choose_us->button_link }}" class="mx-5 mt-3 theme-btn rounded-5">
                                <span>{{ $why_choose_us->button_text }}</span>
                            </a>
                        </div>
                    </div>
                    <!-- Left side with checkboxes -->
                    <div class="text-center col-lg-6 col-md-12 moveToRight">
                        @foreach ($why_choose_us->features as $feature)
                        <div class="p-3 mb-4 rounded feature-box d-flex align-items-center ">
                            <div class="mx-1 bg-warning rounded-circle check-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#000"
                                     class="bi bi-check" viewBox="0 0 16 16">
                                    <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z" />
                                </svg>
                            </div>
                            <span class="text-white fs-5 me-2">{{ htmlspecialchars($feature['title']) }}</span>
                        </div>
                    @endforeach

                    </div>
                </div>


                <div class="row align-items-center show-md-sms ">
                    <!-- Right side with text -->
                    <div class="col-lg-6 col-md-12 features-section-text">
                        <h1 class="mb-4 display-4">{{ $why_choose_us->main_title }}</h1>
                        <h2 class="mb-4" style="line-height: 4rem;">{{ $why_choose_us->sub_title }}</h2>
                        <p style="font-family: 'Alexandria-Regular'; font-size:14px">
                            {{ $why_choose_us->description }}
                        </p>

                    </div>
                    <!-- Left side with checkboxes -->
                    <div class="mt-3 text-center col-lg-6 col-md-12">
                        <div class="row">
                            @foreach ($why_choose_us->features as $feature)
                            <div class="p-3 mb-4 rounded col-md-6 col-sm-6 col-6 d-flex align-items-center show-section-md-sm-box">
                                <div class="mx-1 bg-warning rounded-circle check-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#000"
                                         class="bi bi-check" viewBox="0 0 16 16">
                                        <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z" />
                                    </svg>
                                </div>
                                <span class="text-white fs-5 me-2">{{ htmlspecialchars($feature['title']) }}</span>
                            </div>
                        @endforeach

                        </div>
                    </div>
                    <div class="mb-5 text-center btn-box">
                        <a href="{{ $why_choose_us->button_link }}" class="mx-5 mt-3 theme-btn rounded-5">
                            <span>{{ $why_choose_us->button_text }}</span>
                        </a>
                    </div>
                </div>

            </div>
        </section>


